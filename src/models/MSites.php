<?php

namespace Models;

class MSites extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 'm_sites';
    }

	public function get_by_id($site_id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->select(
                    'site_id',
                    'site_name',
					'site_pathname',
					'site_url',
                    'plus_url'
                    )
                ->where('site_id','=',$site_id)
				->first();
        } catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_all()
    {
        try {
            return $this->_db->table($this->_tableName)
                ->select(
                    'site_id',
                    'site_name',
                    'site_pathname',
                    'site_url',
                    'plus_url'
                    )
                ->orderBy('sort_order', 'asc')->get();
        } catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_others($site_id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->select(
                    'site_id',
                    'site_name',
                    'site_pathname',
                    'site_url',
                    'plus_url'
                    )
                ->where('site_id','!=',$site_id)
                ->orderBy('sort_order', 'asc')->get();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

	public function get_by_pathname($pathname)
    {
        try {
            return $this->_db->table($this->_tableName)
				->select('site_id','site_name','site_pathname','site_url','plus_url')
				->where('site_pathname','=',$pathname)
                ->first();
        } catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_by_baseurl($server)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->select('site_id','site_name','site_pathname','site_url','plus_url')
                ->where('plus_url','=',$server)
                ->first();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_id_by_pathname($pathname)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->select('site_id')
                ->where('site_pathname','=',$pathname)
                ->first();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }
}
