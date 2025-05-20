<?php

namespace Modelings;

use Carbon\Carbon as Carbon;

trait Clinics
{
	public function create_portallist_model(&$rows)
    {
        foreach($rows as $value){
        	$value->url_encoded_name = urlencode($value->master_clinic_name);
        	$value->access = nl2br($value->access);
        	$value->holiday_note = nl2br($value->holiday_note);
        	$value->info_text = nl2br($value->info_text);
        	$value->treatment_duration = nl2br($value->treatment_duration);
        	$value->treatment_times = nl2br($value->treatment_times);
        	/*
        	$value->mv_image_url = $_ENV['IMAGE_SERVER'].'/image/clinic/'.$value->clinic_id.'/'.$value->mv_image;
        	$value->info_image_url = $_ENV['IMAGE_SERVER'].'/image/clinic/'.$value->clinic_id.'/'.$value->info_image;
        	$value->feature_image_url = $_ENV['IMAGE_SERVER'].'/image/clinic/'.$value->clinic_id.'/'.$value->feature_image;
        	*/
        	$value->mv_image_url = $this->set_image_url('/image/'.$value->clinic_id, $value->mv_image);
        	$value->mv_image_attr = (!empty($value->mv_image_attr)) ? $value->mv_image_attr : $value->clinic_name.' '.$value->attribute_name.'の画像';
        	$value->mv_image_note = (!empty($value->mv_image_note)) ? '※引用：'.$value->mv_image_note : '';
        	$value->info_image_url = $this->set_image_url('/image/'.$value->clinic_id, $value->info_image);
        	$value->info_image_attr = (!empty($value->info_image_attr)) ? $value->info_image_attr : $value->clinic_name.' 紹介の画像';
        	$value->info_image_note = (!empty($value->info_image_note)) ? '※引用：'.$value->info_image_note : '';
        	$value->feature_image_url = $this->set_image_url('/image/'.$value->clinic_id, $value->feature_image);
        	$value->feature_image_attr = (!empty($value->feature_image_attr)) ? $value->feature_image_attr : $value->clinic_name.' 特長の画像';
        	$value->feature_image_note = (!empty($value->feature_image_note)) ? '※引用：'.$value->feature_image_note : '';
        	$value->pr_image_url = $this->set_image_url('/image/'.$value->clinic_id, $value->pr_image);
        	$value->pr_image_attr = (!empty($value->pr_image_attr)) ? $value->pr_image_attr : $value->clinic_name.'の画像';

        	$url = '';
        	switch(true) {
			case $value->is_url_visible === 1:
				$url = $value->url;
				break;
			case $value->is_url_sp_visible === 1:
				$url = $value->url_sp;
				break;
			case $value->is_url_plus_visible === 1:
				$url = $value->url_plus;
				break;
			case $value->is_url_teikei_visible === 1:
				$url = $value->teikei_page_url;
				break;
			}
			$value->url = $url;
			$value->url_note = (preg_match('/'.preg_quote($_ENV['SITE_HOST']).'/', $url)) ?
			 '※インプラントネットへ移動します。' : '※医院ホームページへ移動します。';

			$reserve_url = '';
        	switch(true) {
			case $value->is_reserve_url_visible === 1:
				$reserve_url = $value->reserve_url;
				break;
			case $value->is_reserve_url_plus_visible === 1:
				$reserve_url = $value->reserve_url_plus;
				break;
			}
			$value->reserve_url = $reserve_url;

			$value->is_single_btn = ($value->is_pr_reserve_tel_visible && $value->is_pr_reserve_url_visible) ? false : true;
			$value->reserve_url_btn_note = (preg_match('/'.preg_quote($_ENV['SITE_HOST']).'/', $reserve_url)) ?
			'※インプラントネット' : '※外部の予約ページ';
			$value->reserve_url_btn_note.= (!$value->is_single_btn) ? '<br class="u-only_sp">へ移動します。' : 'へ移動します。';
			$value->reserve_url_note = (preg_match('/'.preg_quote($_ENV['SITE_HOST']).'/', $reserve_url)) ?
			'※インプラントネットへ移動します。' : '※外部の予約ページへ移動します。';

			$tel = '';
        	switch(true) {
			case $value->is_tel_visible === 1:
				$tel = $value->tel;
				break;
			case $value->is_tel_sp_visible === 1:
				$tel = $value->tel_sp;
				break;
			case $value->is_reserve_tel_visible === 1:
				$tel = $value->reserve_tel;
				break;
			}
			//$value->tel = str_replace('-','',$tel);
			$value->tel = $tel;

			$value->ga_label = (preg_match('/'.preg_quote($_ENV['SITE_HOST']).'/', $url)) ? 'teikei' : 'HP';

        }
    }

