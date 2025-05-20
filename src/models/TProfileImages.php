<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TProfileImages extends Base
{
    use \Modelings\ProfileImages;

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_profile_images';
    }


    public function get_by_profile_id($id)
    {
        try {
            $list = $this->_db->table($this->_tableName)
                ->select(
                    'image_id',
                    'image_attr',
                    'filename'
                )
                ->where('profile_id', '=', $id)
                ->orderBy('sort_order', 'asc')->get();
            $this->create_cmslist_model($list);
            return $list;

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function insert_for_upload($request)
    {

        $columns = [];
        $columns += ['profile_id' => $request['profile_id']];
        $columns += ['image_id' => $request['image_id']];
        $columns += ['filename' => $request['filename']];
        $columns += ['sort_order' => $request['image_id']];
        $columns += ['created_at' => Carbon::now()];
        $columns += ['updated_at' => Carbon::now()];
        $columns += ['deleted_at' => null];

        try {
            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName)->insert($columns);
            $this->_db->commit();
            return true;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function delete_by_row($request)
    { 
        try {
            $this->_db->table($this->_tableName)
                ->where('profile_id','=',$request['profile_id'])
                ->where('image_id','=',$request['image_id'])
                ->delete();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function delete_by_profile_id($profile_id)
    {
        try {
            $this->_db->table($this->_tableName)->where('profile_id','=',$profile_id)->delete();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }
}
