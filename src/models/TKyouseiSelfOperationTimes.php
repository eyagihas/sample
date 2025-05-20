<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TKyouseiSelfOperationTimes extends Base
{
    use \Modelings\ClinicOperationTimes;

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_kyousei_self_operation_times';
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
                ->orderBy('operation_time_id', 'asc')->get();

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
