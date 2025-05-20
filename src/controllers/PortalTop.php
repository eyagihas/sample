<?php
namespace Controllers;

/* プラス TOPページ */
class PortalTop extends Base
{
	use \Values\Meta;
	use \Values\PortalTop;

    public function handler()
    {
    	try {
			if ( $this->_request->isGet() ) {
				$func_name = 'show_top';
				return $this->execute_method($func_name);
			} elseif ( $this->_request->isPost() ) {
				$mode = $this->_request->getParam('mode');
				return $this->execute_method($mode);
			}
		} catch (\Exception $e) {
			throw new \Exceptions\ErrorException($e);
		}
	}

	/* TOPページ */
	protected function show_top ()
	{
		$base_url = $this->get_baseurl($this->_request->getUri()->getBaseUrl());
		$site = \Services\Factory::get_instance('site')->get_by_baseurl($base_url);

		$header = new \stdClass();
		$header->css_list = $this->get_css_list();
		$header->js_list = $this->get_js_list();

		$value = new \stdClass();
		$value->cities = \Services\Factory::get_instance('recommend')->get_active_pref_list();
		$value->top_cities = \Services\Factory::get_instance('top_city_station', $site->site_pathname)->get_top_city_list();
		$value->stations = \Services\Factory::get_instance('recommend')->get_active_station_list(0);
		$value->top_stations = \Services\Factory::get_instance('top_city_station', $site->site_pathname)->get_top_station_list();

		$value->recommends = \Services\Factory::get_instance('top_recommend', $site->site_pathname)->get_portal_top_list();
		$value->row_type = 'slide';

		$value->profiles = \Services\Factory::get_instance('profile')->get_portal_list(['site_pathname' => $site->site_pathname, 'desc' => true]);

		$value->search_prefs = \Services\Factory::get_instance('recommend')->get_pref_search_list();

		$value->search_areas = \Services\Factory::get_instance('recommend')->get_area_search_list();

		$active_attributes = \Services\Factory::get_instance('recommend')->get_active_attribute_list();
		foreach ($active_attributes as $attribute) {
			$attribute->recommends =  \Services\Factory::get_instance('recommend')->get_attribute_search_list($attribute->attribute_id);
		}
		$value->search_attributes = $active_attributes;

		$value->includes = $this->get_top_includes($site->site_pathname);
		$value->site = $site;

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'top/portaltop.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value
		]);
	}
}
