<?php

namespace Values;

trait Invisalign
{
    private function get_css_list($category)
    {
		switch(true) {
			case $category === 'cms_form':
				$list = [
							'invisalign_form.css'
						];
                break;
			case $category === 'portal_form':
				$list = [
							'invisalign_form.css'
						];
				break;
		}
        return $list;
    }

    private function get_js_list($category)
    {
		switch(true) {
			case $category === 'cms_form':
				$list = [
							'invisalign.js',
							'languages/jquery.validationEngine-ja.js',
							'cms/jquery.validate.min.js',
							'cms/autosize.min.js'
						];
                break;
			case $category === 'portal_form':
				$list = [
							'ajax_common.js',
							'invisalign.js',
							'languages/jquery.validationEngine-ja.js',
							'cms/jquery.validate.min.js',
							'cms/autosize.min.js'
						];
				break;
		}
        return $list;
    }

    private function get_values($category, $clinic = null)
    {
    	switch(true) {
			case $category === 'cms_form':
				$h['title'] = 'インビザライン記事情報入力データ';
				$h['h1_title'] = '';
				if (!empty($clinic)) {
					$h['h1_title'] .= '（'.$clinic->clinic_id.'）'.$clinic->clinic_name.'<br>';
				}
				$h['h1_title'] .= 'インビザライン記事情報入力データ';
				$h['type'] = 'cms';
                break;
			case $category === 'portal_form':
				$h['title'] = 'インビザライン記事情報入力フォーム';
				$h['h1_title'] = 'インビザライン<br class="u-only_sp">記事情報入力フォーム';
				$h['type'] = 'portal';
				break;
		}
      	return $h;
    }

    private function set_site_values(&$value, $site = null)
    {
    	$value->site_id = $site->site_id;
    	$value->site_name = $site->site_name;
    	$value->site_pathname = $site->site_pathname;
    	$value->menu = 'clinic';
    	$value->sub_menu = [
    			['url'=>'clinic_list/','menu_title'=>'医院ページ一覧'],
        		['url'=>'clinic/0','menu_title'=>'医院追加']
    		];
    	$value->sub_menu_title = '医院ページ一覧';
    }
}
