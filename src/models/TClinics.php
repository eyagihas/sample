<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TClinics extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_clinics';
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

    public function get_by_id($id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->select(
                    'clinic_id',
                    'clinic_name',
                    'url',
                    'is_url_visible',
                    'url_sp',
                    'is_url_sp_visible',
                    'url_plus',
                    'is_url_plus_visible',
                    'teikei_page_url',
                    'is_url_teikei_visible',
                    'post_code',
                    'address',
                    'city_id',
                    'station_id_list',
                    'access',
                    'gmap_src',
                    'ope_time',
                    'holiday',
                    'holiday_token',
                    'holiday_note',
                    'reserve_url',
                    'is_reserve_url_visible',
                    'reserve_url_plus',
                    'is_reserve_url_plus_visible',
                    'tel',
                    'is_tel_visible',
                    'tel_sp',
                    'is_tel_sp_visible',
                    'reserve_tel',
                    'is_reserve_tel_visible',
                    'is_pr_reserve_tel_visible',
                    'is_pr_reserve_url_visible',
                    'attribute_num'
                )
                ->where('clinic_id', '=', $id)->first();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function insert($request)
    {
      $columns = [];
      $columns += ['clinic_id' => $request->clinic_id];
      $columns += ['clinic_name' => $request->clinic_name];
      $columns += ['url' => $request->url];
      $columns += ['is_url_visible' => $this->_db->raw(0)];
      $columns += ['url_sp' => $request->sf_url];
      $columns += ['is_url_sp_visible' => $this->_db->raw(0)];
      $columns += ['is_url_plus_visible' => $this->_db->raw(0)];
      $columns += ['teikei_page_url' => $request->teikei_page_url];
      $columns += ['is_url_teikei_visible' => 1];
      $columns += ['post_code' => $request->zip];
      $columns += ['address' => $request->address.' '.$request->address2];
      $columns += ['city_id' => $request->pref_id];
      $columns += ['access' => $request->rail_info];
      $columns += ['city_id' => $request->pref_id];
      $columns += ['station_id_list' => $request->station_id_list];
      //$columns += ['google_map_code' => $request->mapCodeByName];
      $columns += ['ope_time' => $request->ope_time];
      $columns += ['holiday' => $request->holiday];
      $columns += ['holiday_token' => $request->holiday_token];
      $columns += ['holiday_note' => $request->holiday_bikou];
      $columns += ['reserve_url' => $request->reserveUrl];
      $columns += ['is_reserve_url_visible' => 1];
      $columns += ['is_reserve_url_plus_visible' => $this->_db->raw(0)];
      $columns += ['tel' => $request->tel];
      $columns += ['is_tel_visible' => 1];
      $columns += ['tel_sp' => $request->freetel];
      $columns += ['is_tel_sp_visible' => $this->_db->raw(0)];
      $columns += ['is_reserve_tel_visible' => $this->_db->raw(0)];
      $columns += ['is_pr_reserve_tel_visible' => $this->_db->raw(0)];
      $columns += ['is_pr_reserve_url_visible' => $this->_db->raw(0)];
      $columns += ['attribute_num' => $request->specifiedFlgNums];
      $columns += ['sort_order' => $request->clinic_id];
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
        'clinic_name' => $request['clinic_name'],
        'url' => $request['url'],
        'is_url_visible' => $this->_db->raw((int)$request['is_url_visible']),
        'url_sp' => NULL,
        'is_url_sp_visible' => $this->_db->raw(0),
        'url_plus' => $request['url_plus'],
        'is_url_plus_visible' => $this->_db->raw((int)$request['is_url_plus_visible']),
        'teikei_page_url' => $request['teikei_page_url'],
        'is_url_teikei_visible' => $this->_db->raw((int)$request['is_url_teikei_visible']),
        'post_code' => $request['post_code'],
        'address' => $request['address'],
        'city_id' => $request['city_id'],
        'station_id_list' => $request['station_id_list'],
        'access' => $request['access'],
        'gmap_src' => $request['gmap_src'],
        'holiday_note' => $request['holiday_note'],
        'reserve_url' => $request['reserve_url'],
        'is_reserve_url_visible' => $this->_db->raw((int)$request['is_reserve_url_visible']),
        'reserve_url_plus' => $request['reserve_url_plus'],
        'is_reserve_url_plus_visible' => $this->_db->raw((int)$request['is_reserve_url_plus_visible']),
        'tel' => $request['tel'],
        'is_tel_visible' => $this->_db->raw((int)$request['is_tel_visible']),
        'tel_sp' => NULL,
        'is_tel_sp_visible' => $this->_db->raw(0),
        'reserve_tel' => $request['reserve_tel'],
        'is_reserve_tel_visible' => $this->_db->raw((int)$request['is_reserve_tel_visible']),
        'is_pr_reserve_tel_visible' => $this->_db->raw((int)$request['is_pr_reserve_tel_visible']),
        'is_pr_reserve_url_visible' => $this->_db->raw((int)$request['is_pr_reserve_url_visible']),
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

    public function update_specific($clinic_id, $request)
    {
      try {
          $this->_db->beginTransaction();
          $this->_db->table($this->_tableName)->where('clinic_id','=',$clinic_id)->update($request);
          $this->_db->commit();
          return true;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
    }

}
