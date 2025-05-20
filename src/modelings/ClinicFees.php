<?php

namespace Modelings;

trait ClinicFees
{
	public function create_portallist_model(&$rows)
    {
        foreach($rows as $value){
        	$value->fee = nl2br($value->fee);
        }
    }

}