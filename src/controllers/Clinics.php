<?php
namespace Controllers;

/* プラス 医院情報 */
class Clinics extends Base
{
	use \Values\Meta;
	use \Values\Clinics;

	private $search_codes = array('\r', '\r\n', '\n');
	private $replace_code = '\n';

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
						$func_name = ($path_array[3] === '0') ? 'show_add_form' : 'show_cms_detail';
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
		$page = (isset($request['page'])) ? $request['page'] : 1;
		$limit = 10;

		$list = new \stdClass();
		$list->search_text = (isset($request['search_text'])) ? $request['search_text'] : '';
		$list->data = \Services\Factory::get_instance($site_pathname.'_clinic')->get_cms_list($request,$page,$limit);
		$list->total = \Services\Factory::get_instance($site_pathname.'_clinic')->get_total_count($request);

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

	protected function show_cms_detail()
	{
		$pathname = $this->_request->getUri()->getPath();
		$path_array = explode('/', $pathname);
		$site_pathname = str_replace('_cms', '', $path_array[1]);
		$clinic_id = (int)$path_array[3];
		$site = \Services\Factory::get_instance('site')->get_by_pathname($site_pathname);

		$data = new \stdClass();
		$data->site = $site;
		$clinic = \Services\Factory::get_instance('clinic')->get_by_id($clinic_id);
		$clinic->stations = array_filter(explode('/', $clinic->station_id_list));
		$clinic->operation_times = \Services\Factory::get_instance('clinic_operation_time')->get_by_clinic_id($clinic_id);
		$site_clinic = \Services\Factory::get_instance($site_pathname.'_clinic')->get_by_id($clinic_id);
		$site_clinic->stations = array_filter(explode('/', $site_clinic->station_id_list));
		$site_clinic->operation_times = \Services\Factory::get_instance($site_pathname.'_operation_time')->get_by_clinic_id_cms($clinic_id);
		$this->set_display_data($clinic, $site_clinic);
		$data->clinic = $clinic;
		$data->site_clinic = $site_clinic;

		$data->profile = \Services\Factory::get_instance('profile')->get_by_clinic_id($clinic_id, true);

		if ($site_pathname === 'kyousei') {
			$data->invisalign = \Services\Factory::get_instance($site_pathname.'_self_clinic')->is_draft($clinic_id);
		}

		$data->recommend_id = ( !empty($this->_request->getQueryParam('recommend_id')) ) ?
		$this->_request->getQueryParam('recommend_id') : 0;

		$header = new \stdClass();
		$header->js_list = $this->get_js_list('cms');
		$header->css_list = $this->get_css_list('cms');

		$value = new \stdClass();
		$value = $this->get_values($site, $data->clinic);

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'clinic/edit_form.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value,
			'data' => $data
		]);
	}

	protected function show_add_form()
	{
		$pathname = $this->_request->getUri()->getPath();
		$path_array = explode('/', $pathname);
		$site_pathname = str_replace('_cms', '', $path_array[1]);
		$site = \Services\Factory::get_instance('site')->get_by_pathname($site_pathname);

		$header = new \stdClass();
		$header->js_list = $this->get_js_list('cms');
		$header->css_list = $this->get_css_list('cms');

		$value = new \stdClass();
		$value = $this->get_values($site, 0);

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'clinic/add_form.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value
		]);

	}

	protected function add()
	{
		$request = $this->_request->getParsedBody();

		$clinic = $this->insert_clinic($request);

		if (!$clinic) {
			$data = ['is_error'=>false];
			return \Services\Render::to_json($this->_response, $data);
		} else {
			$file = 'list/cms_clinic_row.html';
			$data = ['row'=>$clinic];
			\Services\Render::render($this->_view, $this->_response, $file, $data);
		}
	}

	protected function update()
	{
		$request = $this->_request->getParsedBody();
		$site_pathname = $request['site_pathname'];

		//修正前データの取得
		$prefix = ($site_pathname !== '') ? $site_pathname.'_' : '';
		$prefix2 = ($site_pathname !== '') ? $site_pathname.'_' : 'clinic_';
		$before = \Services\Factory::get_instance($prefix.'clinic')->get_by_id($request['clinic_id']);
		$before_operation_times = \Services\Factory::get_instance($prefix2.'operation_time')->get_by_clinic_id_cms($request['clinic_id']);

		///修正ログデータ
		$logs = array();
		$data = \Services\Factory::get_instance('site')->get_id_by_pathname($request['account_site_pathname']);
		$log_info = $this->get_log_info($data->site_id, $request['clinic_id']);
		$this->set_clinic_logs($before, $request, $log_info, $logs);
		$this->set_operation_times_logs($before_operation_times, $request, $log_info, $logs);
		foreach ($logs as $id => $log) {
			\Services\Factory::get_instance('log')->insert($log);
		}

		//データ更新
		$request['station_id_list'] =  (!empty($request['station_id_list'])) ?
		implode('/', array_filter($request['station_id_list'])) : NULL;
		
		\Services\Factory::get_instance($prefix.'clinic')->update($request);
		\Services\Factory::get_instance($prefix2.'operation_time')->delete_by_clinic_id($request['clinic_id']);
		$operation_times = $this->get_operation_time_array($request);
		foreach ($operation_times as $operation_time) {
			\Services\Factory::get_instance($prefix2.'operation_time')->insert($operation_time);
		}

		$data = ['is_error'=>false];
		return \Services\Render::to_json($this->_response, $data);
	}

	protected function update_feature()
	{
		$request = $this->_request->getParsedBody();
		$site_pathname = $request['site_pathname'];

		//データ更新
		\Services\Factory::get_instance($site_pathname.'_recommend_clinic')->update($request, true);

		\Services\Factory::get_instance($site_pathname.'_recommend_clinic_feature')->delete_by_group($request, true);
		for ($i=0; $i < count($request['feature_title']); $i++) {
			$feature = [
				'recommend_id' => $request['recommend_id'],
				'clinic_id' => $request['clinic_id'],
				'feature_id' => $i+1,
				'feature_title' => $request['feature_title'][$i],
				'feature_text' => $request['feature_text'][$i],
				'sort_order' => $i+1
				];
			\Services\Factory::get_instance($site_pathname.'_recommend_clinic_feature')->insert($feature, true);
		}

		$data = ['is_error'=>false];
		return \Services\Render::to_json($this->_response, $data);
	}

	protected function update_type2_feature()
	{
		$request = $this->_request->getParsedBody();
		$site_pathname = $request['site_pathname'];

		//データ更新
		\Services\Factory::get_instance($site_pathname.'_recommend_clinic')->update($request, true);

		\Services\Factory::get_instance($site_pathname.'_recommend_clinic_fee')->delete_by_group($request, true);
		for ($i=0; $i < count($request['fee_name']); $i++) {
			$fee = [
				'recommend_id' => $request['recommend_id'],
				'clinic_id' => $request['clinic_id'],
				'fee_id' => $i+1,
				'fee_name' => $request['fee_name'][$i],
				'fee' => $request['fee'][$i],
				'sort_order' => $i+1
				];
			\Services\Factory::get_instance($site_pathname.'_recommend_clinic_fee')->insert($fee, true);
		}

		\Services\Factory::get_instance($site_pathname.'_recommend_clinic_flow')->delete_by_group($request, true);
		for ($i=0; $i < count($request['flow_title']); $i++) {
			$flow = [
				'recommend_id' => $request['recommend_id'],
				'clinic_id' => $request['clinic_id'],
				'flow_id' => $i+1,
				'flow_title' => $request['flow_title'][$i],
				'flow_text' => $request['flow_text'][$i],
				'sort_order' => $i+1
				];
			\Services\Factory::get_instance($site_pathname.'_recommend_clinic_flow')->insert($flow, true);
		}

		\Services\Factory::get_instance($site_pathname.'_recommend_clinic_feature')->delete_by_group($request, true);
		for ($i=1; $i < 4; $i++) {
			$case_id = (isset($request['case_id'][$i])) ? (int)$request['case_id'][$i] : 0;
			$feature = [
				'recommend_id' => $request['recommend_id'],
				'clinic_id' => $request['clinic_id'],
				'feature_id' => $i,
				'feature_title' => $request['feature_title'][$i],
				'feature_text' => $request['feature_text'][$i],
				'case_id' => $case_id,
				'sort_order' => $i
				];
			\Services\Factory::get_instance($site_pathname.'_recommend_clinic_feature')->insert($feature, true);
		}

		$data = ['is_error'=>false];
		return \Services\Render::to_json($this->_response, $data);
	}

	protected function update_preview()
	{
		$request = $this->_request->getParsedBody();
		$site_pathname = $request['site_pathname'];

		//修正前データの取得
		$before = \Services\Factory::get_instance($site_pathname.'_recommend_clinic')->get_row($request['recommend_id'], $request['clinic_id']);
		$before_features = \Services\Factory::get_instance($site_pathname.'_recommend_clinic_feature')->get_cms_by_clinic_id($request['recommend_id'], $request['clinic_id']);
		$before_fees = \Services\Factory::get_instance($site_pathname.'_recommend_clinic_fee')->get_cms_by_clinic_id($request['recommend_id'], $request['clinic_id']);
		$before_flows = \Services\Factory::get_instance($site_pathname.'_recommend_clinic_flow')->get_cms_by_clinic_id($request['recommend_id'], $request['clinic_id']);

		//プレビューデータの取得
		$after = \Services\Factory::get_instance($site_pathname.'_recommend_clinic')->get_row($request['recommend_id'], $request['clinic_id'], true);
		$after_features = \Services\Factory::get_instance($site_pathname.'_recommend_clinic_feature')->get_cms_by_clinic_id($request['recommend_id'], $request['clinic_id'], true);
		$after_fees = \Services\Factory::get_instance($site_pathname.'_recommend_clinic_fee')->get_cms_by_clinic_id($request['recommend_id'], $request['clinic_id'], true);
		$after_flows = \Services\Factory::get_instance($site_pathname.'_recommend_clinic_flow')->get_cms_by_clinic_id($request['recommend_id'], $request['clinic_id'], true);

		///修正ログデータ
		/*
		$logs = array();
		$log_info = $this->get_log_info($request['site_id'], $request['clinic_id']);
		$this->set_clinic_logs($before, $after, $log_info, $logs);
		$this->set_feature_logs($before_features, $after_features, $log_info, $logs);
		foreach ($logs as $id => $log) {
			\Services\Factory::get_instance('log')->insert($log);
		}
		*/

		//データ更新
		\Services\Factory::get_instance($site_pathname.'_recommend_clinic')->update($after);

		\Services\Factory::get_instance($site_pathname.'_recommend_clinic_feature')->delete_by_group(['recommend_id'=>$request['recommend_id'], 'clinic_id'=>$request['clinic_id']]);
		foreach ($after_features as $id => $feature) {
			\Services\Factory::get_instance($site_pathname.'_recommend_clinic_feature')->insert($feature);
		}

		\Services\Factory::get_instance($site_pathname.'_recommend_clinic_fee')->delete_by_group(['recommend_id'=>$request['recommend_id'], 'clinic_id'=>$request['clinic_id']]);
		foreach ($after_fees as $id => $fee) {
			\Services\Factory::get_instance($site_pathname.'_recommend_clinic_fee')->insert($fee);
		}

		\Services\Factory::get_instance($site_pathname.'_recommend_clinic_flow')->delete_by_group(['recommend_id'=>$request['recommend_id'], 'clinic_id'=>$request['clinic_id']]);
		foreach ($after_flows as $id => $flow) {
			\Services\Factory::get_instance($site_pathname.'_recommend_clinic_flow')->insert($flow);
		}

		$data = ['is_error'=>false];
		return \Services\Render::to_json($this->_response, $data);
	}

	private function get_clinic_info($clinic_id, $site_id, $site_pathname)
	{
		$url = $_ENV['DENTAL_API_URL'].'get_clinic_info_v2.php?api_key='.$_ENV['DENTAL_API_KEY'];
		$url.= '&clinic_id='.$clinic_id;
		$url.= '&site_id='.$site_id;
		$list = json_decode(\Services\Curl::curl_request($url, false, [], $errno, $errmsg));
		
		$clinic = null;
		if (!empty($list)) {
			$site = \Services\Factory::get_instance('site')->get_by_id((int)$site_id);
			/* 
			if ($request['site_id'] == 2 && !$list[0]->implant_flg) { return null; }

			$planColName = $site->site_pathname.'_teikei';
			$sfPlanColName = 'sf_'.$site->site_pathname.'_teikei';
			if ((int)$list[0]->$planColName == 0 && (int)$list[0]->$sfPlanColName == 0) { return  null; }
			*/

			$this->set_specified_flg_nums($site_pathname, $list);
			
			$clinic = $list[0];
			$this->set_plus_clinic_info($clinic, $site);
		}
		
		return $clinic;
	}

	private function insert_clinic($request)
	{
		$exists = \Services\Factory::get_instance($request['site_pathname'].'_clinic')->exists($request['clinic_id']);
		$masterExists = \Services\Factory::get_instance('clinic')->exists($request['clinic_id']);

		$clinic = new \stdClass();
		$clinic = $this->get_clinic_info($request['clinic_id'], $request['site_id'], $request['site_pathname']);
		$other_sites = \Services\Factory::get_instance('site')->get_others($request['site_id']);
		foreach ($other_sites as $other_site) {
			if (empty($clinic)) $clinic = $this->get_clinic_info($request['clinic_id'], $other_site->site_id, $other_site->site_pathname);
		}
		
		$image_dir_exists = \Services\Factory::get_instance('clinic_image', $request['site_pathname'])->exists_by_clinic($clinic->clinic_id);

		$is_new = false;
		if (!$masterExists) {
			if (!empty($clinic)) {
				\Services\Factory::get_instance('clinic')->insert($clinic);
				if (!$image_dir_exists) {
					foreach ($clinic->imageList as $key => $value) {
						if (\Services\Curl::get_status_code($clinic->siteUrl.$value) == 200) {
							if (exif_imagetype($clinic->siteUrl.$value)) {
								$image_id = \Services\Factory::get_instance('clinic_image', $request['site_pathname'])->get_child_alternatekey('clinic', $request['clinic_id'], 'image');
								$this->get_image_file($clinic->siteUrl.$value, $clinic->clinic_id, $image_id, $request['site_pathname']);
							}
						}
					}
				}

				\Services\Factory::get_instance($request['site_pathname'].'_clinic')->insert($clinic);
				$is_new = true;
			}
		} else {
			if (!empty($clinic)) {
				/* 駅コード実装前に取り込まれたデータ対応 */
				$update_request = ['station_id_list' => $clinic->station_id_list];
				\Services\Factory::get_instance('clinic')->update_specific($clinic->clinic_id, $update_request);
			}
		}
		if ($masterExists && !$exists) {
			if (!empty($clinic)) {
				$clinic->clinic_id = $request['clinic_id'];
				\Services\Factory::get_instance($request['site_pathname'].'_clinic')->insert($clinic);
				if (!$image_dir_exists) {
					foreach ($clinic->imageList as $key => $value) {
						if (\Services\Curl::get_status_code($clinic->siteUrl.$value) == 200) {
						if (exif_imagetype($clinic->siteUrl.$value)) {
							$image_id = \Services\Factory::get_instance('clinic_image', $request['site_pathname'])->get_child_alternatekey('clinic', $request['clinic_id'], 'image');
							$this->get_image_file($clinic->siteUrl.$value, $clinic->clinic_id, $image_id, $request['site_pathname']);
						}
						}
					}
				}
				$is_new = true;
			}
		}

		$clinics = \Services\Factory::get_instance($request['site_pathname'].'_clinic')->get_cms_list(['search_text'=>$request['clinic_id']]);

		if (!empty($clinics)) {
			$clinics[0]->is_new = $is_new;
			return $clinics[0];
		} else {
			return false;
		}
	}

	private function set_display_data(&$clinic, &$site_clinic)
	{
		foreach ($clinic as $key => $value) {
			if (isset($site_clinic->{$key})) {
				if ($key === 'is_pr_reserve_tel_visible' || $key === 'is_pr_reserve_url_visible') {
					$site_clinic->{$key.'_edited'} = ($site_clinic->{$key} !== $clinic->{$key}) ? true : false;
				} elseif (!empty($site_clinic->{$key})) {
					$site_clinic->{$key.'_edited'} = true;
				} else {
					$site_clinic->{$key.'_edited'} = false;
				}
			}
		}
	}

	private function set_clinic_logs($before, &$request, $log_info, &$logs)
	{
		foreach ($before as $key => $value) {
			if (isset($request[$key])) {
				if (substr($key, 0, 3) !== 'is_' && (isset($request['is_'.$key.'_edited']) && $request['is_'.$key.'_edited'] == 0)) {
					$request[$key] = null;
				}

				$before_data = str_replace($this->search_codes, $this->replace_code, $before->{$key});
				$after_data = str_replace($this->search_codes, $this->replace_code, $request[$key]);

				if ($before_data != $after_data) {
					if ((substr($key, 0, 3) === 'is_') && (substr($key, -8, 8) === '_visible')) {
						$key = str_replace(array('is_', '_visible'), array('', ''), $key);
						$updated_item = $request[$key.'_ttl'].' 遷移先チェック';
					} else {
						$updated_item = $request[$key.'_ttl'];
					}

					$logs[] = [
						'account_id' => $log_info['account_id'],
						'site_id' => $log_info['site_id'],
						'recommend_id' => $log_info['recommend_id'],
						'clinic_id' => $log_info['clinic_id'],
						'feature_id' => $log_info['feature_id'],
						'updated_item' => $updated_item
					];
				}
			}
		}
	}

	private function set_operation_times_logs($before, $request, $log_info, &$logs)
	{
		$is_change = false;
		foreach ($before as $id => $row) {
			foreach ($row as $key => $value) {
				if (isset($request[$key][$id])) {
					if ($value != $request[$key][$id]) $is_change = true;
				}
			}
		}
		if ($is_change) {
			$logs[] = [
						'account_id' => $log_info['account_id'],
						'site_id' => $log_info['site_id'],
						'recommend_id' => $log_info['recommend_id'],
						'clinic_id' => $log_info['clinic_id'],
						'feature_id' => $log_info['feature_id'],
						'updated_item' => $request['operation_times_ttl']
					];
		}
	}

	private function set_features_logs($before, $request, $log_info, &$logs)
	{
		foreach ($before as $id => $row) {
			foreach ($row as $key => $value) {
				if (($key === 'feature_title' || $key === 'feature_text') && isset($request[$key][$id])) {

					$before_data = str_replace($this->search_codes, $this->replace_code, $value);
					$after_data = str_replace($this->search_codes, $this->replace_code, $request[$key][$id]);

					if ($before_data != $after_data) {
						$updated_item = $request[$key.'_'.($id+1).'_ttl'];
						$logs[] = [
							'account_id' => $log_info['account_id'],
							'site_id' => $log_info['site_id'],
							'recommend_id' => $log_info['recommend_id'],
							'clinic_id' => $log_info['clinic_id'],
							'feature_id' => $row->feature_id,
							'updated_item' => $updated_item
						];
					}
				}
			}
		}
	}

	private function get_log_info($site_id, $clinic_id, $recommend_id = null, $feature_id = null)
	{
		$account = $_SESSION['account_info_'.$site_id];
		return [
			'account_id' => $account->account_id,
			'site_id' => $site_id,
			'recommend_id' => $recommend_id,
			'clinic_id' => $clinic_id,
			'feature_id' => $feature_id
		];
	}

	private function get_operation_time_array($request)
	{
		$list = [];
		for ($i = 0; $i < count($request['start_at']); $i++) {
			if (!empty($request['start_at'][$i]) || !empty($request['end_at'][$i])) {
				$list[$i] = [
					'clinic_id' => $request['clinic_id'],
					'start_at' => $request['start_at'][$i],
					'end_at' => $request['end_at'][$i],
					'is_mon_open' => $request['is_mon_open'][$i],
					'is_tue_open' => $request['is_tue_open'][$i],
					'is_wed_open' => $request['is_wed_open'][$i],
					'is_thu_open' => $request['is_thu_open'][$i],
					'is_fri_open' => $request['is_fri_open'][$i],
					'is_sat_open' => $request['is_sat_open'][$i],
					'is_sun_open' => $request['is_sun_open'][$i]
				];
			}
		}
		return $list;

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
