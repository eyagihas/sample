<?php

namespace Values;

trait Tags
{
    private function get_values($site, $params = null)
    {
    	$h['site_id'] = $site->site_id;
    	$h['site_name'] = $site->site_name;
    	$h['site_pathname'] = $site->site_pathname;
    	$h['menu'] = 'tag';

    	$h['title'] = '';
    	$h['sub_menu'] = 
    		[
        		['url'=>'tag_list/','menu_title'=>'タグ一覧'],
        		['url'=>'tag/0','menu_title'=>'タグ追加']
        	];
      	if ( $params['tag_id'] === 0 ) {
          $h['sub_menu_title'] = 'タグ追加';
          $h['mode'] = 'insert';
        } else {
          $h['sub_menu_title'] = 'タグ一覧';
          $h['mode'] = 'update';
        }
      	return $h;
    }

    private function get_list_values($site)
    {
    	$h = $this->get_values($site);
    	$h['search_title'] = 'タグ検索';
    	$h['list_title'] = 'タグ一覧';
    	$h['type'] = 'tag';

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
							'cms/edit_tag.js',
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
