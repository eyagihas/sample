<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TKyouseiSelfClinics extends Base
{
    //use \Modelings\Clinics;
    
    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_kyousei_self_clinics';
    }

	public function exists($id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->where('clinic_id', '=', $id)->exists();

        } catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function is_draft($id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->select(
                    'clinic_id',
                    'is_draft'
                )
                ->where('clinic_id', '=', $id)->first();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_by_id($id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->select(
                    'clinic_id',
                    'tel1',
                    'tel2',
                    'tel3',
                    'clinic_name',
                    'pic_name',
                    'director_name',
                    'director_name_kana',
                    'post_code',
                    'address',
                    'nearest_station',
                    'access',
                    'tel_1',
                    'tel_2',
                    'tel_3',
                    'url',
                    'url_sp',
                    'holiday',
                    'holiday_note',
                    'reserve_tel',
                    'is_pr_reserve_tel_visible',
                    'reserve_url',
                    'is_pr_reserve_url_visible',
                    'invisalign_treatment_times',
                    'invisalign_duration',
                    'info_text'
                )
                ->where('clinic_id', '=', $id)->first();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_attributes_by_id($id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->select(
                    'attribute_id_list'
                )
                ->where('clinic_id', '=', $id)->first();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_guideline_by_id($id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->select(
                    'invisalign_treatment_times',
                    'invisalign_duration',
                    'info_text'
                )
                ->where('clinic_id', '=', $id)->first();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function insert($clinic_id)
    {
      $columns = [];
      $columns += ['clinic_id' => $clinic_id];
      $columns += ['created_at' => Carbon::now()];
      $columns += ['updated_at' => Carbon::now()];
      $columns += ['deleted_at' => null];

      try {
          $this->_db->beginTransaction();
          $this->_db->table($this->_tableName)->insert($columns);
          $this->_db->commit();
          return true;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
    }

    public function update($request)
    {
      $columns = [
        'tel1' => $this->h($request['tel1']),
        'tel2' => $this->h($request['tel2']),
        'tel3' => $this->h($request['tel3']),
        'pic_name' => $this->h($request['pic_name']),
        'clinic_name' => $this->h($request['clinic_name']),
        'director_name' => $this->h($request['director_name']),
        'director_name_kana' => $this->h($request['director_name_kana']),
        'post_code' => $this->h($request['post_code']),
        'address' => $this->h($request['address']),
        'nearest_station' => $this->h($request['nearest_station']),
        'access' => $this->h($request['access']),
        'tel_1' => $this->h($request['tel_1']),
        'tel_2' => $this->h($request['tel_2']),
        'tel_3' => $this->h($request['tel_3']),
        'url' => $this->h($request['url']),
        'url_sp' => $this->h($request['url_sp']),
        'holiday' => $this->h($request['holiday']),
        'holiday_note' => $this->h($request['holiday_note']),
        'holiday_note' => $this->h($request['holiday_note']),
        'holiday_note' => $this->h($request['holiday_note']),
        'is_pr_reserve_url_visible' => $this->_db->raw((int)$request['is_pr_reserve_url_visible']),
        'reserve_url' => $this->h($request['reserve_url']),
        'is_pr_reserve_tel_visible' => $this->_db->raw((int)$request['is_pr_reserve_tel_visible']),
        'reserve_tel' => $this->h($request['reserve_tel']),
        'updated_at' => Carbon::now()
        ];

      try {
          $this->_db->beginTransaction();
          $this->_db->table($this->_tableName)->where('clinic_id','=',$request['clinic_id'])->update($columns);
          $this->_db->commit();
          return true;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
    }

    public function update_attribute_id_list($list, $clinic_id)
    {
      $columns = [
        'attribute_id_list' => $list,
        'updated_at' => Carbon::now()
        ];

      try {
          $this->_db->beginTransaction();
          $this->_db->table($this->_tableName)->where('clinic_id','=',$clinic_id)->update($columns);
          $this->_db->commit();
          return true;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
    }

    public function update_guideline_column($request)
    {
      $columns = [
        'invisalign_treatment_times' => $this->h($request['invisalign_treatment_times']),
        'invisalign_duration' => $this->h($request['invisalign_duration']),
        'info_text' => $this->h($request['info_text']),
        'updated_at' => Carbon::now()
        ];

      try {
          $this->_db->beginTransaction();
          $this->_db->table($this->_tableName)->where('clinic_id','=',$request['clinic_id'])->update($columns);
          $this->_db->commit();
          return true;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
    }

    public function fix_data($clinic_id)
    {
      $columns = [
        'is_draft' => $this->_db->raw(0),
        'updated_at' => Carbon::now()
        ];

      try {
          $this->_db->beginTransaction();
          $this->_db->table($this->_tableName)->where('clinic_id','=',$clinic_id)->update($columns);
          $this->_db->commit();
          return true;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
    }

}
