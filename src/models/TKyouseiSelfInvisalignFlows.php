<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TKyouseiSelfInvisalignFlows extends Base
{
    //use \Modelings\KyouseiSelfInvisalignFlows;

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_kyousei_self_invisalign_flows';
    }


    public function get_by_clinic_id_cms($id)
    {
        try {
            $list = $this->_db->table($this->_tableName)
                ->select(
                    //'clinic_id',
                    'flow_id',
                    'flow_title',
                    'flow_text'
                )
                ->where('clinic_id', '=', $id)
                ->orderBy('flow_id', 'asc')->get();

            return $list;

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function insert($request)
    {

        $columns = [];
        $columns += ['clinic_id' => $request['clinic_id']];
        $columns += ['flow_id' => $request['flow_id']];
        $columns += ['flow_title' => $this->h($request['flow_title'])];
        $columns += ['flow_text' => $this->h($request['flow_text'])];
        $columns += ['created_at' => Carbon::now()];
        $columns += ['updated_at' => Carbon::now()];
        $columns += ['deleted_at' => null];

        try {
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
