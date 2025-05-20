<?php

namespace Modelings;

trait ProfileImages
{
	public function create_cmslist_model(&$rows)
	{
		foreach($rows as $value) {
			$value->src = '/image/profile/'.$value->filename;
		}
	}

}
