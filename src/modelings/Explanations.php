<?php

namespace Modelings;

use Carbon\Carbon as Carbon;

trait Explanations
{
	public function create_portallist_model(&$rows)
    {
        foreach($rows as $row){

			$row->url = '/explanation/'.$row->pathname.'/';

			$row->image_dir = '/image/explanation/'.$row->pathname;
			$filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main');
			if (!empty($filename)) {
				$row->image_url = ltrim($filename, '.');
			} else {
				$row->image_url = '/image/common/coming_soon.jpg';
			}

			$row->image_attr = (empty($row->image_attr)) ? $row->title.'の画像' : $row->image_attr;

			$row->profile_image_dir = '/image/profile';
			$filename = \Services\FileMove::exists_file('.'.$row->profile_image_dir, $row->filename);
			if (!empty($filename)) {
				$row->profile_image_url = ltrim($filename, '.');
			} else {
				$row->profile_image_url = '/image/common/coming_soon.jpg';
			}

			$row->profile_image_attr = $row->doctor_name.' 歯科医師の画像';
        }
    }

	public function create_detail_model(&$row, $clinicNum = 0) {

		if ( $row->publish_at !== null ) {
			$publish_at = new Carbon($row->publish_at);
			$row->publish_at_nj = $publish_at->format('Y年n月j日');
		}
		if ( $row->updated_at !== null ) {
			$updated_at = new Carbon($row->updated_at);

			if ($updated_at == $publish_at) {
				$row->updated_at = null;
			} else {
				$row->updated_at_nj = $updated_at->format('Y年n月j日');
			}
		}

		$row->lead_text = nl2br($row->lead_text);

		$row->image_dir = '/image/explanation/'.$row->pathname;
		$filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main');
		if (!empty($filename)) {
			$row->image_url = ltrim($filename, '.');
		} else {
			$row->image_url = '/image/common/coming_soon.jpg';
		}

		$row->image_attr = (empty($row->image_attr)) ? $row->title.'の画像' : $row->image_attr;

		$keyword_str = 'インプラントネットプラス,歯医者さん,おすすめ,';
		if (empty($row->keyword)) {
			$keyword_str .= $row->pref_city_str.',';
			if ($row->attribute_name !== 'インプラント') $keyword_str .= 'インプラント,';
			$keyword_str .=  $row->attribute_name;
		} else {
			$keyword_str .= $row->keyword;
		}
		$row->keyword = $keyword_str;

		if (empty($row->description)) {
			$row->description  = 'この記事では、';
			$row->description .= $row->pref_city_str.'で';
			$row->description .= ($row->attribute_name === 'インプラント') ? 'インプラント治療ができる' : $row->attribute_name.'に対応している';
			$row->description .= 'おすすめの歯医者さんをご紹介します。';
			$row->description .= 'アクセスや診療時間などの基本情報や医院ごとの特長を掲載していますので、';
			$row->description .= $row->pref_city_str.'でインプラント治療';
			$row->description .= ($row->attribute_name === 'インプラント') ? '' : '（'.$row->attribute_name.'）';
			$row->description .= 'をお考えの方は是非参考にしてください。';
		}
	}

}
