<?php

namespace Modelings;

trait Attributes
{
	public function create_cmslist_model($site_pathname, &$rows)
	{
		$is_check_set = false;
		foreach($rows as $value) {
			if (!$is_check_set) {
				if ($site_pathname === 'implant' && $value->attribute_flgname === 'implant_flg') {
					$value->checked = 'checked';
					$is_check_set = true;
				} elseif ($site_pathname === 'kyousei' && $value->attribute_flgname === 'ty_adult_orthodontic') {
					$value->checked = 'checked';
					$is_check_set = true;
				} elseif ($site_pathname === 'kyousei' && $value->attribute_flgname === 'invisalign') {
					$value->checked = 'checked';
					$is_check_set = true;
				} else {
					$value->checked = '';
				}
			}
		}
	}

}
