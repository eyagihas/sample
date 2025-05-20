<?php
namespace Controllers;

/* プラス 駅情報 */
class Stations extends Base
{
	use \Values\Meta;
	use \Values\Stations;

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
						$func_name = ($path_array[3] === '0') ? 'show_add_form' : 'show_cms_list';
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
		$list->data = \Services\Factory::get_instance('station')->get_cms_group_list($request,$page,$limit);
		$list->total = \Services\Factory::get_instance('station')->get_total_count($request);

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

		$header = new \stdClass();
		$header->js_list = $this->get_js_list('cms');
		$header->css_list = $this->get_css_list('cms');

		$value = new \stdClass();
		$value = $this->get_values($site, ['station_group_id' => 0]);

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'city/add_form.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value
		]);

	}

	protected function add()
	{
		$request = $this->_request->getParsedBody();

		\Services\Factory::get_instance('station')->insert($request);

		$data = ['is_error'=>false];
		return \Services\Render::to_json($this->_response, $data);
	}


	protected function update()
	{
		$request = $this->_request->getParsedBody();

		\Services\Factory::get_instance('station')->update($request);

		$data = ['is_error'=>false];
		return \Services\Render::to_json($this->_response, $data);
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
