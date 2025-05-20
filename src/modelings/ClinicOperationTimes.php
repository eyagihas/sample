<?php

namespace Modelings;

use Carbon\Carbon as Carbon;

trait ClinicOperationTimes
{
	public function create_cmslist_model(&$rows)
    {
        foreach($rows as $value){
        	if (!empty($value->start_at)) {
        		$start_at = new Carbon($value->start_at);
        		$value->start_at = $start_at->format('H:i');
        	}
        	if (!empty($value->end_at)) {
        		$end_at = new Carbon($value->end_at);
        		$value->end_at = $end_at->format('H:i');
        	}
        }
    }

    public function create_portallist_model(&$rows)
    {
        foreach($rows as $value){
        	if (!empty($value->start_at)) {
        		$start_at = new Carbon($value->start_at);
        		$value->start_at = $start_at->format('H:i');
        	}
        	if (!empty($value->end_at)) {
        		$end_at = new Carbon($value->end_at);
        		$value->end_at = $end_at->format('H:i');
        	}

        	$value->is_mon_open = $this->get_mark($value->is_mon_open);
        	$value->is_tue_open = $this->get_mark($value->is_tue_open);
        	$value->is_wed_open = $this->get_mark($value->is_wed_open);
        	$value->is_thu_open = $this->get_mark($value->is_thu_open);
        	$value->is_fri_open = $this->get_mark($value->is_fri_open);
        	$value->is_sat_open = $this->get_mark($value->is_sat_open);
        	$value->is_sun_open = $this->get_mark($value->is_sun_open);
        }
    }

    private function get_mark($value)
    {
    	switch(true) {
		case $value === 0:
			return 'ー';
		case $value === 1:
			return '△';
		case $value === 2:
			return '●';
		default:
			return 'ー';
		}
    }

}
