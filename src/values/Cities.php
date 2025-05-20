<?php

namespace Values;

trait Cities
{
    private function get_values($site, $params = null)
    {
    	$h['site_id'] = $site->site_id;
    	$h['site_name'] = $site->site_name;
    	$h['site_pathname'] = $site->site_pathname;
    	$h['menu'] = 'city';

    	$h['title'] = '';
    	$h['sub_menu'] = 
    		[
        		['url'=>'city_list/','menu_title'=>'エリア一覧']
        	];
      	if ( $params['city_id'] === 0 ) {
          $h['sub_menu_title'] = 'エリア追加';
          $h['mode'] = 'insert';
        } else {
          $h['sub_menu_title'] = 'エリア一覧';
          $h['mode'] = 'update';
        }
      	return $h;
    }

    private function get_list_values($site)
    {
    	$h = $this->get_values($site);
    	$h['search_title'] = 'エリア検索';
    	$h['list_title'] = 'エリア一覧';
    	$h['type'] = 'city';

        return $h;
    }

    private function get_css_list($category)
    {
    	switch(true) {
    		case $category === 'cms':
    			$list = [
							'jquery-ui.theme.min.css'
					];
					break;
				case $category === 'portal':
					$list = [];
					break;
			}
			return $list;
    }

    private function get_js_list($category)
    {
			switch(true) {
				case $category === 'cms':
					$list = [
							'cms/edit_city.js',
							'cms/jquery.validate.min.js'
						];
					break;
				case $category === 'portal':
					$list = [];
					break;
			}
			return $list;
    }
}
