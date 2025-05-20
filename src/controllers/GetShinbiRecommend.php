<?php
namespace Controllers;

/* おすすめ歯科医院取得クラス */
class GetShinbiRecommend extends Base
{
	use \Values\Meta;
	use \Values\GetRecommend;


    public function handler()
    {
		if ( $this->_request->isGet() ) {
			if ( !empty($this->_request->getQueryParam('selected_flg')) ) {
				return $this->execute_method('show_list');
			} else {
				return $this->execute_method('show_form');
			}
			
		} elseif ( $this->_request->isPost() ) {

		}

	}

	/* 条件選択フォームページ */
	protected function show_form()
	{

		$value = new \stdClass();
		$value->prefectures = $this->get_prefectures();
		$value->obsessions = $this->get_obsessions();
		$value->site_pathname = 'shinbi';
		$value->site_name = '審美歯科';
		$value->menu = 'get_recommend';


		$header = new \stdClass();
		$header->css_list = $this->get_css_list('cms');
		$header->js_list = $this->get_js_list('cms');
		$header->value = $this->get_values();
		$header->value['title'] = '【審美歯科】'.$header->value['title'];

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'recommend/cms.twig', [
			'header' => $header,
			'value'  => $value
		]);

	}

	/* リストページ */
	protected function show_list()
	{
		$request = $this->_request->getQueryParams();
		$prefectures = $this->get_prefectures();
		$obsessions = $this->get_obsessions();

		$url = $_ENV['DENTAL_API_URL'].'get_city_id.php?api_key='.$_ENV['DENTAL_API_KEY'].'&pref_id='.$request['pref_id'];
		$cities = json_decode(\Services\Curl::curl_request($url, false, [], $errno, $errmsg));

		//指定された「こだわり」フラグが立つ歯科医院の取得
		$implantList = array();
		$kyouseiList = array();
		$shinbiList = array();

		$shinbiList = $this->get_clinic_list('4', $request);
		
		$allList = array_merge($shinbiList, $implantList, $kyouseiList);
		$uniqueList = array();
		if (count($allList) > 0) {
			$sortedList = $this->sort_by_key('specifiedFlgNums', SORT_DESC, $allList);
			$uniqueList = $this->unique_by_clinicid($sortedList);
		}
		

		$value = new \stdClass();
		$value->prefName = (!empty($request['pref_id'])) ? $prefectures[(int)$request['pref_id']] : '';
		$value->cityName = (!empty($request['city_id'])) ? (($request['city_id']!=='pref') ? $this->get_city_name($cities, $request['city_id']) : '') : $request['station_name'].'駅';
		$value->obsession = $this->get_obsession_text($obsessions, $request['selected_flg']);
		$value->data = array_slice($uniqueList, 0, $request['num']);
		$value->site_pathname = 'shinbi';
		$value->site_name = '審美歯科';
		$value->menu = 'get_recommend';
		
		$header = new \stdClass();
		$header->css_list = $this->get_css_list('cms');
		$header->js_list = $this->get_js_list('cms');
		$header->value = $this->get_list_values($value);
		$header->value['title'] = '【審美歯科】'.$header->value['title'];

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'recommend/list.twig', [
			'header' => $header,
			'value'  => $value
		]);

	}

	protected function get_clinic_list($siteId, $request)
	{
		$url = $_ENV['DENTAL_API_URL'].'get_clinic_info_v2.php?api_key='.$_ENV['DENTAL_API_KEY'];
		$url.= ($request['city_id']==='pref') ? '&pref_id='.$request['pref_id'] : 
		((!empty($request['city_id'])) ? '&city_id='.$request['city_id'] : '&station_group_id='.$request['station_group_id']);
		$url.= '&site_id='.$siteId;
		$list = json_decode(\Services\Curl::curl_request($url, false, [], $errno, $errmsg));
		$list = $this->get_hit_clinic($list, $request['selected_flg'], $siteId);
		$this->set_specified_flg_count($list);

		return $list;
	}

	protected function get_hit_clinic($list, $selectedFlg, $siteId) {
		$childFlgs = $this->get_child_obsessions($selectedFlg);

		return array_filter($list, function($element) use($selectedFlg, $siteId, $childFlgs) {
			$hitFlg = false;
			foreach ($childFlgs as $val) {
				$flgname = $val;
				if (isset($element->$flgname)) {
					$hitFlg = true;
					break;
				}
			}
			if ($hitFlg) {
				$element->urlEncodedName = urlencode($element->clinic_name);
				$element->siteName = $this->get_site_name($siteId);
				$element->siteUrl = $this->get_site_url($siteId);

				$planColName = $this->get_site_en($siteId).'_teikei';
				$element->plan_teikei = $this->get_pc_plan($element->$planColName);

				$sfPlanColName = 'sf_'.$this->get_site_en($siteId).'_teikei';
				$element->sf_plan_teikei = $this->get_sf_plan($element->$sfPlanColName);

				$element->mapCode = $this->get_map_code($element->address_lat, $element->address_lon);
				$element->mapCodeByName = $this->get_map_code_by_name($element->urlEncodedName);

				$element->reserveUrl = $this->get_reserve_url(
					$element->clinic_id,
					$element->teikei_yoyaku_jump_flg,
					$element->teikei_yoyaku_static_flg,
					$element->clinic_yoyaku_page_url,
					$siteId);

				$imageList = [];
				for ($i=1;$i<19;$i++) {
					$n = 'innai_image'.$i;
					if ($element->$n !== '') $imageList[] = $element->$n;
				}
				$element->imageList = $imageList;

				$sfImageList = [];
				for ($i=1;$i<11;$i++) {
					$n = 'sf_innai_image'.$i;
					if ($element->$n !== '') $sfImageList[] = $element->$n;
				}
				$element->sfImageList = $sfImageList;

				return (((int)$element->$planColName > 0 || (int)$element->$sfPlanColName > 0 ) && $element->$flgname === "1");
			}
		});
	}

	protected function set_specified_flg_count(&$list)
	{
		$specifiedObsessions = $this->get_specified_obsessions();

		foreach ($list as $item) {
			$nums = 0;
			foreach ($specifiedObsessions as $obsession) {
				if (isset($item->$obsession)) {
					if ($item->$obsession === "1") $nums++;
				}
			}
			$item->specifiedFlgNums = $nums;
		}
	}

	protected function sort_by_key($keyName, $sortOrder, $array)
	{
		foreach ($array as $key => $value) {
			$standardKeyArray[$key] = $value->$keyName;
		}
		array_multisort($standardKeyArray, $sortOrder, $array);

		return $array;
	}

	protected function unique_by_clinicid($orgArray)
	{
		$tmp = array();
		$newArray = array();
		foreach( $orgArray as $key => $value ) {
			if( !in_array( $value->clinic_id, $tmp ) ) {
				$tmp[] = $value->clinic_id;
				$newArray[] = $value;
			}
		}
		return $newArray;
	}


	protected function get_site_name($siteId)
	{
		$siteList = [
			'2' => 'インプラント', '3' => '矯正', '4' => '審美'
		];
		return $siteList[(int)$siteId];
	}

	protected function get_site_en($siteId)
	{
		$siteList = [
			'2' => 'implant', '3' => 'kyousei', '4'=> 'shinbi'
		];
		return $siteList[(int)$siteId];
	}

	protected function get_site_url($siteId)
	{
		$urlList = [
			'2' => 'https://www.implant.ac', 
			'3' => 'https://www.kyousei-shika.net',
			'4' => 'https://www.shinbi-shika.net'
		];
		return $urlList[(int)$siteId];
	}


	protected function get_prefectures()
	{
		return [
			1 => '北海道', 2 => '青森県', 3 => '岩手県', 4 => '宮城県', 5 => '秋田県',
			6 => '山形県', 7 => '福島県', 8 => '茨城県', 9 => '栃木県', 10 => '群馬県',
			11 => '埼玉県', 12 => '千葉県', 13 => '東京都', 14 => '神奈川県', 15 => '新潟県',
			16 => '富山県', 17 => '石川県', 18 => '福井県', 19 => '山梨県', 20 => '長野県',
			21 => '岐阜県', 22 => '静岡県', 23 => '愛知県', 24 => '三重県', 25 => '滋賀県',
			26 => '京都府', 27 => '大阪府', 28 => '兵庫県', 29 => '奈良県', 30 => '和歌山県',
			31 => '鳥取県', 32 => '島根県', 33 => '岡山県', 34 => '広島県', 35 => '山口県',
			36 => '徳島県', 37 => '香川県', 38 => '愛媛県', 39 => '高知県', 40 => '福岡県',
			41 => '佐賀県', 42 => '長崎県', 43 => '熊本県', 44 => '大分県', 45 => '宮崎県',
			46 => '鹿児島県', 47 => '沖縄県'
		];
	}

	protected function get_obsessions()
	{
		return [
            ['flg_name' => 'whitening', 'flg_text' => 'ホワイトニング', 'checked' => ''],
            ['flg_name' => 'ceramic', 'flg_text' => 'セラミック治療', 'checked' => '']
		];
	}

	protected function get_child_obsessions($parent_obsession)
	{
		if ($parent_obsession === 'whitening') {
			return [
            	'whitening_flg',
            	'wh_fap_whitening',
            	'wh_home_whitening',
            	'wh_office_whitening',
            	'wh_dual_whitening',
            	'wh_polyphosphoric'
			];
		} else {
			return [
            	'ct_ceramic_straightening',
            	'ct_ceramic_inlays',
            	'ct_ceramic',
            	'ct_hybrid_ceramic',
            	'ct_ceramic_crowns',
            	'oct_laminate'
			];
		}
	}

	protected function get_obsession_text($list, $flgName)
	{
		$result = array_filter($list, function($element) use($flgName) {
			return $element['flg_name'] === $flgName;
		});
		$result = array_values($result);

		return $result[0]['flg_text'];
	}

	protected function get_specified_obsessions()
	{
		return [
			'free_consult_flg',
			'second_opinion_flg',
			'walk_5min_flg',
			'card_flg',
			'assurance_flg',
			'total_fee_flg',
			'baria_flg',
			'kositu_flg',
			'medical_loan_flg',
			'pauda_flg',
			'gum_shaping_flg',
			'pmtc_flg',
			'woman_doctor_flg',
			'whitening_flg',
			'ct_ceramic',
			'oct_esthetic_treatment_same_day',
			'oct_laminate',
			'oct_metal_bond',
			'oed_gammy_smile',
			'oed_whitening_gum'
		];
	}

	protected function get_city_name($list, $cityId)
	{
		$result = array_filter($list, function($element) use($cityId) {
			return $element->city_id === $cityId;
		});
		$result = array_values($result);
		return $result[0]->city_name;
	}

	protected function get_map_code_by_name($urlEncodedName)
	{
		return '<iframe src="https://www.google.com/maps?q='.$urlEncodedName.'&output=embed&t=m&z=16&hl=ja" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
	}

	protected function get_map_code($lat, $lon)
	{
		return '<iframe src="https://www.google.com/maps?q='.$lat.','.$lon.'&output=embed&t=m&z=16&hl=ja" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
	}

	protected function get_reserve_url($clinicId, $jumpFlg, $staticFlg, $clinicUrl, $siteId)
	{
		if ($jumpFlg) {
			return $clinicUrl;
		} elseif (!$jumpFlg && $staticFlg) {
			return '予約ページ無し';
		} elseif (!$jumpFlg && !$staticFlg) {
			return $this->get_site_url($siteId).'/appoint/index.html?id='.$clinicId;
		}
	}

	protected function get_pc_plan($planId)
	{
		$array = [
			100 => '動画版',
			90 => 'フラッシュ版',
			80 => 'ピクチャー版',
			78 => 'ピクチャー版ディスカウント',
			75 => 'スタートアップ版（歯科技工）',
			70 => 'ベーシック版',
			68 => 'ベーシック版(無料)',
			65 => '廉価版',
			60 => 'リンク版',
			50 => '簡易版',
			45 => '成果報酬版',
			40 => '相互リンク版',
			20 => 'テスト',
			10 => '無料会員',
			0 => '非表示'
		];

		return $array[(int)$planId];
	}

	protected function get_sf_plan($planId)
	{
		$array = [
			100 => '動画版',
			85 => 'JS版',
			80 => 'ピクチャー版',
			65 => 'ライト版',
			60 => 'リンク版',
			50 => '簡易版（PC既存有料）',
			45 => '成果報酬版',
			40 => '簡易版（PC既存無料）',
			10 => '無料会員',
			0 => '非表示'
		];

		return $array[(int)$planId];
	}

}
