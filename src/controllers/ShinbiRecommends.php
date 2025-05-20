<?php
namespace Controllers;

use Carbon\Carbon as Carbon;

/* 矯正歯科ネットプラス おすすめページ */
class ShinbiRecommends extends Base
{
	use \Values\Meta;
	use \Values\ShinbiRecommends;

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
						$type = (isset($request['type'])) ? '_type'.$request['type'] : '';
						$func_name = ($path_array[3] === '0') ? 'show_select_form'.$type : 'show_cms_detail';
					}
				}
				return $this->execute_method($func_name);
			} elseif ( $this->_request->isPost() ) {
				$mode = $this->_request->getParam('mode');
				if ( $mode === 'create' ) {
					$func_name = 'show_create_form';
				} elseif ( $mode === 'preview' ) {
					$func_name = 'show_preview';
				} elseif ( $mode === 'update') {
					$func_name = 'update';
				} elseif ( $mode === 'add_clinic') {
					$func_name = 'add_clinic';
				}
				return $this->execute_method($func_name);
			}
		} catch (\Exception $e) {
			throw new \Exceptions\ErrorException($e);
		}
	}

	public function delete_images()
	{
		$directory = $this->_app->getContainer()->get('clinic_image_directory');
		system("rm -rf {$directory}");
		var_dump($directory);
	}

	protected function show_cms_list()
	{
		$request = $this->_request->getQueryParams();

		$value = new \stdClass();
		$type = (isset($request['type'])) ? (int)$request['type'] : 1;
		$value = $this->get_list_values(['recommend_id'=>1, 'type'=>$type]);
		
		$page = (isset($request['page'])) ? $request['page'] : 1;
		$limit = 10;

		$list = new \stdClass();
		$list->search_text = (isset($request['search_text'])) ? $request['search_text'] : '';
		$list->data = \Services\Factory::get_instance('shinbi_recommend')->get_cms_list($request,$page,$limit);
		$list->total = \Services\Factory::get_instance('shinbi_recommend')->get_total_count($request);

		$header = new \stdClass();
		$header->js_list = $this->get_js_list('cms');

		$headline = new \stdClass();
		$headline->path = $this->_request->getUri()->getPath();
		$headline->page = $page;
		$headline->last_page = ceil($list->total/$limit);
		$headline->type = $type;
		$list->headline = $headline;
		$list->pages = $this->get_pages($page, $headline->last_page, $request);	
		

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'list/cms_list.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value,
			'list' => $list
		]);
	}


	protected function show_select_form()
	{
		$list = new \stdClass();
		$list->prefectures = \Services\Factory::get_instance('prefecture')->get_all(['is_domestic'=>true]);
		$list->attributes = \Services\Factory::get_instance('attribute')->get_list(['type'=>'shinbi', 'is_13'=>true, 'is_valid'=>true]);

		$header = new \stdClass();
		$header->js_list = $this->get_js_list('cms');

		$value = new \stdClass();
		$value = $this->get_values(['recommend_id'=>0, 'type'=>1]);

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'recommend/select_form.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value,
			'list' => $list
		]);

	}

	protected function show_select_form_type2()
	{
		$list = new \stdClass();
		$list->prefectures = \Services\Factory::get_instance('prefecture')->get_all(['is_domestic'=>true]);
		$list->attributes = \Services\Factory::get_instance('attribute')->get_list(['type'=>'shinbi', 'is_valid'=>true, 'attribute_type'=>2]);

		$header = new \stdClass();
		$header->js_list = $this->get_js_list('cms');

		$value = new \stdClass();
		$value = $this->get_values(['recommend_id'=>0, 'type'=>2]);

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'recommend/select_form_type2.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value,
			'list' => $list
		]);

	}

	protected function show_cms_detail()
	{
		$pathname = $this->_request->getUri()->getPath();
		$path_array = explode('/', $pathname);
		$recommend_id = (int)$path_array[3];

		$clinics = \Services\Factory::get_instance('shinbi_recommend_clinic')->get_list($recommend_id, false, true);
		foreach ($clinics as $clinic) {
			$features = \Services\Factory::get_instance('shinbi_recommend_clinic_feature')->get_cms_by_clinic_id($recommend_id, $clinic->clinic_id, false);
			$clinic->feature_title = $this->implode_data($features, 'feature_title');
			$clinic->feature_text = $this->implode_data($features, 'feature_text');

			$flows = \Services\Factory::get_instance('shinbi_recommend_clinic_flow')->get_cms_by_clinic_id($recommend_id, $clinic->clinic_id, false);
			$clinic->flow_title = $this->implode_data($flows, 'flow_title');
			$clinic->flow_text = $this->implode_data($flows, 'flow_text');
		}

		$preview_clinics = \Services\Factory::get_instance('shinbi_recommend_clinic')->get_list($recommend_id, true, true);
		foreach ($preview_clinics as $clinic) {
			$features = \Services\Factory::get_instance('shinbi_recommend_clinic_feature')->get_cms_by_clinic_id($recommend_id, $clinic->clinic_id, true);
			$clinic->feature_title = $this->implode_data($features, 'feature_title');
			$clinic->feature_text = $this->implode_data($features, 'feature_text');

			$flows = \Services\Factory::get_instance('shinbi_recommend_clinic_flow')->get_cms_by_clinic_id($recommend_id, $clinic->clinic_id, true);
			$clinic->flow_title = $this->implode_data($flows, 'flow_title');
			$clinic->flow_text = $this->implode_data($flows, 'flow_text');
		}

		foreach ($preview_clinics as $id => $preview) {
			$index = array_search($preview->clinic_id, array_map(function ($v) { return $v->clinic_id; }, $clinics));
			if ($index !== false && $clinics[$index] != $preview) {
				$clinics[$index] = $preview;
				$clinics[$index]->is_edited = 1;
			} elseif ($index === false) {
				$preview->exists = 0;
				$preview->is_edited = 1;
				$clinics[] = $preview;
			}
		}

		$isEdited = \Services\Factory::get_instance('shinbi_recommend')->exists_preview($recommend_id);
		$data = \Services\Factory::get_instance('shinbi_recommend')->get_by_id($recommend_id, $isEdited, $clinics);

		$data->clinics = $clinics;
		$data->site = \Services\Factory::get_instance('site')->get_by_id(4);

		$header = new \stdClass();
		$header->js_list = $this->get_js_list('cms');
		$header->css_list = $this->get_css_list('cms');

		$value = new \stdClass();
		$value = $this->get_values(['recommend_id'=>$recommend_id, 'type'=>$data->attribute_type]);

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'recommend/edit_form.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value,
			'data' => $data
		]);

	}

	protected function show_create_form()
	{
		$request = $this->_request->getParsedBody();

		if ($request['search_mode'] !== 'import') {
			//選択された条件の記事が存在しているか確認
			$recommendId = \Services\Factory::get_instance('shinbi_recommend')->get_recommend_id($request);

			if ($recommendId > 0) {
				$pathname = $this->_request->getUri()->getPath();
				return \Services\Render::redirect($this->_response, str_replace('0', $recommendId, $pathname));
			}
		}

		//指定された「こだわり」フラグが立つ歯科医院の取得
		$implantList = array();
		$kyouseiList = array();
		$shinbiList = array();

		if ($request['search_mode'] !== 'create') {
			$shinbiList = $this->get_clinic_list('4', 'shinbi', $request);
		}
		
		$allList = array_merge($shinbiList, $implantList, $kyouseiList);
		$uniqueList = array();
		if (count($allList) > 0) {
			$sortedList = $this->sort_by_key('specifiedFlgNums', SORT_DESC, $allList);
			$uniqueList = $this->unique_by_clinicid($sortedList);
		}

		$data = new \stdClass();
		$data->clinics = array_slice($uniqueList, 0, $request['num']);
		//新規クリニックはDB登録
		$data->clinics = $this->insert_clinics($data->clinics);
		
		$data->title = $this->get_string($request, 'title');
		$data->mv_alt_default = $this->get_string($request, 'mv_alt');
		$data->description_default = $this->get_description_string($request);
		$data->lead_default = $this->get_lead_string($request, $data->clinics);

		$publish_at = new Carbon();
		$data->publish_at = $publish_at->format('Y-m-d');
		$data->updated_at = $publish_at->format('Y-m-d');
		if ($request['search_mode'] !== 'import') $this->insert_recommend($request, $data);

		$data->site = \Services\Factory::get_instance('site')->get_by_id(4);
		$data->image_dir = $this->get_image_dir($request);
		
		$header = new \stdClass();
		$header->js_list = $this->get_js_list('cms');
		$header->css_list = $this->get_css_list('cms');

		$value = new \stdClass();
		$value = $this->get_values(['recommend_id'=>0, 'type'=>(int)$request['recommend_type']]);

		/* 出力 */
		$template = ($request['search_mode'] === 'import') ? 'recommend/import_result.twig' : 'recommend/edit_form.twig';
		\Services\Render::render($this->_view, $this->_response, $template, [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value,
			'data' => $data
		]);

	}

	protected function show_preview()
	{
		$request = $this->_request->getParsedBody();

		//プレビューデータの保存
		\Services\Factory::get_instance('shinbi_recommend')->update($request, true);

		$site = \Services\Factory::get_instance('site')->get_by_id(4);
		$path = \Services\Factory::get_instance('shinbi_recommend')->get_url_by_id($request['recommend_id']);
		$data = ['preview_url' => $site->plus_url.$path.'?preview'];
		return \Services\Render::to_json($this->_response, $data);
	}

	protected function update()
	{
		$request = $this->_request->getParsedBody();

		//データの保存
		\Services\Factory::get_instance('shinbi_recommend')->update($request, false);
		\Services\Factory::get_instance('shinbi_recommend')->update($request, true);

		$data = ['is_error'=>false];
		return \Services\Render::to_json($this->_response, $data);
	}

	private function get_clinic_list($siteId, $sitePathname, $request)
	{
		$url = $_ENV['DENTAL_API_URL'].'get_clinic_info_v2.php?api_key='.$_ENV['DENTAL_API_KEY'];
		$url.= ($request['city_id']==='pref') ? '&pref_id='.$request['pref_id'] : 
		((!empty($request['city_id'])) ? '&city_id='.$request['city_id'] : '&station_group_id='.$request['station_group_id']);
		$url.= '&site_id='.$siteId;
		$list = json_decode(\Services\Curl::curl_request($url, false, [], $errno, $errmsg));
		$list = $this->get_hit_clinic($list, $request['attribute_flgname'], $siteId);
		$this->set_specified_flg_nums($sitePathname, $list);

		return $list;
	}

	private function get_hit_clinic($list, $selectedFlg, $siteId) {
		$site = \Services\Factory::get_instance('site')->get_by_id((int)$siteId);

		$childFlgs = \Services\Factory::get_instance('attribute')->get_child_list_by_flgname($selectedFlg);
		
		return array_filter($list, function($element) use($selectedFlg, $site, $childFlgs) {
			$hitFlg = false;
			foreach ($childFlgs as $child) {
				$flgname = $child->child_attribute_flgname;
				if (isset($element->$flgname)) {
					$hitFlg = true;
					break;
				}
			}

			if ($hitFlg) {
				$element->exists = ($this->exists($element->clinic_id)) ? true : false;
				$this->set_plus_clinic_info($element, $site);

				$planColName = $site->site_pathname.'_teikei';
				$sfPlanColName = 'sf_'.$site->site_pathname.'_teikei';
				return (((int)$element->$planColName > 0 || (int)$element->$sfPlanColName > 0 ) && $element->$flgname === "1");
			}
		});
	}

	private function exists($clinicId)
	{
		return \Services\Factory::get_instance('shinbi_clinic')->exists($clinicId);
	}

	private function get_pref_city_string($request)
    {
    	$str = '';

    	$city_name = ($request['city_name']!=='すべて')? $request['city_name'] : '';
		$str = ($request['pref_name'] !== '東京都' && mb_substr($request['city_name'], -1) === '区') ?
		$city_name  : $request['pref_name'].$city_name ;

		return $str;
    }

	private function get_string($request, $type = 'title')
	{
		$str = '';

		$str.= ($request['station_name']!== '') ? $request['station_name'].'駅周辺' : $this->get_pref_city_string($request);
		$str.= 'で'.$request['attribute_name'].'ができるおすすめ歯医者';
		$str.= $request['num'].'選';
		$str.= ($type === 'mv_alt') ? 'の画像' : '';

		return $str;
	}

	private function get_lead_string($request, $clinics)
	{
		$str = '';

		$str.= ($request['station_name']!== '') ? $request['station_name'].'駅周辺で' : $this->get_pref_city_string($request).'で';
		$str .= $request['attribute_name'].'ができるおすすめの歯医者さんをご紹介します。';

		$str.= ($request['station_name']!== '') ? $request['station_name'].'駅周辺で' : $this->get_pref_city_string($request).'で';
		$str .= $request['attribute_name'].'をするならどこのクリニック？何に注目して選ぶべき？そんなお悩みがある方に本記事はおすすめです。&#10;&#10;';

		$str .= 'この記事で紹介する';
		$str .= ($request['station_name']!== '') ? $request['station_name'].'駅周辺で' : $this->get_pref_city_string($request).'で';
		$str .= $request['attribute_name'].'ができるおすすめの歯科医院は下記の通りです。&#10;&#10;';

		foreach ($clinics as $clinic) {
			$str .= '・'.$clinic->clinic_name.'&#10;';
		}

		$str .= '&#10;';

		$str .= $request['attribute_name'].'対応の歯医者さん選びの参考として、アクセスや診療時間などの基本情報や医院ごとの特長、院内写真などを掲載しています。&#10;';
		$str .= $request['attribute_name'].'を検討中の方はぜひ読んでみてください。';

		return $str;
	}

	private function get_description_string($request)
	{
		$str = '';

		$str .= 'この記事では、';
		$str.= ($request['station_name']!== '') ? $request['station_name'].'駅で' : $this->get_pref_city_string($request).'で';
		$str .= $request['attribute_name'].'に対応しているおすすめの歯医者さんをご紹介します。';

		$str .= 'アクセスや診療時間などの基本情報や医院ごとの特長を掲載していますので、';
		$str.= ($request['station_name']!== '') ? $request['station_name'].'駅で' : $this->get_pref_city_string($request).'で';
		$str .= $request['attribute_name'].'をお考えの方は是非参考にしてください。';

		return $str;
	}

	private function get_image_dir($request)
	{
		$str = '/image/recommend/';

		if ($request['station_name'] !== '')
		{
			$station = \Services\Factory::get_instance('station')->get_by_group_id($request['station_group_id'], 'haisha');
			$str .= $station->pref_pathname.'/'.$station->station_pathname.'/';

		} else {
			$city = \Services\Factory::get_instance('city')->get_by_id($request['city_id'], 'haisha');
			$str .= $city->pref_pathname.'/'.$city->city_pathname.'/';
		}
		
		$attribute = \Services\Factory::get_instance('attribute')->get_by_flgname($request['attribute_flgname']);
		$str .= $attribute->attribute_pathname;

		return $str;
	}

	private function get_map_code_by_name($urlEncodedName)
	{
		return '<iframe src="https://www.google.com/maps?q='.$urlEncodedName.'&output=embed&t=m&z=16&hl=ja" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
	}

	private function sort_by_key($keyName, $sortOrder, $array)
	{
		foreach ($array as $key => $value) {
			$standardKeyArray[$key] = $value->$keyName;
		}
		array_multisort($standardKeyArray, $sortOrder, $array);

		return $array;
	}

	private function unique_by_clinicid($orgArray)
	{
		$tmp = array();
		$newArray = array();
		foreach( $orgArray as $key => $value ) {
			if( !in_array( $value->clinic_id, $tmp ) ) {
				$tmp[] = $value->clinic_id;
				$newArray[] = $value;
			}
		}
		return $newArray;
	}

	private function insert_clinics($list)
	{
		foreach ($list as $clinic) {
			$clinic->exists = \Services\Factory::get_instance('shinbi_clinic')->exists($clinic->clinic_id);
			$masterExists = \Services\Factory::get_instance('clinic')->exists($clinic->clinic_id);
			if (!$masterExists) {
				\Services\Factory::get_instance('clinic')->insert($clinic);
				foreach ($clinic->imageList as $key => $value) {
					if (\Services\Curl::get_status_code($clinic->siteUrl.$value) == 200) {
					if (exif_imagetype($clinic->siteUrl.$value)) {
						$image_id = \Services\Factory::get_instance('clinic_image', 'shinbi')->get_child_alternatekey('clinic', $clinic->clinic_id, 'image');
						$this->get_image_file($clinic->siteUrl.$value, $clinic->clinic_id, $image_id, 'shinbi');
					}						
					}
				}
			} else {
				/* 駅コード実装前に取り込まれたデータ対応 */
				$update_request = ['station_id_list' => $clinic->station_id_list, 'updated_at' => Carbon::now()];
				\Services\Factory::get_instance('clinic')->update_specific($clinic->clinic_id, $update_request);
			}
			if (!$clinic->exists) {
				\Services\Factory::get_instance('shinbi_clinic')->insert($clinic);
				if ($masterExists) {
					foreach ($clinic->imageList as $key => $value) {
						if (\Services\Curl::get_status_code($clinic->siteUrl.$value) == 200) {
						if (exif_imagetype($clinic->siteUrl.$value)) {
							$image_id = \Services\Factory::get_instance('clinic_image', 'shinbi')->get_child_alternatekey('clinic', $clinic->clinic_id, 'image');
							$this->get_image_file($clinic->siteUrl.$value, $clinic->clinic_id, $image_id, 'shinbi');
						}							
						}
					}
				}
			}
		}
		return $list;
	}

	private function insert_recommend($request, &$data)
	{
		$recommend_id = 0;

		$attribute_id = \Services\Factory::get_instance('attribute')->get_id_by_flgname($request['attribute_flgname'], 'shinbi');

		$city_id = ($request['city_id'] === '') ? 0 : (($request['city_id'] !== 'pref') ? $request['city_id'] : $request['pref_id']);
		$station_group_id = ($request['station_group_id'] === '') ? 0 : $request['station_group_id'];
		$param = ['city_id' => $city_id, 'attribute_id' => $attribute_id, 'station_group_id' => $station_group_id, 'title' => $data->title];
		$recommend_id = \Services\Factory::get_instance('shinbi_recommend')->insert($param);

		foreach ($data->clinics as $clinic) {
			$param = ['recommend_id' => $recommend_id, 'clinic_id' => $clinic->clinic_id];
			\Services\Factory::get_instance('shinbi_recommend_clinic')->insert($param);
		}
		
		$data->recommend_id = $recommend_id;
	}

	private function show_window($f, $d)
	{
        \Services\Render::render($this->_view, $this->_response, $f, $d);
	}

	/* pagenation配列取得 */
	private function get_pages($page, $last_page, $request)
	{
		$pages = [];
		$params = [];
		$prev_params = [];
		$next_params = [];
		$max = (($last_page - $page) > 3) ? 3 : $last_page - $page;

		if (isset($request['search_text'])) $params[] = 'search_text='.$request['search_text'];
		if (isset($request['type'])) $params[] = 'type='.(int)$request['type'];

		$prev_params = $params;
		if ($page !== 1) $prev_params[] = 'page='.($page-1);
		$pages['prev_href'] = (!empty($prev_params)) ? '?'.implode('&', $prev_params) : '';

		$next_params = $params;
		if ($page !== $last_page) $next_params[] = 'page='.($page+1);
		$pages['next_href'] = (!empty($next_params)) ? '?'.implode('&', $next_params) : '';

		for ($i = 0; $i <= $max; $i++) {
			$has_page = preg_grep('/page=[0-9]+/', $params);
			if ( $has_page ) {
				$key = array_keys($has_page);
				unset($params[$key[0]]);
			}

			if ($i === 0) {
				$pages['numbers'][$i] = ['anchor_class' => 'pagination__a--active', 'href' => '', 'text' => $page]; 
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
