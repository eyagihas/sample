<?php

namespace Values;

trait KyouseiRecommends
{
    private function get_values($params)
    {
    	$h['site_id'] = 3;
			$h['site_name'] = '矯正歯科';
			$h['site_pathname'] = 'kyousei';
			$h['menu'] = 'recommend';
      $h['title'] = 'おすすめ矯正歯科ページ';
      $h['sub_menu'] = 
        	[
        		['url'=>'recommend/0','menu_title'=>'新規ページ作成'],
        		['url'=>'recommend_list/','menu_title'=>'既存ページ修正'],
        		['url'=>'recommend/0?type=2','menu_title'=>'インビザライン新規ページ作成'],
        		['url'=>'recommend_list/?type=2','menu_title'=>'インビザライン既存ページ修正']
        	];
      if ( $params['recommend_id'] === 0 ) {
      	$h['sub_menu_title'] = ($params['type']===1) ? '新規ページ作成' : 'インビザライン新規ページ作成';
        $h['mode'] = 'insert';
      } else {
        $h['sub_menu_title'] = ($params['type']===1) ? '既存ページ修正' : 'インビザライン既存ページ修正';
        $h['mode'] = 'update';
      }
      return $h;
    }

    private function get_list_values($params)
    {
    	$h = $this->get_values($params);
    	$h['search_title'] = '既存ページ検索';
    	$h['list_title'] = '既存ページ一覧';
    	$h['type'] = 'recommend';

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
							'cms/edit_recommend.js',
							'cms/jquery.datetimepicker.full.min.js',
							'cms/jquery.validate.min.js',
							'cms/autosize.min.js'
						];
				break;
			case $category === 'portal':
				$list = [
							'portal/scroll_top.js'
						];
				break;
		}
		return $list;
    }

}
