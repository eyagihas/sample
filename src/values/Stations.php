<?php

namespace Values;

trait Stations
{
    private function get_values($site, $params = null)
    {
    	$h['site_id'] = $site->site_id;
    	$h['site_name'] = $site->site_name;
    	$h['site_pathname'] = $site->site_pathname;
    	$h['menu'] = 'station';

    	$h['title'] = '';
    	$h['sub_menu'] = 
    		[
        		['url'=>'station_list/','menu_title'=>'駅一覧']
        	];
      	if ( $params['station_group_id'] === 0 ) {
          $h['sub_menu_title'] = '駅追加';
          $h['mode'] = 'insert';
        } else {
          $h['sub_menu_title'] = '駅一覧';
          $h['mode'] = 'update';
        }
      	return $h;
    }

    private function get_list_values($site)
    {
    	$h = $this->get_values($site);
    	$h['search_title'] = '駅検索';
    	$h['list_title'] = '駅一覧';
    	$h['type'] = 'station';

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
							'cms/edit_station.js',
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
