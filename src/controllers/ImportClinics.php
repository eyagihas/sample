<?php
namespace Controllers;

use Carbon\Carbon as Carbon;

/* 医院情報インポート用クラス */
class ImportClinics extends Base
{

	public function handler()
	{
		try {
			$request = $this->_request->getQueryParams();
			$sitename = (isset($request['sitename'])) ? $request['sitename'] : '';
			$func_name = 'import_data';
			return $this->execute_method($func_name, $sitename);
		} catch (\Exception $e) {
			throw new \Exceptions\ErrorException($e);
		}
	}

	protected function import_data($sitename)
	{
		$csvClinics = \Services\Factory::get_instance('csv_clinic', $sitename)->get_list();
		$this->import_clinics($sitename, $csvClinics);
	}

	// t_csv_recommend_clinics から　t_<site>_clinicsへのインポート
	private function import_clinics($sitename, $list)
	{
		$now_recommend_id = 0;

		foreach ($list as $row) {
			$images = $this->get_image_array($row);
			$opeTimes = $this->get_operation_time_array($row);
			$exists = \Services\Factory::get_instance('site_clinic', $sitename)->exists($row->clinic_id);
			if (!$exists) {
				$clinic_id = \Services\Factory::get_instance('site_clinic', $sitename)->insert($row, $images, $opeTimes);
			} else {
				//$clinic_id = \Services\Factory::get_instance('site_clinic', $sitename)->update($row, $images, $opeTimes);
			}

			$recommendClinic = $this->get_recommend_clinic_array($row);
			\Services\Factory::get_instance($sitename.'_recommend_clinic')->insert_for_import($recommendClinic);
			\Services\Factory::get_instance($sitename.'_recommend_clinic')->insert_for_import($recommendClinic, true);


			$features = $this->get_feature_array($row);
			foreach ($features as $feature) {
				\Services\Factory::get_instance($sitename.'_recommend_clinic_feature')->insert_for_import($feature);
				\Services\Factory::get_instance($sitename.'_recommend_clinic_feature')->insert_for_import($feature, true);
			}

			$now_recommend_id = $row->recommend_id;
		}
	}

	private function get_image_array($row)
	{
		$list = array();
		for ($i = 1; $i <= 4; $i++) {
			if (!empty($row->{'filename'.$i})) {
				$image_id = (int)substr($row->{'filename'.$i}, 1,1);
				$list[] = ['clinic_id' => $row->clinic_id, 'image_id' => $image_id, 'filename' => $row->{'filename'.$i}];
			}
		}
		return $list;
	}

	private function get_operation_time_array($row)
	{
		$list = array();
		for ($i = 1; $i <= 2; $i++) {
			$list[] = [
				'clinic_id' => $row->clinic_id,
				'start_at' => $row->{'start_at'.$i},
				'end_at' => $row->{'end_at'.$i},
				'is_mon_open' => $row->{'is_mon_open'.$i},
				'is_tue_open' => $row->{'is_tue_open'.$i},
				'is_wed_open' => $row->{'is_wed_open'.$i},
				'is_thu_open' => $row->{'is_thu_open'.$i},
				'is_fri_open' => $row->{'is_fri_open'.$i},
				'is_sat_open' => $row->{'is_sat_open'.$i},
				'is_sun_open' => $row->{'is_sun_open'.$i}
			];
		}
		return $list;
	}

	private function get_feature_array($row)
	{
		$list = array();
		for ($i = 1; $i <= 3; $i++) {
			$list[] = [
				'recommend_id' => $row->recommend_id,
				'clinic_id' => $row->clinic_id,
				'feature_id' => $i,
				'feature_title' => $row->{'feature_title'.$i},
				'feature_text' => $row->{'feature_text'.$i}
			];
		}
		return $list;
	}

	private function get_recommend_clinic_array($row)
	{
		$pr_image_id = (empty($row->filename4)) ? NULL: (int)substr($row->filename4, 1,1);
		
		return [
			'recommend_id' => $row->recommend_id,
			'clinic_id' => $row->clinic_id,
			'price_plan' => $row->price_plan,
			'mv_image_id' => (int)substr($row->filename1, 1,1),
			'mv_image_note' => $row->mv_image_note,
			'info_image_id' => (int)substr($row->filename2, 1,1),
			'info_image_note' => $row->info_image_note,
			'info_text' => $row->info_text,
			'feature_image_id' => (int)substr($row->filename3, 1,1),
			'feature_image_note' => $row->feature_image_note,
			'pr_image_id' => $pr_image_id,
			'pr_image_note' => $row->pr_image_note,
			'sort_order' => $row->sort_order
		];
	}

}
