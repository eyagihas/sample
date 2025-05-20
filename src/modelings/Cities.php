<?php

namespace Modelings;

use Carbon\Carbon as Carbon;

trait Cities
{
	public function create_edit_model(&$row, $site_pathname)
	{
		$row->image_dir = ($row->city_pathname === 'pref') ? 
		'/image/recommend/'.$row->pref_pathname : 
		'/image/recommend/'.$row->pref_pathname.'/'.$row->city_pathname;

		if (preg_match("/".$site_pathname."/",filter_input(INPUT_SERVER, 'SERVER_NAME', FILTER_SANITIZE_STRING))) {
			$filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main');
		} else {
			$site = \Services\Factory::get_instance('site')->get_by_pathname($site_pathname);
			$filename = \Services\FileMove::exists_file_header($site->plus_url.$row->image_dir.'/main.webp');
		}

		if (!empty($filename)) {
			$row->image_url = ltrim($filename, '.');
		} else {
			$row->image_url = '';
		}

		$pref_city = $this->get_pref_city_string($row);

		$row->kyousei_lead_default = $pref_city.$this->get_default_kyousei_lead();
			

		$row->implant_lead_default = $pref_city.$this->get_default_implant_lead();
			

		$row->shinbi_lead_default = $pref_city.$this->get_default_shinbi_lead();
			
	}

	private function create_portal_city_model(&$row)
	{
		$row->pref_city = $this->get_pref_city_string($row);
		$row->keyword_str = ($row->city_id < 48) ? $row->pref_name : $row->city_name;
		$row->city_sub = ($row->city_id < 48) ? ucfirst($row->pref_pathname) : ucfirst(str_replace('-area', '', $row->city_pathname));

		$row->image_dir = '/image/recommend/'.$row->pref_pathname;
		$row->image_dir.= ($row->city_id > 47) ? '/'.$row->city_pathname : '';
		$filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main');
		if (!empty($filename)) {
			$row->image_url = ltrim($filename, '.');
		} else {
			$row->image_url = '/image/common/coming_soon.jpg';
		}

		if (empty($row->kyousei_lead)) {
			$row->kyousei_lead = $row->pref_city.$this->get_default_kyousei_lead();
		}
		if (empty($row->implant_lead)) {
			$row->implant_lead = $row->pref_city.$this->get_default_implant_lead();
		}
		if (empty($row->shinbi_lead)) {
			$row->shinbi_lead = $row->pref_city.$this->get_default_shinbi_lead();
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

    private function get_default_kyousei_lead()
    {
    	return 'であなたに合った歯医者さん（矯正歯科）が見つかる！矯正歯科ネットプラスがおすすめの矯正歯科医院を紹介します。ワイヤー矯正（表側矯正、裏側矯正）、マウスピース矯正、アンカースクリュー、外科矯正、審美ブラケット、部分矯正、小児矯正など歯列矯正を検討中の方はぜひご覧ください。';
    }

    private function get_default_implant_lead()
    {
    	return 'でインプラント、ワンデイインプラント、オールオン4、オーバーデンチャー、フラップレスインプラント、骨造成、歯周病治療などの治療方法から、インプラント保証、無料相談、セカンドオピニオン、メンテナンス、再治療、インプラント除去などのお悩み相談まで対応している歯科医院をご紹介します！最寄り駅や診療時間のご紹介、ご予約までお任せください。';
    }

    private function get_default_shinbi_lead()
    {
    	return 'でホワイトニング、セラミック治療などの審美歯科治療に対応している歯科医院をご紹介します！<br>最寄り駅や診療時間などの基本情報に加えて、歯科医院毎に対応している審美歯科治療内容のご紹介からご予約までお任せください。';
    }

}
