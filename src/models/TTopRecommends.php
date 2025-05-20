<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TTopRecommends extends Base
{

  use \Modelings\TopRecommends;

  public function __construct($sitename = '')
  {
      parent::__construct();
      $this->_tableName = 't_'.$sitename.'_top_recommends';
      $this->_siteName = $sitename;
  }

  public function exists($request)
  {
      try {
          return $this->_db->table($this->_tableName)
          ->where('recommend_id','=',$request['recommend_id'])
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
              't1.recommend_id as recommend_id',
              'r.title as title',
              'r.publish_at as publish_at',
              'r.updated_at as updated_at'
              )
              ->leftJoin('t_'.$this->_siteName.'_recommends as r','t1.recommend_id','=','r.recommend_id')
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

  public function get_portal_top_list()
  {
      try {
          $list = $this->_db->table($this->_tableName.' as t1')->select(
              't1.recommend_id as recommend_id',
              'r.title as title',
              'r.publish_at as publish_at',
              'r.updated_at as updated_at',
              $this->_db->raw('case when c.pref_pathname != "" then c.pref_pathname else cs.pref_pathname end as pref_pathname'),
              'c.city_pathname as city_pathname',
              'sg.station_pathname as station_pathname',
              'a.attribute_pathname as attribute_pathname',
              'i.filename as filename',
              'i.image_attr as image_attr'
              )
              ->leftJoin('t_'.$this->_siteName.'_recommends as r','t1.recommend_id','=','r.recommend_id')
              ->leftJoin('m_attributes as a','r.attribute_id','=','a.attribute_id')
              ->leftJoin('m_cities as c','r.city_id','=','c.city_id')
              ->leftJoin('m_station_groups as sg','r.station_group_id','=','sg.station_group_id')
              ->leftJoin('m_stations as s','r.station_group_id','=','s.station_group_id')
              ->leftJoin('m_cities as cs','s.city_id','=','cs.city_id')
              ->leftJoin('t_implant_recommend_images as i','r.recommend_id','=','i.recommend_id')
              ->where(function ($query) use ($request, $type) {
                  if ($type === 'station') {
                      $query->where('r.station_group_id','>',0);
                  } elseif ($type === 'area') {
                    $query->where('r.city_id','>',0);
                  }
              })
              ->whereRaw('case when r.station_group_id > 0 then s.is_main = 1 else 1 = 1 end')
              ->where('r.is_published','=',1)
              ->where('r.publish_at','<=',Carbon::now())
              ->orderBy('t1.added_at','desc')
              ->get();

          $this->create_portal_list_model($list, $this->_siteName);
          return $list;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function get_total_count($request = null)
  {
      try {
          return $this->_db->table($this->_tableName)->select('t1.recommend_id')
              ->count();
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function get_recommend($request)
  {
      try {
          $data = $this->_db->table('t_'.$this->_siteName.'_recommends as r')->select(
              'r.recommend_id as recommend_id',
              'r.title as title',
              'r.publish_at as publish_at',
              'r.updated_at as updated_at',
              $this->_db->raw('case when t1.added_at is null then "" else t1.added_at end as added_at')
              )
              ->leftJoin($this->_tableName.' as t1','t1.recommend_id','=','r.recommend_id')
              ->where(function ($query) use ($request) {
                    if (!empty($request['city_id'])) {
                        $query->where('r.city_id','=',$request['city_id']);
                    } elseif (!empty($request['station_group_id'])) {
                        $query->where('r.station_group_id','=',$request['station_group_id']);
                    }
                })
              ->where('r.attribute_id','=',$request['attribute_id'])
              ->where('r.is_published','=',1)
              ->where('r.publish_at','<=',Carbon::now())
              ->whereNotNull('r.recommend_id')
              ->first();
          if (!empty($data)) {
              $this->create_row_model($data, $this->_siteName);
          }
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
      $columns += ['recommend_id' => $request['id']];
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
            ->where('recommend_id','=',$request['id'])
            ->delete();
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

}
