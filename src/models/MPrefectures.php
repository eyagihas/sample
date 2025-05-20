<?php

namespace Models;

class MPrefectures extends Base
{
  public function __construct()
  {
      parent::__construct();
      $this->_tableName = 'm_prefectures';
  }

  public function get_all($request = null)
  {
      try {
        return $this->_db->table($this->_tableName)
            ->select(
                'prefecture_id',
                'prefecture_name'
                )
            ->where(function ($query) use ($request) {
                if ( isset($request['is_domestic'])) {
                  $query->where('prefecture_id','<=',47);
                }
            })
            ->orderBy('sort_order', 'asc')->get();
      } catch (\Exception $e) {
		  $queryLogs = $this->_db->getQueryLog();
		  throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

}
