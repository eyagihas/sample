<?php

namespace Values;

trait Profiles
{
    private function get_values($site, $params = null)
    {
    	$h['site_id'] = $site->site_id;
    	$h['site_name'] = $site->site_name;
    	$h['site_pathname'] = $site->site_pathname;
    	$h['plus_url'] = $site->plus_url;
    	$h['menu'] = 'profile';
    	$h['profile_id'] = $params['profile_id'];

    	$h['title'] = '';
    	$h['sub_menu'] = 
    		[
        		['url'=>'profile_list/','menu_title'=>'プロフィール一覧'],
        		['url'=>'profile/0','menu_title'=>'プロフィール追加']
        	];
      	if ( $params['profile_id'] === 0 ) {
          $h['sub_menu_title'] = 'プロフィール追加';
          $h['mode'] = 'add';
        } else {
          $h['sub_menu_title'] = 'プロフィール一覧';
          $h['mode'] = 'update';
        }
      	return $h;
    }

    private function get_list_values($site)
    {
    	$h = $this->get_values($site);
    	$h['search_title'] = 'プロフィール検索';
    	$h['list_title'] = 'プロフィール一覧';
    	$h['type'] = 'profile';

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
				case $category === 'portal_list':
					$list = [
							'profile/profile_top.css'
					];
					break;
				case $category === 'portal_detail':
					$list = [
							'profile/profile.css'
					];
					break;
			}
			return $list;
    }

    private function get_js_list($category)
    {
			switch(true) {
				case $category === 'cms':
					$list = [
							'cms/edit_profile.js',
							'cms/jquery.validate.min.js',
							'cms/autosize.min.js'
						];
					break;
				case $category === 'portal_list':
					$list = [
							'common.js'
						];
					break;
				case $category === 'portal_detail':
					$list = [
							'common.js'
						];
					break;
			}
			return $list;
    }
}
