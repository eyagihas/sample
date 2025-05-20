<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TKyouseiSelfInvisalignFee extends Base
{
    //use \Modelings\KyouseiSelfInvisalignFee;

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_kyousei_self_invisalign_fee';
    }


    public function get_by_clinic_id_cms($id)
    {
        try {
            $list = $this->_db->table($this->_tableName)
                ->select(
                    //'clinic_id',
                    'fee_id',
                    'fee_name',
                    'fee'
                )
                ->where('clinic_id', '=', $id)
                ->orderBy('fee_id', 'asc')->get();

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
        $columns += ['fee_id' => $request['fee_id']];
        $columns += ['fee_name' => $this->h($request['fee_name'])];
        $columns += ['fee' => $this->h($request['fee'])];
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
