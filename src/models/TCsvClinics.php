<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TCsvClinics extends Base
{
    public function __construct($sitename = '')
    {
        parent::__construct();
        $this->_tableName = 't_csv_'.$sitename.'_recommend_clinics';
    }

    public function get_list()
    {
        try {
            return $this->_db->table($this->_tableName)
                ->select(
                    'recommend_id',
                    'clinic_id',
                    'clinic_name',
                    'filename1',
                    'price_plan',
                    'mv_image_note',
                    'gmap_src',
                    'access',
                    'address',
                    'start_at1',
                    'end_at1',
                    'is_mon_open1',
                    'is_tue_open1',
                    'is_wed_open1',
                    'is_thu_open1',
                    'is_fri_open1',
                    'is_sat_open1',
                    'is_sun_open1',
                    'start_at2',
                    'end_at2',
                    'is_mon_open2',
                    'is_tue_open2',
                    'is_wed_open2',
                    'is_thu_open2',
                    'is_fri_open2',
                    'is_sat_open2',
                    'is_sun_open2',
                    'holiday_note',
                    'url_plus',
                    'filename2',
                    'info_image_note',
                    'info_text',
                    'filename3',
                    'feature_image_note',
                    'feature_title1',
                    'feature_text1',
                    'feature_title2',
                    'feature_text2',
                    'feature_title3',
                    'feature_text3',
                    'reserve_tel',
                    'reserve_url_plus',
                    'filename4',
                    'pr_image_note',
                    'sort_order'
                    )
                ->orderBy('recommend_id','asc')->orderBy('clinic_id', 'asc')
                ->get();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

}
