<?php

namespace Modelings;

use Carbon\Carbon as Carbon;

trait TopRecommends
{
	public function create_list_model(&$rows, $site_pathname)
	{
		foreach($rows as $row) {
			$updated_at = ($row->updated_at === null) ? $row->publish_at : $row->updated_at;
			$updated_year = new Carbon($updated_at);
			$updated_year = $updated_year->format('Y年');
			$row->title = '【'.$updated_year.'】'.$row->title;

			$row->id = $row->recommend_id;
			$row->type = 'recommend';
		}
	}

	public function create_portal_list_model(&$rows, $site_pathname)
	{
		foreach($rows as $row) {
			$updated_at = ($row->updated_at === null) ? $row->publish_at : $row->updated_at;
			$updated_year = new Carbon($updated_at);
			$row->updated_in = $updated_year->format('Y年');

			$row->url = '/recommend/'.$row->pref_pathname.'/';
			$row->url.= (!empty($row->station_pathname)) ? $row->station_pathname : $row->city_pathname;
			$row->url.= '/'.$row->attribute_pathname.'/';

			$row->image_dir  = '/image/recommend/'.$row->pref_pathname.'/';
			$row->image_dir .= (!empty($row->station_pathname)) ? $row->station_pathname : $row->city_pathname;
			$row->image_dir .= '/'.$row->attribute_pathname;
			$filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main.webp');
			if (empty($filename)) $filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main.jpg');

			if (!empty($filename)) {
				$row->image_url = ltrim($filename, '.');
			} else {
				$row->image_url = '/image/common/coming_soon.jpg';
			}

			$row->image_attr = (empty($row->image_attr)) ? $row->title.'の画像' : $row->image_attr;
		}
	}

	public function create_row_model(&$row, $site_pathname)
	{
		$updated_at = ($row->updated_at === null) ? $row->publish_at : $row->updated_at;
		$updated_year = new Carbon($updated_at);
		$updated_year = $updated_year->format('Y年');
		$row->title = '【'.$updated_year.'】'.$row->title;

		$row->id = $row->recommend_id;
		$row->type = 'recommend';
	}

}
