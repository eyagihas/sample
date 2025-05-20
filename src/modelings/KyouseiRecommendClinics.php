<?php

namespace Modelings;

use Carbon\Carbon as Carbon;

trait KyouseiRecommendClinics
{
	public function create_list_model($list)
	{
		$orders = array();
		for ($i=1; $i<=10; $i++) {
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

	public function create_detail_model(&$row)
	{
		$recommend_updated_at = new Carbon($row->recommend_updated_at);
		$updated_in = $recommend_updated_at->format('Y年');
		
		$row->title = '【'.$updated_in.'】'.$row->title;

		if (!empty($row->contract_start_on)) {
			$contract_start_on = new Carbon($row->contract_start_on);
			$row->contract_start_on = $contract_start_on->format('Y-m-d');
		}
		
		if (!empty($row->contract_end_on)) {
			$contract_end_on = new Carbon($row->contract_end_on);
			$row->contract_end_on = $contract_end_on->format('Y-m-d');
		}

		if (!empty($row->updated_at)) {
			$updated_at = new Carbon($row->updated_at);
			$row->updated_at = $updated_at->format('Y-m-d');
		}

		$row->mv_image_attr_default = $row->clinic_name.' '.$row->attribute_name.'の画像';
		$row->info_image_attr_default = $row->clinic_name.' 紹介の画像';
		$row->feature_image_attr_default = $row->clinic_name.' 特長の画像';
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

			$row->type_str = ($row->attribute_type !== 1) ? 'type'.$row->attribute_type : '';
        }
    }

}
