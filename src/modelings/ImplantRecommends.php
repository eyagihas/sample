<?php

namespace Modelings;

use Carbon\Carbon as Carbon;

trait ImplantRecommends
{
	public function create_edit_model(&$row, $clinics = null)
	{
		if ( $row->publish_at !== null ) {
			$publish_at = new Carbon($row->publish_at);
			$row->publish_at = $publish_at->format('Y-m-d');
		}
		if ( $row->updated_at !== null ) {
			$updated_at = new Carbon($row->updated_at);
			$row->updated_at = $updated_at->format('Y-m-d');
		}

		$row->image_dir  = '/image/recommend/'.$row->pref_pathname.'/';
		$row->image_dir .= (!empty($row->station_name)) ? $row->station_pathname : $row->city_pathname;
		$row->image_dir .= '/'.$row->attribute_pathname;

		if (preg_match("/implant/",filter_input(INPUT_SERVER, 'SERVER_NAME', FILTER_SANITIZE_STRING))) {
			$filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main.webp');
		} else {
			$site = \Services\Factory::get_instance('site')->get_by_id(2);
			$filename = \Services\FileMove::exists_file_header($site->plus_url.$row->image_dir.'/main.webp');
		}

		if (!empty($filename)) {
			$row->image_url = ltrim($filename, '.');
		} else {
			$row->image_url = '';
		}

		//$row->mv_alt_default = $this->get_alt_string($row);
		$row->mv_alt_default = $row->title.'の画像';
		$row->description_default = $this->get_description_string($row, $clinics);
		$row->lead_default = $this->get_lead_string($row, $clinics);

		$row->tag_id_list = explode(',', $row->tag_id);
		$row->tag_name_list = explode(',', $row->tag_name);


	}

	public function create_cmslist_model(&$rows)
    {
        foreach($rows as $row){
        	if ( !empty($row->updated_at) ) {
        		$updated_at = new Carbon($row->updated_at);
        	} else {
        		$updated_at = new Carbon($row->publish_at);
        	}
			$publish_in = $updated_at->format('Y年');
			$row->publish_in = $publish_in;
        }
    }

    private function get_pref_city_string($row)
    {
    	$str = '';

    	$city_name = ($row->city_name !== 'すべて')? $row->city_name : '';
		$str = ($row->pref_name !== '東京都' && mb_substr($city_name, -1) === '区') ?
		$city_name  : $row->pref_name.$city_name ;

		return $str;

    }

    private function get_alt_string($row)
	{
		$str = '';

		$str.= $this->get_pref_city_string($row).'で';
		$str.= ($row->station_name !== '') ? $row->station_name.'駅周辺で' : $this->get_pref_city_string($row).'で';
		if ($row->attribute_pathname !== 'implant') {
			$str.= $row->attribute_name.'対応の';
		}
		$str.= 'インプラント治療ができるおすすめ歯医者○選';

		return $str;
	}

	private function get_description_string($row, $clinics = null)
	{
		$str = '';

		$str  = 'この記事では、';
		$str .= (!empty($row->station_name)) ? $row->station_name.'駅周辺で' : $this->get_pref_city_string($row).'で';
		$str .= ($row->attribute_pathname !== 'implant') ? $row->attribute_name.'対応の' : '';
		$str .= 'インプラント治療ができるおすすめの歯医者さんをご紹介します。';
		$str .= '歯科医院へのアクセスや診療時間などの基本情報や医院ごとの特長を掲載していますので、';
		$str .= (!empty($row->station_name)) ? $row->station_name.'駅お近くで' : $this->get_pref_city_string($row).'で';
		$str .= ($row->attribute_pathname !== 'implant') ? $row->attribute_name.'対応の' : '';
		$str .= 'インプラントをお考えの方は是非参考にしてください。';

		return $str;
	}

	private function get_lead_string($row, $clinics = null)
	{
		$str = '';

		$str .= (!empty($row->station_name)) ? $row->station_name.'駅周辺で' : $this->get_pref_city_string($row).'で';
		$str .= ($row->attribute_pathname !== 'implant') ? $row->attribute_name.'対応の' : '';
		$str .= 'インプラント治療ができるおすすめの歯医者さんをご紹介します。';

		$str .= (!empty($row->station_name)) ? $row->station_name.'駅周辺で' : $this->get_pref_city_string($row).'で';
		$str .= ($row->attribute_pathname !== 'implant') ? $row->attribute_name.'を受ける' : 'インプラントをする';
		$str .= 'ならどこのクリニック？何に注目して選ぶべき？そんなお悩みがある方に本記事はおすすめです。&#10;&#10;';

		$str .= 'この記事で紹介する';
		$str .= (!empty($row->station_name)) ? $row->station_name.'駅周辺で' : $this->get_pref_city_string($row).'で';
		$str .= ($row->attribute_pathname !== 'implant') ? $row->attribute_name.'対応の' : '';
		$str .= 'インプラント治療ができるおすすめの歯科医院は下記の通りです。&#10;&#10;';

		foreach ($clinics as $clinic) {
			$str .= '・'.$clinic->clinic_name.'&#10;';
		}

		$str .= '&#10;';

		$str .= 'インプラント治療対応の歯科医院選びの参考として、アクセスや診療時間などの基本情報や医院ごとの特長、院内写真などを掲載しています。&#10;';
		$str .= 'インプラント治療を検討中の方はぜひ読んでみてください。';

		return $str;
	}

	private function get_url($data)
	{
        $url = '';
        if ($data) {
        	$url = '/recommend/'.$data->pref_pathname.'/';
        	$url.= (!empty($data->station_pathname)) ? $data->station_pathname : $data->city_pathname;
        	$url.= '/'.$data->attribute_pathname.'/';
        }
        return $url;
	}

}
