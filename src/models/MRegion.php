<?php

namespace Models;

class MRegion extends Base
{
  public function __construct()
  {
      parent::__construct();
      $this->_tableName = 'm_region';
  }

  public function get_all($request = null)
  {
      try {
        return $this->_db->table($this->_tableName)
            ->select(
                'region_id',
                'region_name'
                )
            ->orderBy('sort_order', 'asc')->get();
      } catch (\Exception $e) {
		  $queryLogs = $this->_db->getQueryLog();
		  throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

}
