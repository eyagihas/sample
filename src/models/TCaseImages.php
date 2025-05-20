<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TCaseImages extends Base
{

  use \Modelings\CaseImages;

  public function __construct($sitename = '')
  {
      parent::__construct();
      $this->_tableName = 't_'.$sitename.'_case_images';
      $this->_siteName = $sitename;
  }

  public function insert($request)
  {
      $columns = [];
      $columns += ['clinic_id' => $request['clinic_id']];
      $columns += ['case_id' => $request['case_id']];
      //$columns += ['filename' => $request['filename']];
      $columns += ['created_at' => Carbon::now()];
      $columns += ['updated_at' => Carbon::now()];
      $columns += ['deleted_at' => null];

      try {
        $image_id = $this->get_child_alternatekey('case',$request['case_id'],'image');
        $columns += ['image_id' => $image_id];
        $columns += ['sort_order' => $image_id];
        $this->_db->table($this->_tableName)->insert($columns);
        return true;
      } catch (\Exception $e) {
      	$queryLogs = $this->_db->getQueryLog();
      	$this->_db->rollback();
      	throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function insert_for_upload($request)
  {
      $columns = [];
      $columns += ['clinic_id' => $request['clinic_id']];
      $columns += ['case_id' => $request['case_id']];
      $columns += ['image_id' => $request['image_id']];
      $columns += ['is_'.$request['case_type'] => 1];
      $columns += ['filename' => $request['filename']];
      $columns += ['sort_order' => $request['image_id']];
      $columns += ['created_at' => Carbon::now()];
      $columns += ['updated_at' => Carbon::now()];
      $columns += ['deleted_at' => null];

      try {
        $this->_db->table($this->_tableName)->insert($columns);
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
            ->where('case_id','=',$request['case_id'])
            ->where('image_id','=',$request['image_id'])
            ->delete();
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function delete_by_case_id($case_id)
  {
      try {
          $this->_db->table($this->_tableName)
            ->where('case_id','=',$case_id)
            ->delete();
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function exists($id, $image_id)
  {
      try {
        return $this->_db->table($this->_tableName)
          ->where('case_id', '=', $id)
          ->where('image_id', '=', $image_id)->exists();
      } catch (\Exception $e) {
        $queryLogs = $this->_db->getQueryLog();
        throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function exists_by_filename($id, $filename)
  {
      try {
        return $this->_db->table($this->_tableName)
          ->where('case_id', '=', $id)
          ->where('filename', '=', $filename)->exists();
      } catch (\Exception $e) {
        $queryLogs = $this->_db->getQueryLog();
        throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function exists_by_case($id)
  {
      try {
        return $this->_db->table($this->_tableName)
          ->where('case_id', '=', $id)
          ->exists();
      } catch (\Exception $e) {
        $queryLogs = $this->_db->getQueryLog();
        throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function get_list($case_id, $site_pathname, $type)
  {
      try {
          $list = $this->_db->table($this->_tableName)
            ->select('clinic_id', 'case_id', 'image_id')
            ->where('case_id','=',$case_id)
            ->where('is_'.$type,'=',1)
            ->orderBy('sort_order')
            ->get();
          $this->create_cmslist_model($list, $site_pathname);
          return $list;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

}
