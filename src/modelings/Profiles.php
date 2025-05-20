<?php

namespace Modelings;

use Carbon\Carbon as Carbon;

trait Profiles
{
	public function create_portallist_model(&$rows)
    {
        foreach($rows as $row){
			$row->image_dir = '/image/profile/';
			$filename = \Services\FileMove::exists_file('.'.$row->image_dir, $row->filename);
			if (!empty($filename)) {
				$row->image_url = ltrim($filename, '.');
			} else {
				$row->image_url = '/image/common/coming_soon.jpg';
			}

			$row->image_attr = $row->doctor_name.' 歯科医師の画像';
        }
    }

	public function create_detail_model(&$row)
    {
 		$row->image_dir = '/image/profile/';
		$filename = \Services\FileMove::exists_file('.'.$row->image_dir, $row->filename);
		if (!empty($filename)) {
			$row->image_url = ltrim($filename, '.');
		} else {
			$row->image_url = '/image/common/coming_soon.jpg';
		}

		$row->image_attr = $row->doctor_name.' 歯科医師の画像';

		$row->description = (!empty($row->clinic_name)) ? str_replace(',', '、', $row->clinic_name).'に在籍する' : '';
		$row->description.= $row->doctor_name.'歯科医師のプロフィールページです。';
		$row->description.= $row->doctor_name.'歯科医師の現在に至るまでの経歴と取得している資格について紹介しています。';
		if (!empty($row->clinic_name)) {
			$row->description.= 'また'.$row->doctor_name.'歯科医師が在籍する'.str_replace(',', '、', $row->clinic_name);
			$row->description.= 'を掲載した記事についても記載しています。';
		}
    }

    public function create_banner_model(&$row)
    {
		if (!empty($row->clinic_id)) {
			//$row->bnr_image_dir = '/image/'.$row->clinic_id.'/';
			$row->bnr_image_dir = '/image/profile/';
			$filename = \Services\FileMove::exists_file('.'.$row->bnr_image_dir, 'bnr_'.$row->doctor_en_name.'.webp');

			if (!empty($filename)) {
				$row->bnr_image_url = ltrim($filename, '.');
			} else {
				$row->bnr_image_url = '/image/common/coming_soon.jpg';
			}
		}
    }
}