    public function create_basic_info_model(&$value) {
        $value->url_encoded_name = urlencode($value->master_clinic_name);
        $value->access = nl2br($value->access);
        $value->holiday_note = nl2br($value->holiday_note);

        $value->mv_image_url = $this->set_image_url('/image/'.$value->clinic_id, $value->mv_image);
        $value->mv_image_attr = (!empty($value->mv_image_attr)) ? $value->mv_image_attr : $value->clinic_name.' '.$value->attribute_name.'の画像';
        $value->mv_image_note = (!empty($value->mv_image_note)) ? '※引用：'.$value->mv_image_note : '';

        $url = '';
        switch(true) {
		case $value->is_url_visible === 1:
			$url = $value->url;
			break;
		case $value->is_url_sp_visible === 1:
			$url = $value->url_sp;
			break;
		case $value->is_url_plus_visible === 1:
			$url = $value->url_plus;
			break;
		case $value->is_url_teikei_visible === 1:
			$url = $value->teikei_page_url;
			break;
		}
		$value->url = $url;
		$value->url_note = (preg_match('/'.preg_quote($_ENV['SITE_HOST']).'/', $url)) ? '※インプラントネットへ移動します。' : '※医院ホームページへ移動します。';

		$reserve_url = '';
        switch(true) {
		case $value->is_reserve_url_visible === 1:
			$reserve_url = $value->reserve_url;
			break;
		case $value->is_reserve_url_plus_visible === 1:
			$reserve_url = $value->reserve_url_plus;
			break;
		}
		$value->reserve_url = $reserve_url;

		$value->is_single_btn = ($value->is_pr_reserve_tel_visible && $value->is_pr_reserve_url_visible) ? false : true;
		$value->reserve_url_btn_note = (preg_match('/'.preg_quote($_ENV['SITE_HOST']).'/', $reserve_url)) ? '※インプラントネット' : '※外部の予約ページ';
		$value->reserve_url_btn_note.= (!$value->is_single_btn) ? '<br class="u-only_sp">へ移動します。' : 'へ移動します。';
		$value->reserve_url_note = (preg_match('/'.preg_quote($_ENV['SITE_HOST']).'/', $reserve_url)) ?  '※インプラントネットへ移動します。' : '※外部の予約ページへ移動します。';

		$tel = '';
        switch(true) {
		case $value->is_tel_visible === 1:
			$tel = $value->tel;
			break;
		case $value->is_tel_sp_visible === 1:
			$tel = $value->tel_sp;
			break;
		case $value->is_reserve_tel_visible === 1:
			$tel = $value->reserve_tel;
			break;
		}
		//$value->tel = str_replace('-','',$tel);
		$value->tel = $tel;

		$value->ga_label = (preg_match('/'.preg_quote($_ENV['SITE_HOST']).'/', $url)) ? 'teikei' : 'HP';
    }

    private function set_image_url($directory, $file)
    {
		$url = '';
		$filename = (!empty($file)) ? \Services\FileMove::exists_file('.'.$directory, $file) : '';
		if (!empty($filename)) {
			$url = ltrim($filename, '.');
		} else {
			$url = '/image/common/coming_soon.jpg';
		}

		return $url;
    }

}
