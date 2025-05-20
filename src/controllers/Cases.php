<?php
namespace Controllers;

/* プラス 症例情報 */
class Cases extends Base
{
	use \Values\Meta;
	use \Values\Cases;

    public function handler()
    {
    	if ($this->authorize() !== '') {
    		return \Services\Render::redirect($this->_response, $this->authorize());
    	}

    	try {
			if ( $this->_request->isGet() ) {
				$pathname = $this->_request->getUri()->getPath();
				if ( strpos($pathname,'cms') !== false )  {
					if ( strpos($pathname,'list') !== false ) {
						$func_name = 'show_cms_list';
					} else {
						$path_array = explode('/', $pathname);
						$func_name = ($path_array[3] === '0') ? 'show_create_form' : 'show_cms_list';
					}
				} else {
					$path_array = explode('/',$pathname);
					if ( count($path_array) === 6 ) {
						$func_name =  'show_detail';
					} else {
						return $this->redirect_404();
					}
				}
				return $this->execute_method($func_name);
			} elseif ( $this->_request->isPost() ) {
				$mode = $this->_request->getParam('mode');
				/*
				if ( $mode === 'update') {
					$func_name = 'update';
				} elseif ( $mode === 'update_feature') {
					$func_name = 'update_feature';
				}
				*/
				return $this->execute_method($mode);
			}
		} catch (\Exception $e) {
			throw new \Exceptions\ErrorException($e);
		}
	}

