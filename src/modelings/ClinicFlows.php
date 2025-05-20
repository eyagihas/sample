<?php

namespace Modelings;

trait ClinicFlows
{
	public function create_portallist_model(&$rows)
    {
        foreach($rows as $value){
        	$value->flow_text = nl2br($value->flow_text);
        }
    }

}