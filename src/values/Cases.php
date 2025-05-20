<?php

namespace Values;

trait Cases
{
    private function get_values($site, $params = null)
    {
    	$h['site_id'] = $site->site_id;
    	$h['site_name'] = $site->site_name;
    	$h['site_pathname'] = $site->site_pathname;
    	$h['menu'] = 'case';

    	$h['title'] = '';
    	$h['sub_menu'] = 
    		[
        		['url'=>'case/0','menu_title'=>'症例追加']
        	];
      	if ( $params['id'] === 0 ) {
          $h['sub_menu_title'] = '症例追加';
          $h['mode'] = 'create';
        }
      	return $h;
    }

    private function get_list_values($site)
    {
    	$h = $this->get_values($site);
    	$h['search_title'] = '症例検索';
    	$h['list_title'] = '症例一覧';
    	$h['type'] = 'case';

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
				case $category === 'portal_detail':
					$list = [
							'recommend/recommend_article.css',
							'clinic/case_article.css'
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
							'cms/edit_case.js',
							'cms/jquery.validate.min.js'
						];
					break;
				case $category === 'portal':
					$list = [];
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
