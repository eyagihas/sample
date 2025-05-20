<?php

namespace Modelings;

use Carbon\Carbon as Carbon;

trait Recommends
{
	public function create_portallist_model(&$rows)
    {
        foreach($rows as $row){
        	$publish_at = new Carbon($row->publish_at);
        	$publish_in = $publish_at->format('Y年');
			$row->publish_in = $publish_in;

			$updated_at = new Carbon($row->updated_at);
        	$row->updated_at = ($updated_at == $publish_at) ? null: $updated_at;
			$updated_in = $updated_at->format('Y年');
			$row->updated_in = $updated_in;

			$row->url = '/recommend/'.$row->pref_pathname.'/';
			$row->url.= (!empty($row->station_pathname)) ? $row->station_pathname : $row->city_pathname;
			$row->url.= '/'.$row->attribute_pathname.'/';

			//$row->image_url = $_ENV['IMAGE_SERVER'].'/image/recommend/kyousei/'.$row->filename;
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

	public function create_detail_model(&$row) {

		if ( $row->publish_at !== null ) {
			$publish_at = new Carbon($row->publish_at);
			$row->publish_in = $publish_at->format('Y年');
			$row->publish_at_nj = $publish_at->format('Y年n月j日');
			$row->publish_at_md = $publish_at->format('Y-m-d');
			$row->publish_at_ym = $publish_at->format('Y年n月');
			$row->date_published = $publish_at->format('Y-m-d\TH:iP');
		}
		if ( $row->updated_at !== null ) {
			$updated_at = new Carbon($row->updated_at);
			$row->updated_in = $updated_at->format('Y年');
			$row->updated_at_nj = $updated_at->format('Y年n月j日');
			$row->updated_at_md = $updated_at->format('Y-m-d');
			$row->updated_at_ym = $updated_at->format('Y年n月');
			$row->date_modified = $updated_at->format('Y-m-d\TH:iP');
		} else {
			$row->updated_in = $publish_at->format('Y年');
			$row->date_modified = $publish_at->format('Y-m-d\TH:iP');
		}

		$row->year_title = '【'.$row->updated_in.'】'.$row->title;
		$row->lead_text = nl2br($row->lead_text);

		//$row->image_url = $_ENV['IMAGE_SERVER'].'/image/recommend/kyousei/'.$row->filename;
		$row->image_dir  = '/image/recommend/'.$row->pref_pathname.'/';
		$row->image_dir .= (!empty($row->station_name)) ? $row->station_pathname : $row->city_pathname;
		$row->image_dir .= '/'.$row->attribute_pathname;
		$row->thumbnail_url = $row->image_dir.'/thumbnail.jpg';
		$filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main.webp');
		if (empty($filename)) $filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main.jpg');

		if (!empty($filename)) {
			$row->image_url = ltrim($filename, '.');
		} else {
			$row->image_url = '/image/common/coming_soon.jpg';
		}

		$row->attribute_h2_str = ($row->attribute_name === 'インプラント') ? '' : $row->attribute_name.'対応の';

		$row->pref_city_str = $this->get_pref_city_string($row);

		$row->image_attr = (empty($row->image_attr)) ?
		//$row->pref_city_str.'で'.$row->attribute_h2_str.'インプラント治療ができるおすすめ歯医者' : $row->image_attr;
		$row->title.'の画像' : $row->image_attr;

		$keyword_str = 'インプラントネットプラス,歯医者さん,おすすめ,';
		if (empty($row->keyword)) {
			$keyword_str .= (!empty($row->station_name)) ? $row->station_name.',' : $row->pref_city_str.',';
			if ($row->attribute_name !== 'インプラント') $keyword_str .=  $row->attribute_name.',';
			$keyword_str .= 'インプラント,';
			
		} else {
			$keyword_str .= $row->keyword;
		}
		$row->keyword = $keyword_str;

		if (empty($row->description)) {
			$row->description  = 'この記事では、';
			$row->description .= (!empty($row->station_name)) ? $row->station_name.'駅周辺で' : $row->pref_city_str.'で';
			$row->description .= ($row->attribute_pathname !== 'implant') ? $row->attribute_name.'対応の' : '';
			$row->description .= 'インプラント治療ができるおすすめの歯医者さんをご紹介します。';
			$row->description .= '歯科医院へのアクセスや診療時間などの基本情報や医院ごとの特長を掲載していますので、';
			$row->description .= (!empty($row->station_name)) ? $row->station_name.'駅お近くで' : $row->pref_city_str.'で';
			$row->description .= ($row->attribute_pathname !== 'implant') ? $row->attribute_name.'対応の' : '';
			$row->description .= 'インプラント治療をお考えの方は是非参考にしてください。';
		}
	}

	public function create_portal_citylist_model(&$rows)
	{
		foreach ($rows as $row) {
			$child_city_id = explode(',', $row->city_id);
			$child_city_pathname = explode(',', $row->city_pathname);
			$child_city_name = explode(',', $row->city_name);

			$row->is_section = (count($child_city_id) > 1) ? true : false;

			$child_array = [];
			for ($i=0; $i<count($child_city_id); $i++) {
				$child = new \stdClass();
				$child->pref_name = $row->pref_name;
				$child->city_id = $child_city_id[$i];
				$child->city_pathname = $child_city_pathname[$i];
				$child->city_name = $child_city_name[$i];
				$child->pref_city = $this->get_pref_city_string($child);

				$child->city_name = ($row->parent_city_name !== $child_city_name[$i]) ?
				str_replace($row->parent_city_name, '', $child_city_name[$i]) : $child_city_name[$i];

				$child->url = '/recommend/'.$row->pref_pathname.'/'.$child->city_pathname.'/';
				$child->image_dir = '/image/recommend/'.$row->pref_pathname.'/'.$child->city_pathname;
				$filename = \Services\FileMove::exists_file('.'.$child->image_dir, 'main.webp');
				if (empty($filename)) $filename = \Services\FileMove::exists_file('.'.$child->image_dir, 'main.jpg');

				if (!empty($filename)) {
					$child->image_url = ltrim($filename, '.');
				} else {
					$child->image_url = '/image/common/coming_soon.jpg';
				}

				//$child->image_attr = (empty($child->image_attr)) ? $child->title.'の画像' : $child->image_attr;

				$child_array[] = $child;
			}
			$row->childs = $child_array;
		}
	}

	public function create_portal_preflist_model(&$rows)
	{
		foreach ($rows as $row) {
			$row->url = '/recommend/'.$row->pref_pathname.'/';
			$row->image_dir = '/image/recommend/'.$row->pref_pathname;
			$filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main');
			if (empty($filename)) $filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main.jpg');
			
			if (!empty($filename)) {
				$row->image_url = ltrim($filename, '.');
			} else {
				$row->image_url = '/image/common/coming_soon.jpg';
			}

			$year = new Carbon();
			$row->year = $year->format('Y年');
		}
	}

	public function create_portal_top_citylist_model(&$rows)
	{
		foreach ($rows as $row) {
			$row->url = '/recommend/'.$row->pref_pathname.'/';
			$row->url.= ($row->city_pathname !== 'pref') ? $row->city_pathname.'/' : '';

			$row->image_dir = '/image/recommend/'.$row->pref_pathname;
			$row->image_dir.= ($row->city_pathname !== 'pref') ? '/'.$row->city_pathname : '';
			$filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main');
			if (empty($filename)) $filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main.jpg');
			
			if (!empty($filename)) {
				$row->image_url = ltrim($filename, '.');
			} else {
				$row->image_url = '/image/common/coming_soon.jpg';
			}

			$year = new Carbon();
			$row->year = $year->format('Y年');
			
			$row->pref_city = $this->get_pref_city_string($row);
		}
	}

	public function create_portal_stationlist_model(&$rows)
	{
		foreach ($rows as $row) {
			$row->url = '/recommend/'.$row->pref_pathname.'/'.$row->station_pathname.'/';
			$row->image_dir = '/image/recommend/'.$row->pref_pathname.'/'.$row->station_pathname;
			$filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main.webp');
			if (empty($filename)) $filename = \Services\FileMove::exists_file('.'.$row->image_dir, 'main.jpg');

			if (!empty($filename)) {
				$row->image_url = ltrim($filename, '.');
			} else {
				$row->image_url = '/image/common/coming_soon.jpg';
			}
			
			$year = new Carbon();
			$row->year = $year->format('Y年');
		}
	}

	public function create_search_preflist_model(&$rows)
	{
		foreach ($rows as $row) {
			$row->pref_name_array = explode(',', $row->pref_name);
			$row->pref_pathname_array = explode(',', $row->pref_pathname);
		}	
	}

	public function create_portal_arealist_model(&$rows)
	{
		foreach ($rows as $row) {
			$row->city_name_array = explode(',', $row->city_name);
			$row->city_pathname_array = explode(',', $row->city_pathname);

			foreach ($row->city_name_array as &$city_name) {
				$city_name = ($row->pref_name !== '東京都' && mb_substr($city_name, -1) === '区') ?
				$city_name  : $row->pref_name.$city_name ;
			}
		}	
	}

	public function create_portal_sitemap_model(&$rows)
	{
		foreach ($rows as $row) {
			$row->url = '/recommend/'.$row->pref_pathname.'/';
			$row->url.= (!empty($row->station_pathname)) ? $row->station_pathname.'/' : $row->city_pathname.'/';
			$row->url.= $row->attribute_pathname.'/';
		}	
	}

	private function get_pref_city_string($row)
    {
    	$str = '';

    	$city_name = ($row->city_name !== 'すべて')? $row->city_name : '';
		$str = ($row->pref_name !== '東京都' && mb_substr($city_name, -1) === '区') ?
		$city_name  : $row->pref_name.$city_name ;

		return $str;

    }

}
