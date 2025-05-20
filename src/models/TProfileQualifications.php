<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TProfileQualifications extends Base
{
    //use \Modelings\ProfileQualifications;

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_profile_qualifications';
    }


    public function get_by_profile_id($id, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';
        try {
            $list = $this->_db->table($this->_tableName.$postfix)
                ->select(
                    'qualification_id',
                    'organization_id',
                    'title_id_list',
                    'free_text'
                )
                ->where('profile_id', '=', $id)
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
        $columns += ['qualification_id' => $request['qualification_id']];
        $columns += ['free_text' => $request['free_text']];
        $columns += ['sort_order' => $request['qualification_id']];
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
}
