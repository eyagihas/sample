<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TTopCityStation extends Base
{

  use \Modelings\TopCityStation;

  public function __construct($sitename = '')
  {
      parent::__construct();
      $this->_tableName = 't_'.$sitename.'_top_city_station';
      $this->_siteName = $sitename;
  }

  public function exists($request)
  {
      try {
          return $this->_db->table($this->_tableName)
          ->where(function ($query) use ($request) {
                if (isset($request['city_id'])) {
                    $query->where('city_id','=',$request['city_id']);
                } elseif (isset($request['station_group_id'])) {
                    $query->where('station_group_id','=',$request['station_group_id']);
                }
            })
            ->exists();
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function get_list($request = null,$page = null,$limit = null)
  {
      try {
          $list = $this->_db->table($this->_tableName.' as t1')->select(
              't1.city_id as city_id',
              'c.pref_name as pref_name',
              'c.pref_pathname as pref_pathname',
              'c.city_name as city_name',
              'c.city_pathname as city_pathname',
              't1.station_group_id as station_group_id',
              's.station_name as station_name',
              'sg.station_pathname as station_pathname'
              )
              ->leftJoin('m_cities as c','t1.city_id','=','c.city_id')
              ->leftJoin('m_station_groups as sg','t1.station_group_id','=','sg.station_group_id')
              ->leftJoin('m_stations as s','t1.station_group_id','=','s.station_group_id')
              ->where(function ($query) use ($request) {
                  if (isset($request['type']) && $request['type'] === 'city') {
                      $query->whereNotNull('t1.city_id');
                  } elseif (isset($request['type']) && $request['type'] === 'station') {
                      $query->whereNotNull('t1.station_group_id');
                  } 
              })
              ->whereRaw('case when t1.station_group_id > 0 then s.is_main = 1 else 1 = 1 end')
              ->orderBy('t1.added_at','desc')
              ->get();

          if ( $page !== null && $limit !== null ) {
              $list = collect($list);
              $list = $list->forPage($page,$limit);
          }

          $this->create_list_model($list, $this->_siteName);
          return $list;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function get_top_city_list()
  {
      try {
          $list = $this->_db->table($this->_tableName.' as t1')->select(
              't1.city_id as city_id',
              'c.pref_name as pref_name',
              'c.pref_pathname as pref_pathname',
              'c.city_name as city_name',
              'c.city_pathname as city_pathname'
              )
              ->leftJoin('m_cities as c','t1.city_id','=','c.city_id')
              ->whereNotNull('t1.city_id')
              ->orderBy('t1.added_at','desc')
              ->get();

          $this->create_city_list_model($list, $this->_siteName);
          return $list;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function get_top_station_list()
  {
      try {
          $list = $this->_db->table($this->_tableName.' as t1')
            ->select(
              't1.station_group_id as station_group_id',
              's.station_name as station_name',
              'c.pref_name as pref_name',
              'c.pref_pathname as pref_pathname',
              'sg.station_pathname as station_pathname'
              )
            ->leftJoin('m_stations as s', 't1.station_group_id', '=', 's.station_group_id')
            ->leftJoin('m_station_groups as sg', 's.station_group_id', '=', 'sg.station_group_id')
            ->leftJoin('m_cities as c', 's.city_id', '=', 'c.city_id')
            ->whereNotNull('t1.station_group_id')
            ->where('s.is_main', '=', 1)
            ->groupBy('s.station_group_id')
            ->orderBy('t1.added_at','desc')
            ->get();
          $this->create_station_list_model($list, $this->_siteName);
          return $list;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function get_total_count($request = null)
  {
      try {
          return $this->_db->table($this->_tableName)->select('city_id')
              ->where(function ($query) use ($request) {
                  if (isset($request['type']) && $request['type'] === 'city') {
                      $query->whereNotNull('city_id');
                  } elseif (isset($request['type']) && $request['type'] === 'station') {
                      $query->whereNotNull('station_group_id');
                  } 
              })
              ->count();
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function get_city($city_id)
  {
      try {
          $data = $this->_db->table($this->_tableName.' as t1')->select(
              'c.city_id as city_id',
              'c.pref_name as pref_name',
              'c.city_name as city_name',
              $this->_db->raw('case when t1.added_at is null then "" else t1.added_at end as added_at')
              )
              ->rightJoin('m_cities as c','t1.city_id','=','c.city_id')
              ->where('c.city_id','=',$city_id)
              ->first();

          $this->create_city_row_model($data, $this->_siteName);
          return $data;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function get_station($station_group_id)
  {
      try {
          $data = $this->_db->table($this->_tableName.' as t1')->select(
              's.station_group_id as station_group_id',
              's.station_name as station_name',
              $this->_db->raw('case when t1.added_at is null then "" else t1.added_at end as added_at')
              )
              ->rightJoin('m_stations as s','t1.station_group_id','=','s.station_group_id')
              ->where('s.station_group_id','=',$station_group_id)
              ->where('s.is_main','=',1)
              ->first();

          $this->create_station_row_model($data, $this->_siteName);
          return $data;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function insert($request)
  {
      $columns = [];
      $columns += [$request['colname'] => $request['id']];
      $columns += ['added_at' => Carbon::now()];

      try {
        $this->_db->table($this->_tableName)->insert($columns);
        return true;
      } catch (\Exception $e) {
      	$queryLogs = $this->_db->getQueryLog();
      	$this->_db->rollback();
      	throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function delete($request)
  {
      try {
          $this->_db->table($this->_tableName)
            ->where($request['colname'],'=',$request['id'])
            ->delete();
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

}
