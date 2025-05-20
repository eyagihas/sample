<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TImplantOperationTimes extends Base
{
	use \Modelings\ClinicOperationTimes;

	public function __construct() {
		parent::__construct();
        $this->_tableName = 't_implant_operation_times';
	}

  public function get_by_clinic_id($clinic_id)
  {
		try {
			$operation_times = $this->_db->table($this->_tableName.' as io')
					  ->select(
						  'io.operation_time_id as operation_time_id',
						  $this->_db->raw('case when io.start_at != "" then io.start_at else o.start_at end as start_at'),
						  $this->_db->raw('case when io.end_at != "" then io.end_at else o.end_at end as end_at'),
						  $this->_db->raw('case when io.is_mon_open != "" then io.is_mon_open else o.is_mon_open end as is_mon_open'),
						  $this->_db->raw('case when io.is_tue_open != "" then io.is_tue_open else o.is_tue_open end as is_tue_open'),
						  $this->_db->raw('case when io.is_wed_open != "" then io.is_wed_open else o.is_wed_open end as is_wed_open'),
						  $this->_db->raw('case when io.is_thu_open != "" then io.is_thu_open else o.is_thu_open end as is_thu_open'),
						  $this->_db->raw('case when io.is_fri_open != "" then io.is_fri_open else o.is_fri_open end as is_fri_open'),
						  $this->_db->raw('case when io.is_sat_open != "" then io.is_sat_open else o.is_sat_open end as is_sat_open'),
						  $this->_db->raw('case when io.is_sun_open != "" then io.is_sun_open else o.is_sun_open end as is_sun_open'),
						  'o.sort_order as sort_order'
						  )
					  ->leftJoin('t_clinic_operation_times as o',function ($join) {
					  		$join->on('o.clinic_id','=','io.clinic_id')->on('o.operation_time_id','=','io.operation_time_id');
					  	})
					  ->where('io.clinic_id','=',$clinic_id)
					  ->orderBy('io.sort_order','asc')
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
