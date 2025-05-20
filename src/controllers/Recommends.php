<?php
namespace Controllers;

use Carbon\Carbon as Carbon;

class Recommends extends Base
{
	use \Values\Meta;
	use \Values\Recommends;

    public function handler()
    {
		try {
			if ( $this->_request->isGet() )  {
				$path_array = explode('/',$this->_request->getUri()->getPath());
				if ( count($path_array) === 3 ) {
					$func_name = 'show_list';
				} elseif ( count($path_array) === 4 ) {
					$func_name = 'show_pref';
				} elseif ( count($path_array) === 5 ) {
					$func_name =  (strpos($path_array[3],'-area') !== false || strpos($path_array[3],'pref') !== false) ? 'show_city' : 'show_station';
				} elseif ( count($path_array) === 6 ) {
					$func_name = 'show_detail';
				}
				return $this->execute_method($func_name);
			}
		} catch (\Exception $e) {
			throw new \Exceptions\ErrorException($e);
		}

	}

	/* 記事一覧ページ */
	protected function show_list ()
	{
		$header = new \stdClass();
		$header->css_list = $this->get_css_list('portal_list');
		$header->js_list = $this->get_js_list('portal_list');

		$value = new \stdClass();

		$request = $this->_request->getQueryParams();

		$request['is_published'] = 1;
		$value->type = (isset($request['station'])) ? 'station' : 'area';
		$value->total = \Services\Factory::get_instance('recommend')->get_total_count($request,$value->type);

		if ($value->type === 'area' && $value->total === 0) {
			$value->type = 'station';
			$value->total = \Services\Factory::get_instance('recommend')->get_total_count($request,'station');
		}

		$page = (isset($request['page'])) ? $request['page'] : 1;
		$limit = 12;
		$value->recommends = \Services\Factory::get_instance('recommend')->get_portal_list($request,$page,$limit,$value->type);
		if ( count($value->recommends) === 0 ) {
			return $this->redirect_404();
		}

		$value->prefectures = \Services\Factory::get_instance('recommend')->get_active_pref_list();
		$value->stations = \Services\Factory::get_instance('recommend')->get_active_station_list(0);

		$headline = new \stdClass();
		$headline->path = $this->_request->getUri()->getPath();
		$headline->canonical = $this->get_canonical();
		$headline->page = $page;
		$headline->last_page = ceil($value->total/$limit);
		$value->headline = $headline;

		$value->includes = $this->get_includes('implant');
		$value->breadcrumb = $this->get_breadcrumb($headline);
		$value->pages = $this->get_pages($page, $headline->last_page, $value->type);
		$value->row_type = 'list';

		/* 出力 */
		var_dump($this->_response);
		\Services\Render::render($this->_view, $this->_response, 'recommend/portal_list.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value
		]);
	}

	/* 都道府県ページ */
	protected function show_pref ()
	{
		$city = $this->validate_city_path();
		if ( empty($city) ) {
			return $this->redirect_404();
		}

		$header = new \stdClass();
		$header->css_list = $this->get_css_list('portal_city');
		$header->js_list = $this->get_js_list('portal_city');

		$value = new \stdClass();

		$request = $this->_request->getQueryParams();
		$request['is_published'] = 1;
		$request['pref_id'] = $city->pref_id;
		$request['type'] = (isset($request['station'])) ? 'pref_station' : 'pref_area';
		$value->type = $request['type'];
		$value->total = \Services\Factory::get_instance('recommend')->get_total_count($request,$value->type);

		$page = (isset($request['page'])) ? $request['page'] : 1;
		$limit = 12;
		$value->recommends = \Services\Factory::get_instance('recommend')->get_portal_list_by_pref($request,$page,$limit);

		if ( count($value->recommends) === 0 ) {
			return $this->redirect_404();
		}

		$value->cities = \Services\Factory::get_instance('recommend')->get_active_city_list($city->city_id);
		$value->stations = \Services\Factory::get_instance('recommend')->get_active_station_list($city->city_id);

		$city->page = $page;
		$value->city = $city;

		$headline = new \stdClass();
		$headline->path = $this->_request->getUri()->getPath();
		$headline->canonical = $this->get_canonical();
		$headline->page = $page;
		$headline->last_page = ceil($value->total/$limit);
		$value->headline = $headline;

		$value->includes = $this->get_includes('implant');
		$value->breadcrumb = $this->get_city_breadcrumb($city);
		$value->pages = $this->get_pages($page, $headline->last_page, $value->type);
		$value->row_type = 'list';

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'recommend/portal_pref.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value
		]);
	}

	/* 市区町村ページ */
	protected function show_city ()
	{
		$city = $this->validate_city_path();
		if ( empty($city) ) {
			return $this->redirect_404();
		}

		$header = new \stdClass();
		$header->css_list = $this->get_css_list('portal_city');
		$header->js_list = $this->get_js_list('portal_city');

		$value = new \stdClass();
		$value->city = $city;

		$request = [ 'city_id' => $city->city_id ];
		$value->recommends = \Services\Factory::get_instance('recommend')->get_portal_list_by_city($request);

		if ( count($value->recommends) === 0 ) {
			return $this->redirect_404();
		}

		$headline = new \stdClass();
		$headline->path = $this->_request->getUri()->getPath();
		$headline->canonical = $this->get_canonical();
		$value->headline = $headline;

		$value->includes = $this->get_includes('implant');
		$value->breadcrumb = $this->get_city_breadcrumb($city);
		$value->row_type = 'list';

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'recommend/portal_city.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value
		]);
	}

	/* 駅ページ */
	protected function show_station ()
	{
		$station = $this->validate_station_path();
		if ( empty($station) ) {
			return $this->redirect_404();
		}

		$header = new \stdClass();
		$header->css_list = $this->get_css_list('portal_city');
		$header->js_list = $this->get_js_list('portal_city');

		$value = new \stdClass();
		$value->station = $station;

		$request = [ 'station_group_id' => $station->station_group_id ];
		$value->recommends = \Services\Factory::get_instance('recommend')->get_portal_list_by_station($request);

		if ( count($value->recommends) === 0 ) {
			return $this->redirect_404();
		}

		$headline = new \stdClass();
		$headline->path = $this->_request->getUri()->getPath();
		$headline->canonical = $this->get_canonical();
		$value->headline = $headline;

		$value->includes = $this->get_includes('implant');
		$value->breadcrumb = $this->get_station_breadcrumb($station);
		$value->row_type = 'list';

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'recommend/portal_station.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value
		]);
	}

	/* おすすめ記事ページ */
	protected function show_detail ()
	{
		$site = \Services\Factory::get_instance('site')->get_by_id(2);

		$data = $this->validate_detail_path();
		if ( empty($data) ) {
			return $this->redirect_404();
		}
		$data->base_url = $this->_request->getUri()->getBaseUrl();
		$data->canonical = $this->get_canonical();

		$request = $this->_request->getQueryParams();
		$isPreview = (isset($request['preview'])) ? true : false;
		$data->ga_index = ($isPreview) ? 'noindex,nofollow' : 'index,follow';

		$clinics = \Services\Factory::get_instance('implant_clinic')->get_portal_list($data->recommend_id, $isPreview);
		$recommend = \Services\Factory::get_instance('recommend')->get_by_id($data->recommend_id, $isPreview);
		if ( !isset($request['preview']) && !($recommend->is_published === 1 && $recommend->publish_at <= Carbon::now()) ) {
			return $this->redirect_404();
		}

		$has_pr = false;
		$article_body = '';
		$jsonld_image = '';
		foreach ( $clinics as $val ) {
			$val->features = \Services\Factory::get_instance('implant_recommend_clinic_feature')->get_by_clinic_id($data->recommend_id,$val->clinic_id, $isPreview);
			$val->has_case = false;
			foreach ($val->features as $feature) {
				if ($feature->case_id > 0) {
					$val->has_case = true;
					break;
				}
			}
			$val->operation_times = \Services\Factory::get_instance('implant_operation_time')->get_by_clinic_id($val->clinic_id);
			$val->profile = \Services\Factory::get_instance('profile')->get_by_clinic_id($val->clinic_id, $isPreview, 'implant');

			if ($val->price_plan > 0) {
				$has_pr = true;
				$article_body .= $this->get_article_body($val);
				$jsonld_image .= $this->get_jsonld_image($val, $site->plus_url);
			}

			if ($data->implant_attribute_type === 2) {
				$val->fees = \Services\Factory::get_instance('implant_recommend_clinic_fee')->get_by_clinic_id($data->recommend_id, $val->clinic_id, $isPreview);

				$val->flows = \Services\Factory::get_instance('implant_recommend_clinic_flow')->get_by_clinic_id($data->recommend_id, $val->clinic_id, $isPreview);
			}
		}
		$clinics = $this->sort_clinics($clinics);

		$header = new \stdClass();
		$header->css_list = $this->get_css_list('portal_detail');
		$header->js_list = $this->get_js_list('portal_detail');

		$value = new \stdClass();
		$value->includes = $this->get_includes('implant');
		$value->breadcrumb = $this->get_breadcrumb($recommend);
		$value->headline = $data;
		$value->recommend = $recommend;
		$value->clinics = $clinics;
		$value->is_preview = $isPreview;
		$value->has_pr = $has_pr;
		$value->article_body = $article_body;
		$value->jsonld_image = $jsonld_image;
		$value->site  = $site;

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'recommend/portal_detail.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value
		]);
	}

	private function sort_clinics($clinics)
	{
		$list = [];
		$free_key = 11;
		foreach ($clinics as $clinic) {
			if (!empty($clinic->sort_order)) {
				$list[$clinic->sort_order] = $clinic;
			} else {
				$list[$free_key] = $clinic;
				$free_key++;
			}
		}
		ksort($list);
		return $list;
	}

	/* 都道府県および市区町村ページURLチェック */
	private function validate_city_path()
	{
		$path_array = explode('/',$this->_request->getUri()->getPath());
		$params = [];
		$params += ['pref_pathname'=>$path_array[2]];
		$params += (!empty($path_array[3])) ? ['city_pathname'=>$path_array[3]] : ['city_pathname'=>'pref'];

		return \Services\Factory::get_instance('city')->get_by_pathname($params);
	}

	/* 駅ページURLチェック */
	private function validate_station_path()
	{
		$path_array = explode('/',$this->_request->getUri()->getPath());
		$params = [];
		$params += ['pref_pathname'=>$path_array[2]];
		$params += ['station_pathname'=>$path_array[3]];

		return \Services\Factory::get_instance('station')->get_by_pathname($params);
	}

	/* おすすめ記事ページURLチェック */
	private function validate_detail_path()
	{
		$path_array = explode('/',$this->_request->getUri()->getPath());
		$params = [
			'pref_pathname'=>$path_array[2],
			'attribute_pathname'=>$path_array[4]
		];

		if (strpos($path_array[3],'-area') !== false || strpos($path_array[3],'pref') !== false) {
			$params['city_pathname'] = $path_array[3];
			return \Services\Factory::get_instance('recommend')->get_recommend_id($params);
		} elseif (strpos($path_array[3],'-st') !== false) {
			$params['station_pathname'] = $path_array[3];
			return \Services\Factory::get_instance('recommend')->get_recommend_id_by_station($params);
		} else {
			return null;
		}
	}

	/* おすすめ記事ページパンくず配列取得 */
	private function get_breadcrumb($data = [])
	{
		$breadcrumb = [];
		$breadcrumb[0] = ['href' => '/' , 'text' => 'インプラントネット＋'];
		if (!empty($data->page) && $data->page === 1) {
			$breadcrumb[1] = ['href' => '' , 'text' => 'インプラント対応おすすめ歯医者'];
		} else {
			$path_array = explode('/',$this->_request->getUri()->getPath());
			$breadcrumb[1] = ['href' => DS.$path_array[1].DS , 'text' => 'インプラント対応おすすめ歯医者'];

			if (!empty($data->page) && $data->page > 1) {
				$breadcrumb[2] = ['href' => '' , 'text' => $data->page.'ページ目'];
			} else {
				$breadcrumb[2] = ['href' => DS.$path_array[1].DS.$path_array[2].DS , 'text' => $data->pref_name];

				if (!empty($data->city_name) && $data->city_pathname !== 'pref') {
					$breadcrumb[3] = ['href' => DS.$path_array[1].DS.$path_array[2].DS.$path_array[3].DS , 'text' => $data->city_name];
				} elseif (!empty($data->station_name)) {
					$breadcrumb[3] = ['href' => DS.$path_array[1].DS.$path_array[2].DS.$path_array[3].DS , 'text' => $data->station_name.'駅'];
				}

				$breadcrumb[4] = ['href' => '' , 'text' => $data->year_title];				
			}
		}
		return $breadcrumb;
	}

	/* 都道府県・市区町村ページパンくず配列取得 */
	private function get_city_breadcrumb($data)
	{
		$breadcrumb = [];
		$breadcrumb[0] = ['href' => '/' , 'text' => 'インプラントネット＋'];
		if ( empty($data) ) {
			$breadcrumb[1] = ['href' => '' , 'text' => 'インプラント対応おすすめ歯医者'];
		} else {
			$path_array = explode('/',$this->_request->getUri()->getPath());

			$breadcrumb[1] = ['href' => DS.$path_array[1].DS , 'text' => 'インプラント対応おすすめ歯医者'];

			if ($data->city_id < 48) {
				if ($data->page === 1) {
					$breadcrumb[2] = ['href' => '' , 'text' => $data->pref_name];
				} else {
					$breadcrumb[2] = ['href' => DS.$path_array[1].DS.$path_array[2].DS , 'text' => $data->pref_name];
					$breadcrumb[3] = ['href' => '' , 'text' => $data->page.'ページ目'];
				}
			} elseif ($data->city_id > 48) {
				$breadcrumb[2] = ['href' => DS.$path_array[1].DS.$path_array[2].DS , 'text' => $data->pref_name];
				$breadcrumb[3] = ['href' => '' , 'text' => $data->city_name];
			}
		}
		return $breadcrumb;
	}

	/* 駅ページパンくず配列取得 */
	private function get_station_breadcrumb($data)
	{
		$breadcrumb = [];
		$breadcrumb[0] = ['href' => '/' , 'text' => '矯正歯科ネット＋'];
		if ( empty($data) ) {
			$breadcrumb[1] = ['href' => '' , 'text' => 'おすすめ矯正歯科'];
		} else {
			$path_array = explode('/',$this->_request->getUri()->getPath());

			$breadcrumb[1] = ['href' => DS.$path_array[1].DS , 'text' => 'おすすめ矯正歯科'];

			$pref_href = DS.$path_array[1].DS.$path_array[2].DS;
			$breadcrumb[2] = ['href' => $pref_href , 'text' => $data->pref_name];
			$breadcrumb[3] = ['href' => '' , 'text' => $data->station_name.'駅'];
		}
		return $breadcrumb;
	}

	/* 記事ページjson-ld articleBody 取得 */
	private function get_article_body($clinic)
	{
		$str = '';

		$str.= $clinic->clinic_name."のご紹介\n";
		$str.= strip_tags($clinic->info_text)."\n\n";

		foreach ($clinic->features as $feature) {
			$str.= $feature->feature_title."\n";
			$str.= strip_tags($feature->feature_text)."\n\n";
		}

		return $str;
	}

	/* 記事ページjson-ld image 取得 */
	private function get_jsonld_image($clinic, $site_url)
	{
		$str = ",\n";

		$str.= '"'.$site_url.$clinic->mv_image_url.'"'.",\n";
		$str.= '"'.$site_url.$clinic->info_image_url.'"'.",\n";
		$str.= '"'.$site_url.$clinic->feature_image_url.'"'.",\n";
		$str.= '"'.$site_url.$clinic->pr_image_url.'"';

		return $str;
	}

	/* pagenation配列取得 */
	private function get_pages($page, $last_page, $type)
	{
		$pages = [];
		$type_param = (strpos($type, 'station') === FALSE) ? '' : 'station&';
		$max = (($last_page - $page) > 3) ? 3 : $last_page - $page;
		for ($i = 0; $i <= $max; $i++) {
			if ($i === 0) {
				$pages[$i] = ['anchor_class' => 'pagination__a--active', 'href' => '', 'text' => $page];
			} elseif ($i === 2 && ($last_page - $page) > 3) {
				$pages[$i] = ['anchor_class' => '', 'href' => '', 'text' => '<span class="pagination__text">...</span>'];
			} elseif($i === 3 && ($last_page - $page) > 3) {
				$pages[$i] = ['anchor_class' => 'pagination__a', 'href' => '?'.$type_param.'page='.($last_page), 'text' => $last_page];
			} else {
				$pages[$i] = ['anchor_class' => 'pagination__a', 'href' => '?'.$type_param.'page='.($page+$i), 'text' => $page+$i];
			}
		}
		return $pages;
	}
}
