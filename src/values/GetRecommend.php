<?php

namespace Values;

trait GetRecommend
{
    private function get_values()
    {
        $h['title'] = 'おすすめ歯科医院';
        return $h;
    }

    private function get_list_values($value)
    {
        $h['title'] = $value->cityName.' '.$value->obsession.'対応 おすすめ歯科'.count($value->data).'選';
        return $h;
    }

    private function get_css_list($category)
    {
		switch(true) {
			case $category === 'cms':
				$list = [
							//'cms_form.css'
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
							'cms/get_recommend.js',
						];
                break;
			case $category === 'portal':
				$list = [];
				break;
		}
        return $list;
    }

}
