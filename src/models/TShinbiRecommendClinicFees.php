<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TShinbiRecommendClinicFees extends Base
{
    use \Modelings\ClinicFees;
    
    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_shinbi_recommend_clinic_fees';
    }

    public function get_by_clinic_id($recommend_id, $clinic_id, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        try {
            $fees = $this->_db->table($this->_tableName.$postfix.' as f')
                      ->select(
                          'f.recommend_id as recommend_id',
                          'f.clinic_id as clinic_id',
                          'f.fee_id as fee_id',
                          'f.fee_name as fee_name',
                          'f.fee as fee',
                          'f.sort_order as sort_order'
                          )
            ->where('f.recommend_id','=',$recommend_id)
            ->where('f.clinic_id','=',$clinic_id)
            ->orderBy('f.sort_order','asc')
            ->get();
            $this->create_portallist_model($fees);
            return $fees;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_cms_by_clinic_id($recommend_id, $clinic_id, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        try {
            $fees = $this->_db->table($this->_tableName.$postfix.' as f')
                      ->select(
                          'f.recommend_id as recommend_id',
                          'f.clinic_id as clinic_id',
                          'f.fee_id as fee_id',
                          'f.fee_name as fee_name',
                          'f.fee as fee',
                          'f.sort_order as sort_order'
                          )
            ->where('f.recommend_id','=',$recommend_id)
            ->where('f.clinic_id','=',$clinic_id)
            ->orderBy('f.sort_order','asc')
            ->get();
            return $fees;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function exists_preview($recommend_id, $clinic_id)
    {
        try {
            return $this->_db->table($this->_tableName.'_preview')
                ->where('recommend_id', '=', $recommend_id)
                ->where('clinic_id', '=', $clinic_id)->exists();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }
    
    public function insert_for_import($request, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        $columns = [];
        $columns += ['recommend_id' => $request['recommend_id']];
        $columns += ['clinic_id' => $request['clinic_id']];
        $columns += ['fee_id' => $request['fee_id']];
        $columns += ['fee_name' => $request['fee_name']];
        $columns += ['fee' => $request['fee']];
        $columns += ['sort_order' => $request['fee_id']];
        $columns += ['created_at' => Carbon::now()];
        $columns += ['updated_at' => Carbon::now()];
        $columns += ['deleted_at' => null];

        try {
            $this->delete_by_row($request);
            $this->delete_by_row($request, true);

            if ( !empty($request['fee_name']) || !empty($request['fee']) ) {
                $this->_db->table($this->_tableName.$postfix)->insert($columns);
            }
            return true;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function insert($request, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';
        $request = (is_object($request)) ? (array)$request : $request;

        $columns = [];
        $columns += ['recommend_id' => $request['recommend_id']];
        $columns += ['clinic_id' => $request['clinic_id']];
        $columns += ['fee_id' => $request['fee_id']];
        $columns += ['fee_name' => $request['fee_name']];
        $columns += ['fee' => $request['fee']];
        $columns += ['sort_order' => $request['fee_id']];
        $columns += ['created_at' => Carbon::now()];
        $columns += ['updated_at' => Carbon::now()];
        $columns += ['deleted_at' => null];

        try {
            $this->delete_by_row($request, $isPreview);

            if ( !empty($request['fee_name']) || !empty($request['fee']) ) {
                $this->_db->table($this->_tableName.$postfix)->insert($columns);
            }
            return true;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function delete_by_row($request, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';
        try {
            $this->_db->table($this->_tableName.$postfix)
            ->where('recommend_id','=', $request['recommend_id'])
            ->where('clinic_id', '=', $request['clinic_id'])
            ->where('fee_id', '=', $request['fee_id'])
            ->delete();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function delete_by_group($request, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';
        try {
            $this->_db->table($this->_tableName.$postfix)
            ->where('recommend_id','=', $request['recommend_id'])
            ->where('clinic_id', '=', $request['clinic_id'])
            ->delete();
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
