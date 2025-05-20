<?php
namespace Controllers;

abstract class Base
{
    protected $_app = null;
    protected $_view = null;
    protected $_flash = null;
    protected $_response = null;
    protected $_request = null;

    public function __construct()
    {
        $this->_app = \Application::getInstance();
        $this->_view = $this->_app->getContainer()->get('view');
        $this->_flash = $this->_app->getContainer()->get('flash');
        $this->_request = $this->_app->getContainer()->get('request');
        $this->_response = $this->_app->getContainer()->get('response');
    }

    abstract protected function handler();

    public function execute_method($func_name,$param = null)
    {
		if (method_exists($this, $func_name)) {
            return $this->$func_name($param);
        } else {
            $this->redirect_404();
        }
    }

    public function redirect_404() {
		$handler = $this->_app->getContainer()->get('notFoundHandler');
		return $handler($this->_app->getContainer()->get('request'), $this->_app->getContainer()->get('response'));
    }

    public function get_canonical()
    {
        $query = (!empty($this->_request->getUri()->getQuery())) ? '?'.$this->_request->getUri()->getQuery() : '';
        return $this->get_baseurl($this->_request->getUri()->getBaseUrl()).$this->_request->getUri()->getPath().$query;
    }

    public function get_canonical_by_siteurl($site)
    {
        return $site->site_url.$this->_request->getUri()->getPath();
    }

    public function get_includes($site_pathname)
    {
        $directory = $this->_app->getContainer()->get('include_directory');
        return [
            'head_gtm' => file_get_contents($directory.'/head_plus'.$site_pathname.'_gtm.html'),
            'body_gtm' => file_get_contents($directory.'/body_plus'.$site_pathname.'_gtm.html'),
            'header' => file_get_contents($directory.'/inc_header.html'),
            'header_home' => file_get_contents($directory.'/inc_header_home.html'),
            'footer' => file_get_contents($directory.'/inc_footer.html')
        ];
    }

    public function get_top_includes($site_pathname)
    {
        $directory = $this->_app->getContainer()->get('include_directory');

        //トップページのみヘッダーロゴを<h1>要素に
        $header = file_get_contents($directory.'/inc_header.html');
        $header = preg_replace('{<div class="header__logo"(.*?)</div>}s', '<h1 class="header__logo"$1</h1>', $header);
        
        return [
            'head_gtm' => file_get_contents($directory.'/head_plus'.$site_pathname.'_gtm.html'),
            'body_gtm' => file_get_contents($directory.'/body_plus'.$site_pathname.'_gtm.html'),
            //'header' => file_get_contents($directory.'/inc_header.html'),
            'header' => $header,
            'header_home' => file_get_contents($directory.'/inc_header_home.html'),
            'footer' => file_get_contents($directory.'/inc_footer.html'),
            'explanation' => file_get_contents($directory.'/top/inc_explanation.html'),
            'qa' => file_get_contents($directory.'/top/inc_qa.html'),
            'ranking' => file_get_contents($directory.'/top/inc_ranking.html')
        ];
    }

    public function get_form_includes()
    {
        $directory = $this->_app->getContainer()->get('include_directory');
        return [
            'head_gtm' => file_get_contents($directory.'/head_pluskyousei_gtm.html'),
            'body_gtm' => file_get_contents($directory.'/body_pluskyousei_gtm.html'),
            'header' => file_get_contents($directory.'/inc_form_header.html'),
            'footer' => file_get_contents($directory.'/inc_form_footer.html')
        ];
    }

    public function get_basic_auth_baseurl($plus_url)
    {
        $basic_auth = $this->_app->getContainer()->get('basic_auth');
        return str_replace('//', '//'.$basic_auth, $plus_url);
    }

    public function get_baseurl($base_url)
    {
        $basic_auth = $this->_app->getContainer()->get('basic_auth');
        return str_replace('//'.$basic_auth, '//', $base_url);
    }


    protected function authorize()
    {
        $pathname = $this->_request->getUri()->getPath();
        if ( strpos($pathname,'cms') !== false )  {
            $path_array = explode('/',$pathname);
            $site_pathname = str_replace('_cms', '', $path_array[1]);
            $info = \Services\Factory::get_instance('site')->get_by_pathname($site_pathname);

            if ( empty($_SESSION["account_info_".$info->site_id]) ) {
                $_SESSION["requeset_path"] = $pathname;
                return '/cms/login/'.$info->site_id;
            } else {
                return '';
            }
        } else {
            return '';
        }

    }

    protected function implode_data($rows, $column_name)
    {
        $str = '';
        foreach ($rows as $row) {
            $str .= $row->$column_name.',';
        }
        return $str;
    }

