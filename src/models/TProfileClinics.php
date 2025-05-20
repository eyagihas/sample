<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TProfileClinics extends Base
{
    //use \Modelings\ProfileQualifications;

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_profile_clinics';
    }


    public function get_by_profile_id($id, $isPreview = false, $site_pathname = null)
    {
        $postfix = ($isPreview) ? '_preview' : '';
        try {
            $list = $this->_db->table($this->_tableName.$postfix.' as pc')
                ->select(
                    'pc.clinic_id as clinic_id',
                    $this->_db->raw('case when sc.clinic_name != "" then sc.clinic_name else c.clinic_name end as clinic_name'),
                    'pc.sort_order as sort_order'
                )
                ->leftJoin('t_clinics as c', 'pc.clinic_id', '=', 'c.clinic_id')
                ->leftJoin('t_'.$site_pathname.'_clinics as sc', 'pc.clinic_id', '=', 'sc.clinic_id')
                ->where('pc.profile_id', '=', $id)
                ->orderBy('sort_order', 'asc')->get();

            return $list;

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function insert($request, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';
        $columns = [];
        $columns += ['profile_id' => $request['profile_id']];
        $columns += ['clinic_id' => $request['clinic_id']];
        $columns += ['sort_order' => $request['sort_order']];
        $columns += ['created_at' => Carbon::now()];
        $columns += ['updated_at' => Carbon::now()];
        $columns += ['deleted_at' => null];

        try {
            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName.$postfix)->insert($columns);
            $this->_db->commit();
            return true;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function delete_by_profile_id($profile_id, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';
        try {
            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName.$postfix)->where('profile_id','=',$profile_id)->delete();
            $this->_db->commit();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function unrelate($request)
    {
        try {
            $this->_db->beginTransaction();

            $this->_db->table($this->_tableName)
                ->where('profile_id','=',$request['profile_id'])
                ->where('clinic_id','=',$request['clinic_id'])->delete();

            $this->_db->table($this->_tableName.'_preview')
                ->where('profile_id','=',$request['profile_id'])
                ->where('clinic_id','=',$request['clinic_id'])->delete();

            $this->_db->commit();
            return true;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }

    }
}