	protected function show_cms_list()
	{
		$pathname = $this->_request->getUri()->getPath();
		$path_array = explode('/', $pathname);
		$site_pathname = str_replace('_cms', '', $path_array[1]);
		$site = \Services\Factory::get_instance('site')->get_by_pathname($site_pathname);

		$value = new \stdClass();
		$value = $this->get_list_values($site);

		$request = $this->_request->getQueryParams();
		$request['site_pathname'] = $site->site_pathname;
		$page = (isset($request['page'])) ? $request['page'] : 1;
		$limit = 10;

		$list = new \stdClass();
		$list->search_text = (isset($request['search_text'])) ? $request['search_text'] : '';
		$list->data = \Services\Factory::get_instance($site_pathname.'_case')->get_cms_list($request,$page,$limit);
		$list->total = \Services\Factory::get_instance($site_pathname.'_case')->get_total_count($request);

		$header = new \stdClass();
		$header->js_list = $this->get_js_list('cms');

		$headline = new \stdClass();
		$headline->path = $this->_request->getUri()->getPath();
		$headline->page = $page;
		$headline->last_page = ceil($list->total/$limit);
		$list->headline = $headline;
		$list->pages = $this->get_pages($page, $headline->last_page, $request, $site->site_pathname);		
		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'list/cms_list.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value,
			'list' => $list
		]);
	}

	protected function show_create_form()
	{
		$pathname = $this->_request->getUri()->getPath();
		$path_array = explode('/', $pathname);
		$site_pathname = str_replace('_cms', '', $path_array[1]);
		$site = \Services\Factory::get_instance('site')->get_by_pathname($site_pathname);

		$header = new \stdClass();
		$header->js_list = $this->get_js_list('cms');
		$header->css_list = $this->get_css_list('cms');

		$value = new \stdClass();
		$value = $this->get_values($site, ['id' => 0]);

		$list = new \stdClass();
		$list->attributes = \Services\Factory::get_instance('attribute')->get_list(['type'=>$site_pathname, 'is_valid'=>true, 'attribute_type'=>2]);

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'case/create_form.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value,
			'list' => $list
		]);

	}

	/* 症例ページ */
	protected function show_detail ()
	{
		$base_url = $this->get_baseurl($this->_request->getUri()->getBaseUrl());
		$site = \Services\Factory::get_instance('site')->get_by_baseurl($base_url);

		$request = $this->_request->getQueryParams();
		$isPreview = (isset($request['preview'])) ? true : false;

		$detail = $this->validate_detail_path($site, $isPreview);
		if ( empty($detail) ) {
			return $this->redirect_404();
		}
		\Services\Factory::get_instance($site->site_pathname.'_case')->get_pub_upd_at($detail, $isPreview, true);
		$detail->meta = \Services\Factory::get_instance($site->site_pathname.'_recommend_clinic_feature')->get_recommend_info($detail, $site, $isPreview);

		$headline = new \stdClass();
		$headline->base_url = $base_url;
		$headline->canonical = $this->get_canonical();
		$headline->ga_index = ($isPreview) ? 'noindex,nofollow' : 'index,follow';

		$cases = \Services\Factory::get_instance($site->site_pathname.'_case')->get_list_by_clinic($detail->clinic_id, $detail->case_attribute_id, null, null, $isPreview, true);

		if ( !isset($request['preview']) && empty($cases) ) {
			return $this->redirect_404();
		}

		$clinic = \Services\Factory::get_instance($site->site_pathname.'_clinic')->get_basic_clinic_info($detail->clinic_id, $detail->meta->recommend_id, $isPreview);
		$clinic->operation_times = \Services\Factory::get_instance($site->site_pathname.'_operation_time')->get_by_clinic_id($detail->clinic_id);

		$header = new \stdClass();
		$header->css_list = $this->get_css_list('portal_detail');
		$header->js_list = $this->get_js_list('portal_detail');

		$value = new \stdClass();
		$value->includes = $this->get_includes($site->site_pathname);
		$value->breadcrumb = $this->get_breadcrumb($site, $detail);
		$value->site = $site;
		$value->headline = $headline;
		$value->detail = $detail;
		$value->cases = $cases;
		$value->clinic = $clinic;

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'case/portal_detail.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value
		]);
	}

	protected function create()
	{
		$request = $this->_request->getParsedBody();

		$request['sort_order'] = \Services\Factory::get_instance($request['site_pathname'].'_case')->get_grandson_order('clinic',$request['clinic_id'],'case_attribute',$request['case_attribute_id']);

		$self_data = null;
		if (isset($request['feature_id'])) {
			$self_data = \Services\Factory::get_instance($request['site_pathname'].'_self_clinic_feature')->get_by_clinic_feature_id($request['clinic_id'], $request['feature_id']);
		}

		$case_id = \Services\Factory::get_instance($request['site_pathname'].'_case')->insert($request, $self_data, true);
		$case_id = \Services\Factory::get_instance($request['site_pathname'].'_case')->insert($request);

		/* image file */
		if (isset($request['feature_id'])) {
			$site = \Services\Factory::get_instance('site')->get_by_pathname($request['site_pathname']);
			$before_url = $site->plus_url.'/image/'.$request['clinic_id'].'/case_'.$request['feature_id'].'_before.webp';
			$after_url = $site->plus_url.'/image/'.$request['clinic_id'].'/case_'.$request['feature_id'].'_after.webp';
			$this->get_case_image_file($before_url, $request['clinic_id'], $case_id, 'before', $request['site_pathname']);
			$this->get_case_image_file($after_url, $request['clinic_id'], $case_id, 'after', $request['site_pathname']);
		}

		$request['case_id'] = $case_id;
		if (isset($request['feature_id'])) \Services\Factory::get_instance($request['site_pathname'].'_self_clinic_feature')->update_case_id($request);

		$pathname = '/'.$request['site_pathname'].'_cms/clinic/'.$request['clinic_id'];
		return \Services\Render::redirect($this->_response, $pathname);
	}

	protected function update_case()
	{
		$request = $this->_request->getParsedBody();
		$site_pathname = $request['site_pathname'];

		//データ更新
		\Services\Factory::get_instance($site_pathname.'_case')->update($request, true);

		$data = ['is_error'=>false];
		return \Services\Render::to_json($this->_response, $data);
	}

	protected function update_preview()
	{
		$request = $this->_request->getParsedBody();
		$site_pathname = $request['site_pathname'];

		//修正前データの取得
		$before = \Services\Factory::get_instance($site_pathname.'_case')->get_row($request['case_id']);

		//プレビューデータの取得
		$after = \Services\Factory::get_instance($site_pathname.'_case')->get_row($request['case_id'], true);

		//データ更新
		\Services\Factory::get_instance($site_pathname.'_case')->update($after);

		$data = ['is_error'=>false];
		return \Services\Render::to_json($this->_response, $data);
	}

	private function get_image_dir($request)
	{
		$str = '/image/'.$request['clinic_id'];

		return $str;
	}

    private function get_case_image_file($url, $clinic_id, $case_id, $case_type, $site_pathname)
    {
        $context = stream_context_create(array(
            'http' => array('ignore_errors' => true)
        ));

        $data = file_get_contents($url,false,$context);
        $extension = pathinfo($url, PATHINFO_EXTENSION);

        if (preg_match("/".$site_pathname."/",filter_input(INPUT_SERVER, 'SERVER_NAME', FILTER_SANITIZE_STRING))) {
            $pos = strpos($http_response_header[0], '200');
            /*if ( $pos ) {*/
            $directory = $this->_app->getContainer()->get('clinic_image_directory').'/'.$clinic_id.'/';
            $image_id = \Services\Factory::get_instance('case_image', $site_pathname)->get_child_alternatekey('case', $case_id, 'image');
            $filename = 'case_'.$case_id.'_'.$image_id.'.'.$extension;
            if ( \Services\Image::move_image_file($data, $directory, $filename) ) {
                $request = [
                	'clinic_id' => $clinic_id,
                	'case_id' => $case_id,
                	'image_id' => $image_id,
                	'case_type' => $case_type,
                	'filename' => $filename
                ];
                \Services\Factory::get_instance('case_image', $site_pathname)->insert_for_upload($request);
            }
            /*}*/
        } else {
            $directory = $this->_app->getContainer()->get('clinic_image_directory').'tmp/';
            $filename = 'case_'.$case_id.'_'.$image_id.'.'.$extension;
            if ( \Services\Image::move_image_file_as_webp($data, $directory, $filename) ) {
                $site = \Services\Factory::get_instance('site')->get_by_pathname($site_pathname);
                $request = $this->create_case_image_request($site, $clinic_id, $case_id, $case_type);

                //絶対パス生成
                $absolute_dir = $this->create_absolute_path($directory);
                $request['filename'] = $absolute_dir.'public/image/tmp/'.$filename;

                $basic_auth_baseurl = $this->get_basic_auth_baseurl($site->plus_url);

                if ( \Services\FileMove::upload_to_remote($request, $basic_auth_baseurl, true) ) unlink($directory.$filename);
            }
        }
    }

    private function create_case_image_request($site, $clinic_id, $case_id, $case_type)
    {
        $request = [
            'mode' => 'upload_case_file',
            'element_type' => 'case_image',
            'clinic_id' => $clinic_id,
            'case_id' => $case_id,
            'image_dir' => '/image/'.$clinic_id,
            'case_type' => $case_type,
            'host' => $site->plus_url,
            'site_pathname' => $site->site_pathname
        ];

        return $request;
    }

   	/* 症例ページURLチェック */
	private function validate_detail_path($site, $isPreview)
	{
		$path_array = explode('/',$this->_request->getUri()->getPath());
		$clinic_id = $path_array[2];
		$attribute_pathname = $path_array[4];

		$detail = \Services\Factory::get_instance($site->site_pathname.'_case')->get_page_detail($clinic_id, $attribute_pathname, $isPreview);

		return $detail;
	}

	/* 症例ページパンくず配列取得 */
	private function get_breadcrumb($site, $data = [])
	{
		$breadcrumb = [];

		$top_text = ($site->site_id > 1) ? $site->site_name.'ネット＋' : 'TOP';
		$breadcrumb[0] = ['href' => '/' , 'text' => $top_text];
		$breadcrumb[1] = ['href' => '' , 'text' => '【'.$data->clinic_name.'】'.$data->attribute_name.'の症例'];

		return $breadcrumb;
	}

	/* pagenation配列取得 */
	private function get_pages($page, $last_page, $request, $site_pathname)
	{
		$pages = [];
		$params = [];
		$prev_params = [];
		$next_params = [];
		$max = (($last_page - $page) > 3) ? 3 : $last_page - $page;

		if (isset($request['search_text'])) $params[] = 'search_text='.$request['search_text'];

		$prev_params = $params;
		if ($page !== 1) $prev_params[] = 'page='.($page-1);
		$pages['prev_href'] = (!empty($prev_params)) ? '?'.implode('&', $prev_params) : '';

		$next_params = $params;
		if ($page !== $last_page) $next_params[] = 'page='.($page+1);
		$pages['next_href'] = (!empty($next_params)) ? '?'.implode('&', $next_params) : '';

		for ($i = 0; $i <= $max; $i++) {
			if ($i === 0) {
				$pages['numbers'][$i] = ['anchor_class' => 'pagination__a--active '.$site_pathname.'-color', 'href' => '', 'text' => $page]; 
			} elseif ($i === 2 && ($last_page - $page) > 3) {
				$pages['numbers'][$i] = ['anchor_class' => '', 'href' => '', 'text' => '<span class="pagination__text">...</span>'];
			} elseif($i === 3 && ($last_page - $page) > 3) {
				$params[] = 'page='.($last_page);
				$pages['numbers'][$i] = ['anchor_class' => 'pagination__a', 'href' => '?'.implode('&', $params), 'text' => $last_page]; 
			} else {
				$params[] = 'page='.($page+$i);
				$pages['numbers'][$i] = ['anchor_class' => 'pagination__a', 'href' => '?'.implode('&', $params), 'text' => $page+$i]; 
			}
		}
		return $pages;
	}
}
