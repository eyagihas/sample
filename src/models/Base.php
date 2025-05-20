<?php

namespace Models;

abstract class Base
{
    protected $_tableName = '';
    protected $_siteName = '';
    protected $_db = null;

    public function __construct()
    {
        $this->_db = \Application::getInstance()->getContainer()['masterDB'];
    }

    public function getByIdInternal($idFieldPrefix, $idValue, $targetFields)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->select($targetFields)
                ->where($idFieldPrefix.'_id', $idValue)
                ->orderBy($idFieldPrefix.'_id', 'asc')->get();
        } catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    protected function replaceEmptyToNull($data)
    {
		    foreach ( $data as $key => $value ) {
			       if ( $value === '' ) {
				           $data[$key] = null;
             }
        }
		    return $data;
    }

    public function get_alternatekey($prefix)
    {
        try{
            $id = $this->_db->table($this->_tableName)->max($prefix.'_id');
            return (is_null($id)) ? 1 : (int) $id + 1;
        } catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

	public function get_child_alternatekey($parent_prefix,$parent_id,$prefix)
    {
        try{
            $id = $this->_db->table($this->_tableName)->where($parent_prefix.'_id',$parent_id)->max($prefix.'_id');
            return (is_null($id)) ? 1 : (int) $id + 1;
        } catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

	public function get_grandson_alternatekey($grand_parent_prefix,$grand_parent_id,$parent_prefix,$parent_id,$prefix)
    {
        try{
            $id = $this->_db->table($this->_tableName)
							->where($grand_parent_prefix.'_id',$grand_parent_id)
							->where($parent_prefix.'_id',$parent_id)
							->max($prefix.'_id');
            return (is_null($id)) ? 1 : (int) $id + 1;
        } catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_grandson_order($grand_parent_prefix,$grand_parent_id,$parent_prefix,$parent_id)
    {
        try{
            $order = $this->_db->table($this->_tableName)
                            ->where($grand_parent_prefix.'_id',$grand_parent_id)
                            ->where($parent_prefix.'_id',$parent_id)
                            ->max('sort_order');
            return (is_null($order)) ? 1 : (int) $order + 1;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_child_maxkey($parent_prefix,$parent_id,$prefix)
    {
        try{
            $id = $this->_db->table($this->_tableName)->where($parent_prefix.'_id',$parent_id)->max($prefix.'_id');
            return (is_null($id)) ? 0 : (int) $id;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    public function mbtrim($str) {
        return preg_replace("/(^\s+)|(\s+$)/u", "", $str);
    }
}
