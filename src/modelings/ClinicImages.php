<?php

namespace Modelings;

trait ClinicImages
{
	public function create_cmslist_model(&$rows, $site_pathname)
	{
		foreach($rows as $value) {
			if (preg_match("/".$site_pathname."/",filter_input(INPUT_SERVER, 'SERVER_NAME', FILTER_SANITIZE_STRING))) {
				$filename = \Services\FileMove::exists_file('./image/'.$value->clinic_id, $value->filename);
			} else {
				$site = \Services\Factory::get_instance('site')->get_by_pathname($site_pathname);
				$filename = \Services\FileMove::exists_file_header($site->plus_url.'/image/'.$value->clinic_id.'/'.$value->filename);
			}

			if (!empty($filename)) {
				$value->src = ltrim($filename, '.');
			} else {
				$value->src = '';
			}
		}
	}

}
