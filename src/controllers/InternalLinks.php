<?php
namespace Controllers;

/* 内部リンク管理クラス */
class InternalLinks extends Base
{
	use \Values\Meta;
	use \Values\InternalLinks;

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
					if ( empty($path_array[3]) ) {
						$func_name = 'show_city_station_form';
					} elseif ( !empty($path_array[3]) ) {
						$func_name = 'show_'.$path_array[3].'_form';
					}
				} else {

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

	/* 市区町村・駅リンク管理ページ */
	protected function show_city_station_form()
	{
		$pathname = $this->_request->getUri()->getPath();
		$path_array = explode('/', $pathname);
		$site_pathname = str_replace('_cms', '', $path_array[1]);
		$site = \Services\Factory::get_instance('site')->get_by_pathname($site_pathname);

		$data = new \stdClass();
		$data->prefectures = \Services\Factory::get_instance('prefecture')->get_all(['is_domestic'=>true]);

		$header = new \stdClass();
		$header->js_list = $this->get_js_list('cms');
		$header->css_list = $this->get_css_list('cms');

		$value = new \stdClass();
		$value = $this->get_values($site, ['section'=>'city_station']);

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'internal_link/cms_city_station.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value,
			'data' => $data
		]);

	}

	/* ピックアップ記事リンク管理ページ */
	protected function show_recommend_form()
	{
		$pathname = $this->_request->getUri()->getPath();
		$path_array = explode('/', $pathname);
		$site_pathname = str_replace('_cms', '', $path_array[1]);
		$site = \Services\Factory::get_instance('site')->get_by_pathname($site_pathname);

		$data = new \stdClass();
		$data->prefectures = \Services\Factory::get_instance('prefecture')->get_all(['is_domestic'=>true]);
		//$data->attributes = \Services\Factory::get_instance('attribute')->get_list(['type'=>$site->site_pathname, 'is_13'=>true, 'is_valid'=>true, 'attribute_type'=>1]);
		$data->attributes = \Services\Factory::get_instance('attribute')->get_list(['type'=>$site->site_pathname, 'is_13'=>true, 'is_valid'=>true]);

		$header = new \stdClass();
		$header->js_list = $this->get_js_list('cms');
		$header->css_list = $this->get_css_list('cms');

		$value = new \stdClass();
		$value = $this->get_values($site, ['section'=>'recommend']);

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'internal_link/cms_recommend.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value,
			'data' => $data
		]);

	}

}
