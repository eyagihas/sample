<?php

namespace Modelings;

use Carbon\Carbon as Carbon;

trait ClinicFeatures
{
	public function create_portallist_model(&$rows)
    {
        $case_order = 1;
        foreach($rows as $value){
        	$value->feature_text = nl2br($value->feature_text);

            if ($value->case_id > 0) {
                //$char_code = 9311 + $value->case_sort_order;
                $char_code = 9311 + $case_order;
                $value->case_order = $case_order;
                $case_order++;
            }
            $value->case_circle_order = ($value->case_id > 0) ? '&#'.$char_code.';' : '';

            $value->case_before_image = $this->set_image_url('/image/'.$value->clinic_id, 'case_'.$value->case_id.'_'.$value->before_image_id.'.webp');
            $value->case_after_image = $this->set_image_url('/image/'.$value->clinic_id, 'case_'.$value->case_id.'_'.$value->after_image_id.'.webp');
        }
    }

    public function create_case_detail_model(&$row, $case, $site)
    {
        $row->description = '【'.$case->clinic_name.'】'.$case->attribute_name.'の症例を紹介します。';

        $row->pref_city_str = $this->get_pref_city_string($row);
        $keyword_str = $site->site_name.'ネットプラス,';
        $keyword_str .= (!empty($row->station_name)) ? $row->station_name.',' : $row->pref_city_str.',';
        $keyword_str .= $site->site_name.',';
        $keyword_str .= $case->attribute_name;
        $row->keyword = $keyword_str;

        $row->title = '【'.$case->clinic_name.'】'.$case->attribute_name.'の症例';
        $row->thumbnail_url = '/image/clinic/thumbnail_'.$case->attribute_pathname.'.jpg';
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

    private function get_pref_city_string($row)
    {
        $str = '';

        $city_name = ($row->city_name !== 'すべて')? $row->city_name : '';
        $str = ($row->pref_name !== '東京都' && mb_substr($city_name, -1) === '区') ?
        $city_name  : $row->pref_name.$city_name ;

        return $str;

    }
}