    /* for clinic import */
    protected function set_plus_clinic_info(&$clinic, $site)
    {
        $clinic->urlEncodedName = urlencode($clinic->clinic_name);
        $clinic->siteName = $site->site_name;
        $clinic->siteUrl = $site->site_url;

        //$clinic->mapCodeByName = $this->get_map_code_by_name($clinic->urlEncodedName);

        $clinic->reserveUrl = $this->get_reserve_url(
            $clinic->clinic_id,
            $clinic->teikei_yoyaku_jump_flg,
            $clinic->teikei_yoyaku_static_flg,
            $clinic->clinic_yoyaku_page_url,
            $clinic->siteUrl);

        $imageList = [];
        for ($i=1;$i<19;$i++) {
            $n = 'innai_image'.$i;
            if ($clinic->$n !== '') $imageList[] = $clinic->$n;
        }
        $clinic->imageList = $imageList;

        $sfImageList = [];
        for ($i=1;$i<11;$i++) {
            $n = 'sf_innai_image'.$i;
            if ($clinic->$n !== '') $sfImageList[] = $clinic->$n;
        }
        $clinic->sfImageList = $sfImageList;

        $tmpArray = [];
        foreach ($clinic->clinic_station_railway_info as $station) {
            if (!in_array($station->station_group_id, $tmpArray)) {
                $tmpArray[] = $station->station_group_id;
            }
        }
        $clinic->station_id_list = implode('/', $tmpArray);
    }

    protected function set_specified_flg_nums($site_pathname, &$list)
    {
        $specs = \Services\Factory::get_instance('attribute')->get_list(['type'=>$site_pathname, 'is_specified'=>true, 'is_valid'=>true]);

        foreach ($list as $item) {
            $nums = 0;
            foreach ($specs as $spec) {
                $flgName = $spec->attribute_flgname;
                if (isset($item->$flgName)) {
                    if ($item->$flgName === "1") $nums++;
                }
            }
            $item->specifiedFlgNums = $nums;
        }
    }

    protected function get_reserve_url($clinicId, $jumpFlg, $staticFlg, $clinicUrl, $siteUrl)
    {
        if ($jumpFlg) {
            return $clinicUrl;
        } elseif (!$jumpFlg && $staticFlg) {
            return '';
        } elseif (!$jumpFlg && !$staticFlg) {
            return $siteUrl.'/appoint/index.html?id='.$clinicId;
        }
    }

    protected function get_image_file($url, $clinicId, $imageId, $sitePathname)
    {
        $context = stream_context_create(array(
            'http' => array('ignore_errors' => true)
        ));

        $data = file_get_contents($url,false,$context);
        $extension = pathinfo($url, PATHINFO_EXTENSION);

        if (preg_match("/".$sitePathname."/",filter_input(INPUT_SERVER, 'SERVER_NAME', FILTER_SANITIZE_STRING))) {
            $pos = strpos($http_response_header[0], '200');
            /*if ( $pos ) {*/
            $directory = $this->_app->getContainer()->get('clinic_image_directory').'/'.$clinicId.'/';
            $filename = sprintf('%02d', $imageId).'.'.$extension;
            if ( \Services\Image::move_image_file($data, $directory, $filename) ) {
                $request = ['clinic_id' => $clinicId, 'image_id' => $imageId, 'filename' => $filename];
                \Services\Factory::get_instance('clinic_image', $sitePathname)->insert_for_upload($request);
            }
            /*}*/
        } else {
            $directory = $this->_app->getContainer()->get('clinic_image_directory').'tmp/';
            $filename = $clinicId.'_'.sprintf('%02d', $imageId).'.'.$extension;
            if ( \Services\Image::move_image_file($data, $directory, $filename) ) {
                $site = \Services\Factory::get_instance('site')->get_by_pathname($sitePathname);
                $request = $this->create_clinic_image_request($site, $clinicId);

                //絶対パス生成
                $absolute_dir = $this->create_absolute_path($directory);
                $request['filename'] = $absolute_dir.'public/image/tmp/'.$filename;

                $basic_auth_baseurl = $this->get_basic_auth_baseurl($site->plus_url);

                if ( \Services\FileMove::upload_to_remote($request, $basic_auth_baseurl, true) ) unlink($directory.$filename);
            }
        }
    }

    protected function create_clinic_image_request($site, $clinic_id)
    {
        $request = [
            'mode' => 'upload_file',
            'element_type' => 'clinic_image',
            'clinic_id' => $clinic_id,
            'image_dir' => '/image/'.$clinic_id,
            'host' => $site->plus_url,
            'site_pathname' => $site->site_pathname
        ];

        return $request;
    }

    protected function create_absolute_path($directory)
    {
        $directories = explode('/', $directory);
        $str = '';
        foreach ($directories as $dir) {
            $str .= $dir.'/';
            if ($dir === 'dentarest') return $str;
        }

        return $str;
    }
}
