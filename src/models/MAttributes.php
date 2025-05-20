<?php

namespace Models;

class MAttributes extends Base
{
    use \Modelings\Attributes;

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 'm_attributes';
    }

    public function get_list($request = null)
    {
        try {
            $list = $this->_db->table($this->_tableName)
                ->select(
                    'attribute_id',
                    'attribute_name',
                    'attribute_flgname',
                    'attribute_pathname',
                    'self_form_annotation'
                    )
                ->where(function ($query) use ($request) {
                    if ( isset($request['is_13'])) {
                        $query->where('is_'.$request['type'].'_attribute','=',1);
                    }
                    if ( isset($request['is_specified'])) {
                        $query->where('is_'.$request['type'].'_specified','=',1);
                    }
                    if ( isset($request['is_valid'])) {
                        $query->where('is_valid','=',1);
                    }
                    if ( isset($request['is_self_visible'])) {
                        $query->where('is_'.$request['type'].'_self_visible','=',1);
                    }
                    if ( isset($request['attribute_type'])) {
                        $query->where($request['type'].'_attribute_type','=',$request['attribute_type']);
                    }
                })
                ->orderBy($request['type'].'_sort_order', 'asc')->get();
                $this->create_cmslist_model($request['type'], $list);
                return $list;
        } catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_id_by_flgname($flgname, $sitename = 'kyousei', $attribute_type = 1)
    {
        try {
            $data = $this->_db->table($this->_tableName)
                ->select('attribute_id')
                ->where('attribute_flgname', '=', $flgname)
                ->where('is_'.$sitename.'_attribute', '=', 1)
                ->where($sitename.'_attribute_type', '=', $attribute_type)
                ->first();
            return $data->attribute_id;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_child_list_by_flgname($flgname)
    {
        try {
            return $this->_db->table($this->_tableName.' as a')
                ->select(
                    'c.child_attribute_id',
                    'c.child_attribute_name',
                    'c.child_attribute_flgname'
                    )
                ->join('m_child_attributes as c', 'a.attribute_id', '=', 'c.attribute_id')
                ->where('a.attribute_flgname', '=', $flgname)
                ->get();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_by_flgname($flgname)
    {
        try {
            $attribute = $this->_db->table($this->_tableName)
                ->select(
                    'attribute_id',
                    'attribute_name',
                    'attribute_flgname',
                    'attribute_pathname'
                    )
                ->where('attribute_flgname', '=', $flgname)
                ->first();
            return $attribute;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

}
