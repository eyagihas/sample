<?php

namespace Modelings;

use Carbon\Carbon as Carbon;

trait TopCityStation
{
	public function create_list_model(&$rows, $site_pathname)
	{
		$now = new Carbon();
		$year = $now->format('Y年');

		foreach($rows as $row) {
			$prefix = (!empty($row->city_id)) ? $this->get_pref_city_string($row) : $row->station_name.'駅';
			$postfix = $this->get_title_postfix($site_pathname);

			$row->type = (!empty($row->city_id)) ? 'city' : 'station';
			$row->id = (!empty($row->city_id)) ? $row->city_id : $row->station_group_id;
			$row->title = '【'.$year.'】'.$prefix.'で'.$postfix;
		}
	}

	public function create_city_list_model(&$rows, $site_pathname)
	{
		$now = new Carbon();
		$year = $now->format('Y年');

		foreach($rows as $row) {
			$row->url = '/recommend/'.$row->pref_pathname.'/'.$row->city_pathname.'/';

			$row->image_dir  = '/image/recommend/'.$row->pref_pathname.'/'.$row->city_pathname;
			$filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main.webp');
			if (empty($filename)) $filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main.jpg');

			if (!empty($filename)) {
				$row->image_url = ltrim($filename, '.');
			} else {
				$row->image_url = '/image/common/coming_soon.jpg';
			}

			$row->year = $year;
			$row->pref_city = $this->get_pref_city_string($row);
		}
	}

	public function create_station_list_model(&$rows, $site_pathname)
	{
		$now = new Carbon();
		$year = $now->format('Y年');

		foreach($rows as $row) {
			$row->url = '/recommend/'.$row->pref_pathname.'/'.$row->station_pathname.'/';

			$row->image_dir  = '/image/recommend/'.$row->pref_pathname.'/'.$row->station_pathname;
			$filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main.webp');
			if (empty($filename)) $filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main.jpg');

			if (!empty($filename)) {
				$row->image_url = ltrim($filename, '.');
			} else {
				$row->image_url = '/image/common/coming_soon.jpg';
			}

			$row->year = $year;
		}
	}


	public function create_city_row_model(&$row, $site_pathname)
	{
		$now = new Carbon();
		$year = $now->format('Y年');

		$prefix = $this->get_pref_city_string($row);
		$postfix = $this->get_title_postfix($site_pathname);

		$row->type = 'city';
		$row->id = $row->city_id;
		$row->title = '【'.$year.'】'.$prefix.'で'.$postfix;
	}

	public function create_station_row_model(&$row, $site_pathname)
	{
		$now = new Carbon();
		$year = $now->format('Y年');

		$prefix = $row->station_name.'駅';
		$postfix = $this->get_title_postfix($site_pathname);

		$row->type = 'station';
		$row->id = $row->station_group_id;
		$row->title = '【'.$year.'】'.$prefix.'で'.$postfix;
	}

	private function get_pref_city_string($row)
    {
    	$str = '';

    	$city_name = ($row->city_name !== 'すべて')? $row->city_name : '';
		$str = ($row->pref_name !== '東京都' && mb_substr($city_name, -1) === '区') ?
		$city_name  : $row->pref_name.$city_name ;

		return $str;

    }

	private function get_title_postfix($site_pathname)
    {
    	$str = '';

		if ($site_pathname === 'implant') {
			$str .= 'インプラント治療ができるおすすめ歯医者';
		} elseif ($site_pathname === 'kyousei') {
			$str .= 'おすすめの矯正歯科医院';
		} elseif ($site_pathname === 'shinbi') {
			$str .= '審美歯科治療ができるおすすめ歯医者';
		}

		return $str;

    }
}
