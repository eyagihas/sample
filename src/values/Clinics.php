<?php

namespace Values;

trait Clinics
{
    private function get_values($site, $clinic = null)
    {
    	$h['site_id'] = $site->site_id;
    	$h['site_name'] = $site->site_name;
    	$h['site_pathname'] = $site->site_pathname;
    	$h['menu'] = 'clinic';

    	if (!empty($clinic)) {
    		$h['title'] = '('.$clinic->clinic_id.')'.$clinic->clinic_name.'【フラグ数：'.$clinic->attribute_num.'】';
    		$h['sub_menu_title'] = '医院ページ一覧';
    	} elseif ($clinic === 0) {
    		$h['title'] = '';
    		$h['sub_menu_title'] = '医院追加';
    	} else {
    		$h['title'] = '';
    		$h['sub_menu_title'] = '医院ページ一覧';
    	}
    	
    	$h['sub_menu'] = 
    		[
        		['url'=>'clinic_list/','menu_title'=>'医院ページ一覧'],
        		['url'=>'clinic/0','menu_title'=>'医院追加']
        	];

      $h['mode'] = '';
      return $h;
    }

    private function get_list_values($site)
    {
    	$h = $this->get_values($site);
    	$h['search_title'] = '医院ページ検索';
    	$h['list_title'] = '医院ページ一覧';
    	$h['type'] = 'clinic';

        return $h;
    }

    private function get_css_list($category)
    {
    	switch(true) {
    		case $category === 'cms':
    			$list = [
							'jquery.datetimepicker.css',
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
							'cms/edit_clinic.js',
							'cms/jquery.datetimepicker.full.min.js',
							'cms/jquery.validate.min.js',
							'cms/autosize.min.js'
						];
					break;
				case $category === 'portal':
					$list = [];
					break;
			}
			return $list;
    }
}
