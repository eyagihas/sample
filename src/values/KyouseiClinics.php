<?php

namespace Values;

trait KyouseiClinics
{
    private function get_values()
    {
    	$h['site_id'] = 3;
		$h['site_name'] = '矯正歯科';
		$h['site_pathname'] = 'kyousei';
		$h['menu'] = 'clinic';
        $h['title'] = '医院ページ';
        $h['sub_menu'] = 
        	[
        		['url'=>'clinic_list/','menu_title'=>'医院ページ一覧']
        	];
        $h['sub_menu_title'] = '医院ページ一覧';
        $h['mode'] = '';
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
