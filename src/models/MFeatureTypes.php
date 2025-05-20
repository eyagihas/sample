<?php

namespace Models;

class MFeatureTypes extends Base
{
  //use \Modelings\FeatureTypes;

  public function __construct()
  {
      parent::__construct();
      $this->_tableName = 'm_feature_types';
  }

  public function get_all()
  {
      try {
        $list = $this->_db->table($this->_tableName)
            ->select(
                'feature_type_id',
                'feature_type_name'
                )
            ->where('is_valid', '=', 1)
            ->orderBy('sort_order', 'asc')->get();
        //$this->create_portal_model($list);
        return $list;
      } catch (\Exception $e) {
		  $queryLogs = $this->_db->getQueryLog();
		  throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function get_feature_type_name($feature_type_id)
  {
      try {
        return $this->_db->table($this->_tableName)
            ->select(
                'feature_type_name'
                )
            ->where('feature_type_id', '=', $feature_type_id)
            ->first();
      } catch (\Exception $e) {
      $queryLogs = $this->_db->getQueryLog();
      throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

}
