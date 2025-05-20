<?php

namespace Values;

trait PortalTop
{
    private function get_values($site, $params = null)
    {
    	$h['site_id'] = $site->site_id;
    	$h['site_name'] = $site->site_name;
    	$h['site_pathname'] = $site->site_pathname;
    	$h['plus_url'] = $site->plus_url;

      return $h;
    }


    private function get_css_list()
    {
    	$list = [
    		'slick/slick.css',
    		'slick/slick-theme.css',
    		'home/home.css'
    	];
			return $list;
    }

    private function get_js_list()
    {
    	$list = [
    		'slick.min.js',
    		'home.js',
    		'common.js'
    	];
			return $list;
    }
}
