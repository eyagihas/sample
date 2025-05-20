<?php
namespace Controllers;

class Parts extends Base
{
	use \Values\Meta;

	public function handler()
	{
		try {
			if ( $this->_request->isGet() )  {
				$func_name = $this->_request->getParam('mode');
				return $this->execute_method($func_name);
			} else {
				$this->redirect_404();
			}
		} catch (\Exception $e) {
			throw new \Exceptions\ErrorException($e);
		}
	}

	protected function get_pref_list()
	{
		$request = $this->_request->getQueryParams();
		$value = new \stdClass();
		$value->cities = \Services\Factory::get_instance('recommend')->get_active_pref_list();

		$file = 'city/portal_pref_'.$request['type'].'.html';
		$data = ['value'=>$value];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_top_station_list()
	{
		$request = $this->_request->getQueryParams();
		$value = new \stdClass();
		$value->stations = \Services\Factory::get_instance('recommend')->get_active_station_list(0);

		$file = 'station/portal_'.$request['type'].'.html';
		$data = ['value'=>$value];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_top_recommend_list()
	{
		$value = new \stdClass();

		$value->recommends = \Services\Factory::get_instance('recommend')->get_portal_list([],1,5,'all');
		$value->row_type = 'slide';

		$headline = new \stdClass();
		$headline->path = '/recommend/';
		$value->headline = $headline;

		$file = 'recommend/portal_row.html';
		$data = ['value'=>$value];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_top_profile_list()
	{
		$site_pathname = $this->_request->getParam('site_pathname');
		$value = new \stdClass();
		$value->profiles = \Services\Factory::get_instance('profile')->get_portal_list(['site_pathname' => $site_pathname, 'desc' => true]);

		$file = 'profile/portal_top_row.html';
		$data = ['value'=>$value];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_profile_list()
	{
		$request = $this->_request->getQueryParams();
		$value = new \stdClass();
		$value->profiles = \Services\Factory::get_instance('profile')->get_cms_list($request);

		$file = 'profile/profile_search_row.html';
		$data = ['data'=>$value];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_city()
	{
		$request = $this->_request->getQueryParams();
		$url = $_ENV['DENTAL_API_URL'].'get_city_id.php?api_key='.$_ENV['DENTAL_API_KEY'].'&pref_id='.$request['pref_id'];
		$value = json_decode(\Services\Curl::curl_request($url, false, [], $errno, $errmsg));
		$file = 'add/city.html';
		$data = ['cities'=>$value];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_order_list()
	{
		$request = $this->_request->getQueryParams();

		$orders = \Services\Factory::get_instance($request['site'].'_recommend_clinic')->get_recommend_clinic_orders($request['recommend_id']);
		$file = 'add/recommend_clinic_order.html';
		$data = ['orders'=>$orders];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_case_order_list()
	{
		$request = $this->_request->getQueryParams();

		$orders = \Services\Factory::get_instance($request['site'].'_case')->get_case_orders($request['clinic_id'], $request['case_attribute_id']);
		$file = 'add/case_order.html';
		$data = ['orders'=>$orders];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_now_edited_recommend()
	{
		$request = $this->_request->getQueryParams();

		$list = new \stdClass();

		$list->recommends = \Services\Factory::get_instance($request['site'].'_recommend_clinic')->get_list_by_clinic($request['clinic_id'], 1, 1, $request['recommend_id']);

		$file = 'clinic/clinic_now_edited_recommend.html';
		$data = ['list'=>$list];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_clinics_by_recommend()
	{
		$request = $this->_request->getQueryParams();

		$value = new \stdClass();
		$value->site_pathname = $request['site'];

		$clinics = \Services\Factory::get_instance($request['site'].'_recommend_clinic')->get_list($request['recommend_id'], false, true);
		foreach ($clinics as $clinic) {
			$features = \Services\Factory::get_instance($request['site'].'_recommend_clinic_feature')->get_cms_by_clinic_id($request['recommend_id'], $clinic->clinic_id, false);
			$clinic->feature_title = $this->implode_data($features, 'feature_title');
			$clinic->feature_text = $this->implode_data($features, 'feature_text');

			$flows = \Services\Factory::get_instance($request['site'].'_recommend_clinic_flow')->get_cms_by_clinic_id($request['recommend_id'], $clinic->clinic_id, false);
			$clinic->flow_title = $this->implode_data($flows, 'flow_title');
			$clinic->flow_text = $this->implode_data($flows, 'flow_text');
		}

		$preview_clinics = \Services\Factory::get_instance($request['site'].'_recommend_clinic')->get_list($request['recommend_id'], true, true);
		foreach ($preview_clinics as $clinic) {
			$features = \Services\Factory::get_instance($request['site'].'_recommend_clinic_feature')->get_cms_by_clinic_id($request['recommend_id'], $clinic->clinic_id, true);
			$clinic->feature_title = $this->implode_data($features, 'feature_title');
			$clinic->feature_text = $this->implode_data($features, 'feature_text');

			$flows = \Services\Factory::get_instance($request['site'].'_recommend_clinic_flow')->get_cms_by_clinic_id($request['recommend_id'], $clinic->clinic_id, true);
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
			if ( $request['clinic_id'] == $preview->clinic_id) {
				$index = array_search($preview->clinic_id, array_map(function ($v) { return $v->clinic_id; }, $clinics));
				unset($clinics[$index]);
			}
		}

		$value->clinics = $clinics;
		$value->recommend_id = $request['recommend_id'];

		$file = 'clinic/other_clinic.html';
		$data = ['value'=>$value];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_recommends_by_clinic()
	{
		$request = $this->_request->getQueryParams();

		$list = new \stdClass();
		$headline = new \stdClass();
		$page = (isset($request['page'])) ? $request['page'] : null;
		$limit = (isset($request['page'])) ? 10 : null;
		$is_pr = (isset($request['type']) && $request['type'] === 'profile') ? true : false;

		$list->recommends = \Services\Factory::get_instance($request['site'].'_recommend_clinic')->get_list_by_clinic($request['clinic_id'], $page, $limit, 0, $is_pr);
		$list->total = \Services\Factory::get_instance($request['site'].'_recommend_clinic')->get_total_by_clinic($request['clinic_id']);


		$headline = new \stdClass();
		$headline->page = $page;
		$headline->last_page = (!empty($limit)) ? ceil($list->total/$limit) : null;
		$list->headline = $headline;
		$list->pages = $this->get_pages($headline->page, $headline->last_page, $request['site']);

		$type = (isset($request['type'])) ? $request['type'] : 'clinic';
		$file = $type.'/'.$type.'_recommend_list.html';
		$data = ['list'=>$list];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_recommend_features_by_clinic()
	{
		$request = $this->_request->getQueryParams();

		$value = new \stdClass();
		$value->site_pathname = $request['site'];
		$value->detail = \Services\Factory::get_instance($request['site'].'_recommend_clinic')->get_detail($request['recommend_id'], $request['clinic_id'], true);
		$value->images = \Services\Factory::get_instance('clinic_image', $request['site'])->get_list($request['clinic_id'], $request['site']);
		$value->features = \Services\Factory::get_instance($request['site'].'_recommend_clinic_feature')->get_cms_by_clinic_id($request['recommend_id'], $request['clinic_id'], true);
		$value->site = \Services\Factory::get_instance('site')->get_by_pathname($request['site']);

		$file = 'clinic/feature.html';
		$data = ['value'=>$value];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_recommend_type2_features_by_clinic()
	{
		$request = $this->_request->getQueryParams();

		$value = new \stdClass();
		$value->site_pathname = $request['site'];

		$self = \Services\Factory::get_instance($request['site'].'_self_clinic')->is_draft($request['clinic_id']);
		$is_never_updated = \Services\Factory::get_instance($request['site'].'_recommend_clinic')->is_never_updated($request['recommend_id'], $request['clinic_id']);

		$value->detail = \Services\Factory::get_instance($request['site'].'_recommend_clinic')->get_detail($request['recommend_id'], $request['clinic_id'], true);
		if (!empty($self) && ($self->is_draft === 0 && $is_never_updated)) {
			$self_detail = \Services\Factory::get_instance($request['site'].'_self_clinic')->get_by_id($request['clinic_id']);
			$value->detail->treatment_times = $self_detail->invisalign_treatment_times;
			$value->detail->treatment_duration = $self_detail->invisalign_treatment_times;
		}

		$value->images = \Services\Factory::get_instance('clinic_image', $request['site'])->get_list($request['clinic_id'], $request['site']);
		$value->features = \Services\Factory::get_instance($request['site'].'_recommend_clinic_feature')->get_cms_by_clinic_id($request['recommend_id'], $request['clinic_id'], true);

		if (!empty($self) && ($self->is_draft === 0 && $is_never_updated)) {
			$value->fees = \Services\Factory::get_instance($request['site'].'_self_invisalign_fee')->get_by_clinic_id_cms($request['clinic_id']);
			$value->flows = \Services\Factory::get_instance($request['site'].'_self_invisalign_flow')->get_by_clinic_id_cms($request['clinic_id']);
		} else {
			$value->fees = \Services\Factory::get_instance($request['site'].'_recommend_clinic_fee')->get_cms_by_clinic_id($request['recommend_id'], $request['clinic_id'], true);
			$value->flows = \Services\Factory::get_instance($request['site'].'_recommend_clinic_flow')->get_cms_by_clinic_id($request['recommend_id'], $request['clinic_id'], true);
		}
		$value->site = \Services\Factory::get_instance('site')->get_by_pathname($request['site']);

		$file = 'clinic/type2_feature.html';
		$data = ['value'=>$value];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_cases_by_clinic()
	{
		$request = $this->_request->getQueryParams();

		$list = new \stdClass();
		$headline = new \stdClass();
		$page = (isset($request['page'])) ? $request['page'] : null;
		$limit = (isset($request['page'])) ? 10 : null;

		$cases = \Services\Factory::get_instance($request['site'].'_case')->get_list_by_clinic($request['clinic_id'], null, $page, $limit);
		$preview_cases = \Services\Factory::get_instance($request['site'].'_case')->get_list_by_clinic($request['clinic_id'], null, $page, $limit, true);
		$list->total = \Services\Factory::get_instance($request['site'].'_case')->get_total_count($request['clinic_id']);

		foreach ($preview_cases as $id => $preview) {
			$cases[$id]->is_edited = ($preview != $cases[$id]) ? 1 : 0;
		}
		$list->cases = $cases;

		$headline = new \stdClass();
		$headline->page = $page;
		$headline->last_page = (!empty($limit)) ? ceil($list->total/$limit) : null;
		$list->headline = $headline;
		$list->pages = $this->get_pages($headline->page, $headline->last_page, $request['site']);

		$file = 'clinic/clinic_case_list.html';
		$data = ['list'=>$list];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_case_by_id()
	{
		$request = $this->_request->getQueryParams();

		$value = new \stdClass();
		$value->site_pathname = $request['site'];

		$value->detail = \Services\Factory::get_instance($request['site'].'_case')->get_detail($request['case_id'], true);
		$value->before_images = \Services\Factory::get_instance('case_image', $request['site'])->get_list($request['case_id'], $request['site'], 'before');
		$value->after_images = \Services\Factory::get_instance('case_image', $request['site'])->get_list($request['case_id'], $request['site'], 'after');
		$value->site = \Services\Factory::get_instance('site')->get_by_pathname($request['site']);

		$file = 'case/case_info.html';
		$data = ['value'=>$value];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_self_cases_by_clinic()
	{
		$request = $this->_request->getQueryParams();

		$list = new \stdClass();
		$list->cases = \Services\Factory::get_instance($request['site'].'_self_clinic_feature')->get_list_by_clinic($request['clinic_id'], 2);

		$file = 'case/self_case_list.html';
		$data = ['list'=>$list];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_city_list()
	{
		$request = $this->_request->getQueryParams();

		$data = new \stdClass();
		$data->search_text = $request['search_text'];
		$data->cities = \Services\Factory::get_instance('city')->get_list($request);

		$file = 'city/cms_city_row.html';
		$data = ['data'=>$data];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_clinic_list()
	{
		$request = $this->_request->getQueryParams();

		$data = new \stdClass();
		$data->search_text = $request['search_text'];
		$data->clinics = \Services\Factory::get_instance($request['site_pathname'].'_clinic')->get_cms_list($request);

		$file = 'clinic/clinic_search_row.html';
		$data = ['data'=>$data];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_station_list()
	{
		$request = $this->_request->getQueryParams();

		$data = new \stdClass();
		$data->search_text = $request['search_text'];
		$data->stations = \Services\Factory::get_instance('station')->get_cms_group_list($request);

		$file = 'station/station_search_row.html';
		$data = ['data'=>$data];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_basic_clinic_info()
	{
		$request = $this->_request->getQueryParams();

		$data = new \stdClass();
		$clinic = \Services\Factory::get_instance('clinic')->get_by_id($request['clinic_id']);
		$clinic->stations = array_filter(explode('/', $clinic->station_id_list));
		$clinic->operation_times = \Services\Factory::get_instance('clinic_operation_time')->get_by_clinic_id($request['clinic_id']);
		$data->clinic = $clinic;

		$file = 'clinic/basic_info.html';
		$data = ['data'=>$data];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}


	protected function add_clinic()
	{
		$request = $this->_request->getQueryParams();

		//重複チェック
		$exists = \Services\Factory::get_instance($request['site_pathname'].'_recommend_clinic')->exists($request['recommend_id'], $request['clinic_id']);

		if ($exists) {
			$data = ['is_error'=>true];
			return \Services\Render::to_json($this->_response, $data);
		} else {
			//データの保存
			\Services\Factory::get_instance($request['site_pathname'].'_recommend_clinic')->insert($request, true);

			$file = 'clinic/recommend_clinic_row.html';
			$data = ['clinic'=>$request];
			\Services\Render::render($this->_view, $this->_response, $file, $data);
		}
	}

	protected function delete_clinic()
	{
		$request = $this->_request->getQueryParams();

		//データの削除
		\Services\Factory::get_instance($request['site_pathname'].'_recommend_clinic')->delete_by_row($request['recommend_id'], $request['clinic_id']);
		\Services\Factory::get_instance($request['site_pathname'].'_recommend_clinic')->delete_by_row($request['recommend_id'], $request['clinic_id'], true);
		\Services\Factory::get_instance($request['site_pathname'].'_recommend_clinic_feature')->delete_by_group($request);
		\Services\Factory::get_instance($request['site_pathname'].'_recommend_clinic_feature')->delete_by_group($request, true);


		$data = ['is_error'=>false];
		\Services\Render::to_json($this->_response, $data);
	}

	protected function delete_tag()
	{
		$request = $this->_request->getQueryParams();

		//データの削除
		\Services\Factory::get_instance('tag')->delete_by_id($request['tag_id']);
		\Services\Factory::get_instance('recommend_tag', $request['site_pathname'])->delete_by_tag($request['tag_id']);
		\Services\Factory::get_instance('recommend_tag', $request['site_pathname'])->delete_by_tag($request['tag_id'], true);


		$data = ['is_error'=>false];
		\Services\Render::to_json($this->_response, $data);
	}

	protected function get_tag_info()
	{
		$request = $this->_request->getQueryParams();

		$data = new \stdClass();
		$tag = \Services\Factory::get_instance('tag')->get_by_id($request['tag_id']);
		$tag->recommends = \Services\Factory::get_instance('recommend_tag', $request['site_pathname'])->get_by_tag_id($request['tag_id']);
		$data->tag = $tag;

		$value = new \stdClass();
		$value->site_pathname = $request['site_pathname'];

		$file = 'tag/edit_form.html';
		$data = ['data'=>$data, 'value'=>$value];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_city_info()
	{
		$request = $this->_request->getQueryParams();

		$data = new \stdClass();
		$city = \Services\Factory::get_instance('city')->get_by_id($request['city_id'], $request['site_pathname']);
		$city->recommends = \Services\Factory::get_instance($request['site_pathname'].'_recommend')->get_cms_list(['city_id' => $request['city_id']]);
		$data->city = $city;

		$value = new \stdClass();
		$value->site = \Services\Factory::get_instance('site')->get_by_pathname($request['site_pathname']);

		$file = 'city/edit_form.html';
		$data = ['data'=>$data, 'value'=>$value];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_station_info()
	{
		$request = $this->_request->getQueryParams();

		$data = new \stdClass();
		$station = \Services\Factory::get_instance('station')->get_by_group_id($request['station_group_id'], $request['site_pathname']);
		$station->recommends = \Services\Factory::get_instance($request['site_pathname'].'_recommend')->get_cms_list(['station_group_id' => $request['station_group_id']]);
		$data->station = $station;

		$value = new \stdClass();
		$value->site = \Services\Factory::get_instance('site')->get_by_pathname($request['site_pathname']);

		$file = 'station/edit_form.html';
		$data = ['data'=>$data, 'value'=>$value];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_operation_time_row()
	{
		$file = 'invisalign/operation_time_row.html';
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_fee_row()
	{
		$file = 'invisalign/fee_row.html';
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_flow_row()
	{
		$file = 'invisalign/flow_row.html';
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_feature_fee()
	{
		$file = 'add/fee.html';
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_feature_flow()
	{
		$file = 'add/flow.html';
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_basic_feature()
	{
		$request = $this->_request->getQueryParams();

		$value = new \stdClass();
		$value->feature_id = $request['feature_id'];
		$value->feature_id_circle = $request['feature_id_circle'];

		$file = 'add/basic_feature.html';
		$data = ['value'=>$value];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_case_feature()
	{
		$request = $this->_request->getQueryParams();

		$value = new \stdClass();
		$value->feature_id = $request['feature_id'];
		$value->feature_id_circle = $request['feature_id_circle'];

		$recommend = \Services\Factory::get_instance($request['site_pathname'].'_recommend')->get_attribute_id($request['recommend_id']);
		$value->cases = \Services\Factory::get_instance($request['site_pathname'].'_case')->get_cms_for_feature($request['clinic_id'], $recommend->attribute_id);

		$file = 'add/case_feature.html';
		$data = ['value'=>$value];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function unrelate_profile()
	{
		$request = $this->_request->getQueryParams();
		\Services\Factory::get_instance('profile_clinic')->unrelate($request);

		$data = ['is_error'=>false];
		\Services\Render::to_json($this->_response, $data);
	}

	protected function get_city_station()
	{
		$request = $this->_request->getQueryParams();

		$city_id = ($request['city_id'] === 'pref') ? $request['pref_id'] : $request['city_id'];
		$row = (!empty($city_id)) ?
		\Services\Factory::get_instance('top_city_station', $request['site_pathname'])->get_city($city_id):
		\Services\Factory::get_instance('top_city_station', $request['site_pathname'])->get_station($request['station_group_id']);

		$row->postnum = (!empty($city_id)) ?
		\Services\Factory::get_instance($request['site_pathname'].'_recommend')->get_postnum_by_city($city_id):
		\Services\Factory::get_instance($request['site_pathname'].'_recommend')->get_postnum_by_station($request['station_group_id']);

		$file = 'list/cms_link_row.html';
		$data = ['row'=>$row];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_recommend()
	{
		$request = $this->_request->getQueryParams();

		$request['city_id'] = ($request['city_id'] === 'pref') ? $request['pref_id'] : $request['city_id'];
		$row = \Services\Factory::get_instance('top_recommend', $request['site_pathname'])->get_recommend($request);

		if (!empty($row)) {
			$row->has_pr = (\Services\Factory::get_instance($request['site_pathname'].'_recommend_clinic')->exists_pr($row->recommend_id)) ?
			true : false;
		}

		$file = 'list/cms_link_row.html';
		$data = ['row'=>$row];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_top_city_station_cmslist()
	{
		$request = $this->_request->getQueryParams();

		$list = new \stdClass();
		$headline = new \stdClass();
		$page = (isset($request['page'])) ? $request['page'] : 1;
		$limit = 10;

		$links = \Services\Factory::get_instance('top_city_station', $request['site_pathname'])->get_list($request,$page,$limit);
		foreach ($links as $row) {
			$row->postnum = (!empty($row->city_id)) ?
			\Services\Factory::get_instance($request['site_pathname'].'_recommend')->get_postnum_by_city($row->city_id):
			\Services\Factory::get_instance($request['site_pathname'].'_recommend')->get_postnum_by_station($row->station_group_id);
		}
		$list->links = $links;
		$list->total = \Services\Factory::get_instance('top_city_station', $request['site_pathname'])->get_total_count($request);

		$headline->page = $page;
		$headline->last_page = (!empty($limit)) ? ceil($list->total/$limit) : null;
		$list->headline = $headline;
		$list->pages = $this->get_pages($headline->page, $headline->last_page, $request['site_pathname']);

		$file = 'internal_link/top_link_list.html';
		$data = ['list'=>$list];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function get_top_recommend_cmslist()
	{
		$request = $this->_request->getQueryParams();

		$list = new \stdClass();
		$headline = new \stdClass();
		$page = (isset($request['page'])) ? $request['page'] : 1;
		$limit = 10;

		$links = \Services\Factory::get_instance('top_recommend', $request['site_pathname'])->get_list(null,$page,$limit);
		foreach ($links as $row) {
			$row->has_pr = (\Services\Factory::get_instance($request['site_pathname'].'_recommend_clinic')->exists_pr($row->recommend_id)) ?
			true : false;
		}
		$list->links = $links;
		$list->total = \Services\Factory::get_instance('top_recommend', $request['site_pathname'])->get_total_count();

		$headline->page = $page;
		$headline->last_page = (!empty($limit)) ? ceil($list->total/$limit) : null;
		$list->headline = $headline;
		$list->pages = $this->get_pages($headline->page, $headline->last_page, $request['site_pathname']);

		$file = 'internal_link/top_link_list.html';
		$data = ['list'=>$list];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}


	/* pagenation配列取得 */
	private function get_pages($page, $last_page, $site_pathname = 'kyousei')
	{
		$pages = [];
		$max = (($last_page - $page) > 3) ? 3 : $last_page - $page;

		for ($i = 0; $i <= $max; $i++) {
			if ($i === 0) {
				$pages['numbers'][$i] = ['anchor_class' => 'pagination__a--active '.$site_pathname.'-color', 'href' => '', 'text' => $page];
			} elseif ($i === 2 && ($last_page - $page) > 3) {
				$pages['numbers'][$i] = ['anchor_class' => '', 'href' => '', 'text' => '<span class="pagination__text">...</span>'];
			} elseif($i === 3 && ($last_page - $page) > 3) {
				$pages['numbers'][$i] = ['anchor_class' => 'pagination__a', 'href' => '', 'text' => $last_page];
			} else {
				$pages['numbers'][$i] = ['anchor_class' => 'pagination__a', 'href' => '', 'text' => $page+$i];
			}
		}
		return $pages;
	}

}
