<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TImplantRecommendClinicFlows extends Base
{
    use \Modelings\ClinicFlows;
    
    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_implant_recommend_clinic_flows';
    }

    public function get_by_clinic_id($recommend_id, $clinic_id, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        try {
            $flows = $this->_db->table($this->_tableName.$postfix.' as f')
                      ->select(
                          'f.recommend_id as recommend_id',
                          'f.clinic_id as clinic_id',
                          'f.flow_id as flow_id',
                          'f.flow_title as flow_title',
                          'f.flow_text as flow_text',
                          'f.sort_order as sort_order'
                          )
            ->where('f.recommend_id','=',$recommend_id)
            ->where('f.clinic_id','=',$clinic_id)
            ->orderBy('f.sort_order','asc')
            ->get();
            $this->create_portallist_model($flows);
            return $flows;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_cms_by_clinic_id($recommend_id, $clinic_id, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        try {
            $flows = $this->_db->table($this->_tableName.$postfix.' as f')
                      ->select(
                          'f.recommend_id as recommend_id',
                          'f.clinic_id as clinic_id',
                          'f.flow_id as flow_id',
                          'f.flow_title as flow_title',
                          'f.flow_text as flow_text',
                          'f.sort_order as sort_order'
                          )
            ->where('f.recommend_id','=',$recommend_id)
            ->where('f.clinic_id','=',$clinic_id)
            ->orderBy('f.sort_order','asc')
            ->get();
            return $flows;
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
        $columns += ['flow_id' => $request['flow_id']];
        $columns += ['flow_title' => $request['flow_title']];
        $columns += ['flow_text' => $request['flow_text']];
        $columns += ['sort_order' => $request['flow_id']];
        $columns += ['created_at' => Carbon::now()];
        $columns += ['updated_at' => Carbon::now()];
        $columns += ['deleted_at' => null];

        try {
            $this->delete_by_row($request);
            $this->delete_by_row($request, true);

            if ( !empty($request['flow_title']) || !empty($request['flow_text']) ) {
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
        $columns += ['flow_id' => $request['flow_id']];
        $columns += ['flow_title' => $request['flow_title']];
        $columns += ['flow_text' => $request['flow_text']];
        $columns += ['sort_order' => $request['flow_id']];
        $columns += ['created_at' => Carbon::now()];
        $columns += ['updated_at' => Carbon::now()];
        $columns += ['deleted_at' => null];

        try {
            $this->delete_by_row($request, $isPreview);

            if ( !empty($request['flow_title']) || !empty($request['flow_text']) ) {
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
            ->where('flow_id', '=', $request['flow_id'])
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
