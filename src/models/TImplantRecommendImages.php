<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TImplantRecommendImages extends Base
{
  public function __construct()
  {
      parent::__construct();
      $this->_tableName = 't_implant_recommend_images';
  }

	public function get_by_id($recommend_id, $image_id)
  {
  		try {
        	return $this->_db->table($this->_tableName)
        			->select(
                  'recommend_id',
                  'image_id',
                  'image_attr',
                  'filename'
               )
              ->where('recommend_id','=',$recommend_id)
              ->where('image_id','=',$image_id)->first();
    	} catch (\Exception $e) {
					$queryLogs = $this->_db->getQueryLog();
					throw new \Exceptions\SqlException($e,$queryLogs);
    	}
  }

  public function insert($recommend_id)
  {
      $columns = [];
      $columns += ['recommend_id' => $recommend_id];
      $columns += ['image_id' => 1];
      $columns += ['sort_order' => 1];
      $columns += ['created_at' => Carbon::now()];
      $columns += ['updated_at' => Carbon::now()];
      $columns += ['deleted_at' => null];

      try {
          $this->_db->table($this->_tableName)->insert($columns);
      } catch (\Exception $e) {
      		$queryLogs = $this->_db->getQueryLog();
      		$this->_db->rollback();
      		throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function update_row($request)
  {
	  try {
		  $columns = [];
		  $columns += ['updated_at' => Carbon::now()];
		  if (!empty($request['image_attr'])) {
		  	$columns += ['image_attr' => $request['image_attr']];
		  }
		  if (!empty($request['filename'])) {
		  	$columns += ['filename' => $request['filename']];
		  }
		  $this->_db->table($this->_tableName)
		  			->where('recommend_id','=',(int)$request['recommend_id'])
		  			->where('image_id','=',(int)$request['image_id'])
		  			->update($columns);
	  } catch (\Exception $e) {
		  $queryLogs = $this->_db->getQueryLog();
		  $this->_db->rollback();
		  throw new \Exceptions\SqlException($e,$queryLogs);
	  }
  }

}
