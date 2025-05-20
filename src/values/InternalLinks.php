<?php

namespace Values;

trait InternalLinks
{
    private function get_values($site, $params = null)
    {
    	$h['site_id'] = $site->site_id;
    	$h['site_name'] = $site->site_name;
    	$h['site_pathname'] = $site->site_pathname;
    	$h['plus_url'] = $site->plus_url;
    	$h['menu'] = 'internal_link';
    	$h['section'] = $params['section'];

    	$h['title'] = '【'.$site->site_name.'】';
    	$h['sub_menu'] =
    		[
        		['url'=>'internal_link/','menu_title'=>'市区町村・駅記事'],
        		['url'=>'internal_link/recommend','menu_title'=>'ピックアップ記事']
        	];
      if ( $params['section'] === 'city_station' ) {
        $h['sub_menu_title'] = '市区町村・駅記事';
    } elseif ( $params['section'] === 'recommend' ) {
        $h['sub_menu_title'] = 'ピックアップ記事';
      }
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
							'cms/edit_internal_link.js',
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
