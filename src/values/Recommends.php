<?php

namespace Values;

trait Recommends
{
    private function get_css_list($category)
    {
		switch(true) {
			case $category === 'cms':
				$list = [];
                break;
			case $category === 'portal_detail':
				$list = [
							'recommend/recommend_article_2.css'
						];
				break;
			case $category === 'portal_list':
				$list = [
							'recommend/recommend_top.css'
						];
				break;
			case $category === 'portal_pref':
				$list = [
							'recommend/recommend_area_top.css'
						];
				break;
			case $category === 'portal_city':
				$list = [
							'recommend/recommend_area_top.css'
						];
				break;
		}
        return $list;
    }

    private function get_js_list($category)
    {
		switch(true) {
			case $category === 'cms':
				$list = [];
                break;
			case $category === 'portal_detail':
				$list = [
							'common.js'
						];
				break;
			case $category === 'portal_list':
				$list = [
							'common.js',
							'recommend_top.js'
						];
				break;
			case $category === 'portal_pref':
				$list = [
							'common.js'
						];
				break;
			case $category === 'portal_city':
				$list = [
							'common.js',
							'recommend_area_top.js'
						];
				break;
		}
        return $list;
    }

}
