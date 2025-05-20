<?php
namespace Controllers;

/* プラス 医師プロフィール情報 */
class Profiles extends Base
{
	use \Values\Meta;
	use \Values\Profiles;

    public function handler()
    {
    	if ($this->authorize() !== '') {
    		return \Services\Render::redirect($this->_response, $this->authorize());
    	}

    	try {
			if ( $this->_request->isGet() ) {
				$pathname = $this->_request->getUri()->getPath();
				$path_array = explode('/', $pathname);
				if ( strpos($pathname,'cms') !== false )  {
					if ( strpos($pathname,'list') !== false ) {
						$func_name = 'show_cms_list';
					} else {
						$func_name = ($path_array[3] === '0') ? 'show_add_form' : 'show_cms_detail';
					}
				} else {
					if ( count($path_array) === 3 ) {
						$func_name = 'show_list';
					} elseif ( count($path_array) === 4 ) {
						$func_name = 'show_detail';
					}
				}
				return $this->execute_method($func_name);
			} elseif ( $this->_request->isPost() ) {
				$mode = $this->_request->getParam('mode');
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
		$list->data = \Services\Factory::get_instance('profile')->get_cms_list($request,$page,$limit);
		$list->total = \Services\Factory::get_instance('profile')->get_total_count($request);

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

	protected function show_add_form()
	{
		$pathname = $this->_request->getUri()->getPath();
		$path_array = explode('/', $pathname);
		$site_pathname = str_replace('_cms', '', $path_array[1]);
		$site = \Services\Factory::get_instance('site')->get_by_pathname($site_pathname);

		$data = new \stdClass();
		$data->other_sites = \Services\Factory::get_instance('site')->get_others($site->site_id);

		$header = new \stdClass();
		$header->js_list = $this->get_js_list('cms');
		$header->css_list = $this->get_css_list('cms');

		$value = new \stdClass();
		$value = $this->get_values($site, ['profile_id' => 0]);

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'profile/edit_form.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value,
			'data' => $data
		]);

	}

	protected function show_cms_detail()
	{
		$pathname = $this->_request->getUri()->getPath();
		$path_array = explode('/', $pathname);
		$profile_id = (int)$path_array[3];
		$site_pathname = str_replace('_cms', '', $path_array[1]);
		$site = \Services\Factory::get_instance('site')->get_by_pathname($site_pathname);

		$data = new \stdClass();
		//$isEdited = \Services\Factory::get_instance('profile')->exists_preview($profile_id);
		$isEdited = true;
		$profile = \Services\Factory::get_instance('profile')->get_by_id($profile_id, $isEdited, $site_pathname);
		$profile->careers = \Services\Factory::get_instance('profile_career')->get_by_profile_id($profile_id, $isEdited);
		$profile->qualifications = \Services\Factory::get_instance('profile_qualification')->get_by_profile_id($profile_id, $isEdited);
		$data->profile = $profile;
		$data->profile_images = \Services\Factory::get_instance('profile_image')->get_by_profile_id($profile_id);
		$data->profile_banner = $this->get_image_url('/image/profile', 'bnr_'.$profile->doctor_en_name);
		$data->other_sites = \Services\Factory::get_instance('site')->get_others($site->site_id);

		$clinics = \Services\Factory::get_instance('profile_clinic')->get_by_profile_id($profile_id, $isEdited, $site_pathname);
		foreach ($clinics as $clinic) {
			$clinic->recommends = \Services\Factory::get_instance($site_pathname.'_recommend_clinic')->get_list_by_clinic($clinic->clinic_id, null, null, 0, true);
		}
		$data->clinics = $clinics;

		$list = new \stdClass();
		$list->explanations = \Services\Factory::get_instance($site_pathname.'_explanation')->get_cms_list(['profile_id' => $profile_id]);

		$header = new \stdClass();
		$header->js_list = $this->get_js_list('cms');
		$header->css_list = $this->get_css_list('cms');

		$value = new \stdClass();
		$value = $this->get_values($site, ['profile_id' => $profile_id]);

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'profile/edit_form.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value,
			'data' => $data,
			'list' => $list
		]);

	}

	/* プロフィール一覧ページ */
	protected function show_list ()
	{
		$base_url = $this->get_baseurl($this->_request->getUri()->getBaseUrl());
		$site = \Services\Factory::get_instance('site')->get_by_baseurl($base_url);

		$header = new \stdClass();
		$header->css_list = $this->get_css_list('portal_list');
		$header->js_list = $this->get_js_list('portal_list');

		$value = new \stdClass();

		$request = [ 'is_'.$site->site_pathname.'_published' => 1 ];
		$value->total = \Services\Factory::get_instance('profile')->get_total_count($request);

		$request = $this->_request->getQueryParams();
		/*
		$page = (isset($request['page'])) ? $request['page'] : 1;
		$limit = 12;
		*/
		$request['site_pathname'] = $site->site_pathname;
		$value->profiles = \Services\Factory::get_instance('profile')->get_portal_list($request);

		$headline = new \stdClass();
		$headline->path = $this->_request->getUri()->getPath();
		$headline->canonical = $this->get_canonical();
		//$headline->page = $page;
		//$headline->last_page = ceil($value->total/$limit);
		$value->headline = $headline;

		$value->includes = $this->get_includes($site->site_pathname);
		$value->breadcrumb = $this->get_breadcrumb($site);
		$value->site = $site;
		//$value->pages = $this->get_pages($page, $headline->last_page);
		$value->row_type = 'list';

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'profile/portal_list.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value
		]);
	}

	/* プロフィール詳細ページ */
	protected function show_detail ()
	{
		$data = $this->validate_detail_path();
		if ( empty($data) ) {
			return $this->redirect_404();
		}
		$data->base_url = $this->_request->getUri()->getBaseUrl();
		$data->canonical = $this->get_canonical();

		$base_url = $this->get_baseurl($this->_request->getUri()->getBaseUrl());
		$site = \Services\Factory::get_instance('site')->get_by_baseurl($base_url);

		$request = $this->_request->getQueryParams();
		$isPreview = (isset($request['preview'])) ? true : false;
		$data->ga_index = ($isPreview) ? 'noindex,nofollow' : 'index,follow';

		$profile = \Services\Factory::get_instance('profile')->get_by_id($data->profile_id, $isPreview, $site->site_pathname);
		if ( !isset($request['preview']) && $profile->is_published === 0 ) {
			return $this->redirect_404();
		}

		$profile->careers = \Services\Factory::get_instance('profile_career')->get_by_profile_id($data->profile_id, $isPreview);
		$profile->qualifications = \Services\Factory::get_instance('profile_qualification')->get_by_profile_id($data->profile_id, $isPreview);
		$profile->clinics = \Services\Factory::get_instance('profile_clinic')->get_by_profile_id($data->profile_id, $isPreview, $site->site_pathname);

		$recommends = new \stdClass();
		$id_tmp = [];
		if (!empty($profile->clinics)) {
			foreach ($profile->clinics as $clinic) {
				$id_tmp[] = $clinic->clinic_id;
			}
			$request['clinic_id'] = $id_tmp;
			$request['order'] = implode(',',$id_tmp);
			$recommends = \Services\Factory::get_instance('recommend')->get_portal_list_by_clinic($request);
		}

		$request = ['profile_id'=>$profile->profile_id, 'site_pathname'=>$site->site_pathname];
		$explanations = \Services\Factory::get_instance($site->site_pathname.'_explanation')->get_portal_list($request);

		$header = new \stdClass();
		$header->css_list = $this->get_css_list('portal_detail');
		$header->js_list = $this->get_js_list('portal_detail');

		$value = new \stdClass();
		$value->includes = $this->get_includes($site->site_pathname);
		$value->breadcrumb = $this->get_breadcrumb($site, $profile);
		$value->site = $site;
		$value->headline = $data;
		$value->profile = $profile;
		$value->recommends = $recommends;
		$value->explanations = $explanations;

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'profile/portal_detail.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value
		]);
	}

	protected function add()
	{
		$request = $this->_request->getParsedBody();

		$profile_id = \Services\Factory::get_instance('profile')->insert($request);
		$request['profile_id'] = $profile_id;
		$careers = $this->get_career_array($request);
		foreach ($careers as $career) {
			\Services\Factory::get_instance('profile_career')->insert($career);
			\Services\Factory::get_instance('profile_career')->insert($career, true);
		}
		$qualifications = $this->get_qualification_array($request);
		foreach ($qualifications as $qualification) {
			\Services\Factory::get_instance('profile_qualification')->insert($qualification);
			\Services\Factory::get_instance('profile_qualification')->insert($qualification, true);
		}
		$clinics = $this->get_clinic_array($request);
		foreach ($clinics as $clinic) {
			\Services\Factory::get_instance('profile_clinic')->insert($clinic);
			\Services\Factory::get_instance('profile_clinic')->insert($clinic, true);
		}

		$data = ['new_profile_id' => $profile_id];
		return \Services\Render::to_json($this->_response, $data);
	}


	protected function update()
	{
		$request = $this->_request->getParsedBody();

		\Services\Factory::get_instance('profile')->update($request);
		\Services\Factory::get_instance('profile')->update($request, true);

		\Services\Factory::get_instance('profile_career')->delete_by_profile_id($request['profile_id']);
		\Services\Factory::get_instance('profile_career')->delete_by_profile_id($request['profile_id'], true);
		$careers = $this->get_career_array($request);
		foreach ($careers as $career) {
			\Services\Factory::get_instance('profile_career')->insert($career);
			\Services\Factory::get_instance('profile_career')->insert($career, true);
		}

		\Services\Factory::get_instance('profile_qualification')->delete_by_profile_id($request['profile_id']);
		\Services\Factory::get_instance('profile_qualification')->delete_by_profile_id($request['profile_id'], true);
		$qualifications = $this->get_qualification_array($request);
		foreach ($qualifications as $qualification) {
			\Services\Factory::get_instance('profile_qualification')->insert($qualification);
			\Services\Factory::get_instance('profile_qualification')->insert($qualification, true);
		}

		\Services\Factory::get_instance('profile_clinic')->delete_by_profile_id($request['profile_id']);
		\Services\Factory::get_instance('profile_clinic')->delete_by_profile_id($request['profile_id'], true);
		$clinics = $this->get_clinic_array($request);
		foreach ($clinics as $clinic) {
			\Services\Factory::get_instance('profile_clinic')->insert($clinic);
			\Services\Factory::get_instance('profile_clinic')->insert($clinic, true);
		}

		$data = ['is_error'=>false];
		return \Services\Render::to_json($this->_response, $data);
	}

	protected function preview()
	{
		$request = $this->_request->getParsedBody();

		//プレビューデータの保存
		\Services\Factory::get_instance('profile')->update($request, true);
		
		\Services\Factory::get_instance('profile_career')->delete_by_profile_id($request['profile_id'], true);
		$careers = $this->get_career_array($request);
		foreach ($careers as $career) {
			\Services\Factory::get_instance('profile_career')->insert($career, true);
		}

		\Services\Factory::get_instance('profile_qualification')->delete_by_profile_id($request['profile_id'], true);
		$qualifications = $this->get_qualification_array($request);
		foreach ($qualifications as $qualification) {
			\Services\Factory::get_instance('profile_qualification')->insert($qualification, true);
		}
		\Services\Factory::get_instance('profile_clinic')->delete_by_profile_id($request['profile_id'], true);
		$clinics = $this->get_clinic_array($request);
		foreach ($clinics as $clinic) {
			\Services\Factory::get_instance('profile_clinic')->insert($clinic, true);
		}

		$site = \Services\Factory::get_instance('site')->get_by_pathname($request['site_pathname']);
		$data = ['preview_url' => $site->plus_url.'/profile/'.$request['doctor_en_name'].'/?preview'];
		return \Services\Render::to_json($this->_response, $data);
	}

	private function get_career_array($request)
	{
		$list = [];
		for ($i = 0; $i < count($request['date_info']); $i++) {
			if (!empty($request['date_info'][$i]) || !empty($request['career_text'][$i])) {
				$list[$i] = [
					'profile_id' => (int)$request['profile_id'],
					'career_id' => $i+1,
					'date_info' => $request['date_info'][$i],
					'career_text' => $request['career_text'][$i]
				];
			}
		}
		return $list;
	}

	private function get_qualification_array($request)
	{
		$list = [];
		for ($i = 0; $i < count($request['free_text']); $i++) {
			if (!empty($request['free_text'][$i]) || !empty($request['free_text'][$i])) {
				$list[$i] = [
					'profile_id' => (int)$request['profile_id'],
					'qualification_id' => $i+1,
					'free_text' => $request['free_text'][$i]
				];
			}
		}
		return $list;
	}

	private function get_clinic_array($request)
	{
		$list = [];
		$id = 1;
		foreach($request['clinic_id'] as $value) {
			if (!empty($value)) {
				$list[$id] = [
					'profile_id' => (int)$request['profile_id'],
					'clinic_id' => (int)$value,
					'sort_order' => $id
				];
				$id++;
			}
		}
		return $list;
	}

	private function get_image_url($directory, $name)
	{
		$filename = \Services\FileMove::exists_file('.'.$directory, $name);

		if (!empty($filename)) {
			$url = ltrim($filename, '.');
		} else {
			$url = '';
		}
		return $url;
	}

	/* おすすめ記事ページURLチェック */
	private function validate_detail_path()
	{
		$path_array = explode('/',$this->_request->getUri()->getPath());
		$doctor_en_name = $path_array[2];

		return \Services\Factory::get_instance('profile')->get_profile_id($doctor_en_name);
	}

	/* おすすめ記事ページパンくず配列取得 */
	private function get_breadcrumb($site, $data = [])
	{
		$breadcrumb = [];

		$top_text = ($site->site_id > 1) ? $site->site_name.'ネット＋' : 'TOP';
		$breadcrumb[0] = ['href' => '/' , 'text' => $top_text];
		if ( empty($data) ) {
			$breadcrumb[1] = ['href' => '' , 'text' => '歯科医師を探す'];
		} else {
			$path_array = explode('/',$this->_request->getUri()->getPath());
			$breadcrumb[1] = ['href' => DS.$path_array[1].DS , 'text' => '歯科医師を探す'];
			$breadcrumb[2] = ['href' => DS.$path_array[1].DS.$path_array[2].DS , 'text' => $data->doctor_name.' 歯科医師のプロフィール'];
		}
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
