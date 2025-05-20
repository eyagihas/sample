<?php

namespace Modelings;

trait SelfClinicFeatures
{
	public function create_cmslist_model(&$rows)
    {
        foreach($rows as $value){
            $char_code = 9311 + $value->sort_order;
            $value->circle_order = '&#'.$char_code.';';
        }
    }
}