<?php

namespace Modelings;

use Carbon\Carbon as Carbon;

trait Cases
{
    public function create_orderlist_model($list)
    {
        $orders = array();
        for ($i=1; $i<=5; $i++) {
            $data = new \stdClass();
            $data->sort_order = $i;
            $data->class = '';
            foreach ($list as $row) {
                if ($row->sort_order == $i) $data->class = 'reserved';
            }
            $orders[$i] = $data;
        }
        return $orders;
    }

    public function create_list_model(&$rows)
    {
        foreach($rows as $row){
            $row->before_image = $this->set_image_url('/image/'.$row->clinic_id, 'case_'.$row->case_id.'_'.$row->before_image_id.'.webp');
            $row->before_image_attr = (!empty($row->before_image_attr)) ? $row->before_image_attr : $row->attribute_name.'の症例 治療前の画像';
            $row->before_image_note = (!empty($row->before_image_note)) ? '※引用：'.$row->before_image_note : '';

            $row->after_image = $this->set_image_url('/image/'.$row->clinic_id, 'case_'.$row->case_id.'_'.$row->after_image_id.'.webp');
            $row->after_image_attr = (!empty($row->after_image_attr)) ? $row->after_image_attr : $row->attribute_name.'の症例 治療後の画像';
            $row->after_image_note = (!empty($row->after_image_note)) ? '※引用：'.$row->after_image_note : '';

            $row->case_chief_complaint = nl2br($row->case_chief_complaint);
            $row->case_duration = nl2br($row->case_duration);
            $row->case_treatment_times = nl2br($row->case_treatment_times);
            $row->case_consultation_fee = nl2br($row->case_consultation_fee);
            $row->case_diagnostic_fee = nl2br($row->case_diagnostic_fee);
            $row->case_treatment_fee = nl2br($row->case_treatment_fee);
            $row->case_monthly_fee = nl2br($row->case_monthly_fee);
            $row->case_retainer_fee = nl2br($row->case_retainer_fee);
            $row->case_total_fee = nl2br($row->case_total_fee);
            $row->case_description = nl2br($row->case_description);
            $row->risk_side_effects = nl2br($row->risk_side_effects);
            $row->case_comment = nl2br($row->case_comment);

            $row->case_doctor_name = ($row->doctor_id > 0) ? $row->doctor_name : $row->case_doctor_name;
            $row->profile_page_url = ($row->doctor_id > 0 && $row->profile_is_published) ? '/profile/'.$row->doctor_en_name.'/' : '';
            $row->profile_banner_image = ($row->doctor_id > 0) ? $this->set_image_url('/image/profile', 'bnr_'.$row->doctor_en_name.'.webp') : '';

            if ($row->publish_at !== null) {
                $publish_at = new Carbon($row->publish_at);
                $row->publish_at = $publish_at->format('Y年n月j日');
            }

        }
    }

    public function  create_pub_upd_at_model(&$detail, $row)
    {
        $detail->publish_at = $row->publish_at;
        $detail->updated_at = $row->updated_at;

        if ( $detail->publish_at !== null ) {
            $publish_at = new Carbon($detail->publish_at);
            $detail->publish_at_nj = $publish_at->format('Y年n月j日');
            $detail->publish_at_md = $publish_at->format('Y-m-d');
        }
        if ( $detail->updated_at !== null ) {
            $updated_at = new Carbon($detail->updated_at);

            if ($updated_at == $publish_at) {
                $detail->updated_at = null;
            } else {
                $detail->updated_at_nj = $updated_at->format('Y年n月j日');
                $detail->updated_at_md = $updated_at->format('Y-m-d');
            }
        }
    }

    public function create_detail_model(&$row)
    {
        if ( $row->publish_at !== null ) {
            $publish_at = new Carbon($row->publish_at);
            $row->publish_at = $publish_at->format('Y-m-d');
        }
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