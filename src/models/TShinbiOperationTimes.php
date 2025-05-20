<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TShinbiOperationTimes extends Base
{
    use \Modelings\ClinicOperationTimes;

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_shinbi_operation_times';
    }

    public function get_by_clinic_id($clinic_id)
  {
        try {
            $operation_times = $this->_db->table($this->_tableName.' as so')
                      ->select(
                          'so.operation_time_id as operation_time_id',
                          $this->_db->raw('case when so.start_at != "" then so.start_at else o.start_at end as start_at'),
                          $this->_db->raw('case when so.end_at != "" then so.end_at else o.end_at end as end_at'),
                          $this->_db->raw('case when so.is_mon_open != "" then so.is_mon_open else o.is_mon_open end as is_mon_open'),
                          $this->_db->raw('case when so.is_tue_open != "" then so.is_tue_open else o.is_tue_open end as is_tue_open'),
                          $this->_db->raw('case when so.is_wed_open != "" then so.is_wed_open else o.is_wed_open end as is_wed_open'),
                          $this->_db->raw('case when so.is_thu_open != "" then so.is_thu_open else o.is_thu_open end as is_thu_open'),
                          $this->_db->raw('case when so.is_fri_open != "" then so.is_fri_open else o.is_fri_open end as is_fri_open'),
                          $this->_db->raw('case when so.is_sat_open != "" then so.is_sat_open else o.is_sat_open end as is_sat_open'),
                          $this->_db->raw('case when so.is_sun_open != "" then so.is_sun_open else o.is_sun_open end as is_sun_open'),
                          'o.sort_order as sort_order'
                          )
                      ->leftJoin('t_clinic_operation_times as o',function ($join) {
                            $join->on('o.clinic_id','=','so.clinic_id')->on('o.operation_time_id','=','so.operation_time_id');
                        })
                      ->where('so.clinic_id','=',$clinic_id)
                      ->orderBy('so.sort_order','asc')
                      ->orderBy('o.sort_order', 'asc')
            ->get();
            $this->create_portallist_model($operation_times);
            return $operation_times;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
    }
  }

    public function get_by_clinic_id_cms($id)
    {
        try {
            $list = $this->_db->table($this->_tableName)
                ->select(
                    //'clinic_id',
                    'operation_time_id',
                    'start_at',
                    'end_at',
                    'is_mon_open',
                    'is_tue_open',
                    'is_wed_open',
                    'is_thu_open',
                    'is_fri_open',
                    'is_sat_open',
                    'is_sun_open'
                )
                ->where('clinic_id', '=', $id)
                ->orderBy('sort_order', 'asc')->get();

            $this->create_cmslist_model($list);
            return $list;

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function insert($request)
    {
        $start_at = (empty($request['start_at']) || $request['start_at'] === '00:00:00') ? null : $request['start_at'];
        $end_at = (empty($request['end_at']) || $request['end_at'] === '00:00:00') ? null : $request['end_at'];

        $columns = [];
        $columns += ['clinic_id' => $request['clinic_id']];
        $columns += ['start_at' => $start_at];
        $columns += ['end_at' => $end_at];
        $columns += ['is_mon_open' => $request['is_mon_open']];
        $columns += ['is_tue_open' => $request['is_tue_open']];
        $columns += ['is_wed_open' => $request['is_wed_open']];
        $columns += ['is_thu_open' => $request['is_thu_open']];
        $columns += ['is_fri_open' => $request['is_fri_open']];
        $columns += ['is_sat_open' => $request['is_sat_open']];
        $columns += ['is_sun_open' => $request['is_sun_open']];
        $columns += ['created_at' => Carbon::now()];
        $columns += ['updated_at' => Carbon::now()];
        $columns += ['deleted_at' => null];

        try {
            $operation_time_id = $this->get_child_alternatekey('clinic',$request['clinic_id'],'operation_time');
            $columns += ['operation_time_id' => $operation_time_id];
            $columns += ['sort_order' => $operation_time_id];
            $this->_db->table($this->_tableName)->insert($columns);
            return true;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function delete_by_clinic_id($clinic_id)
    {
        try {
            $this->_db->table($this->_tableName)
            ->where('clinic_id','=',$clinic_id)
            ->delete();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }
}
