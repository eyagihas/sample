<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TClinicImages extends Base
{

  use \Modelings\ClinicImages;

  public function __construct($sitename = '')
  {
      parent::__construct();
      $this->_tableName = 't_'.$sitename.'_clinic_images';
      $this->_siteName = $sitename;
  }

  public function insert($request)
  {
      $columns = [];
      $columns += ['clinic_id' => $request['clinic_id']];
      $columns += ['filename' => $request['filename']];
      $columns += ['created_at' => Carbon::now()];
      $columns += ['updated_at' => Carbon::now()];
      $columns += ['deleted_at' => null];

      try {
        $image_id = $this->get_child_alternatekey('clinic',$request['clinic_id'],'image');
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
      $columns += ['image_id' => $request['image_id']];
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
            ->where('clinic_id','=',$request['clinic_id'])
            ->where('image_id','=',$request['image_id'])
            ->delete();
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function delete_by_clinic_id($clinic_id)
  {
      try {
          $this->_db->table($this->_tableName)
            ->where('clinic_id','=',$clinic_id)
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
          ->where('clinic_id', '=', $id)
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
          ->where('clinic_id', '=', $id)
          ->where('filename', '=', $filename)->exists();
      } catch (\Exception $e) {
        $queryLogs = $this->_db->getQueryLog();
        throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function exists_by_clinic($id)
  {
      try {
        return $this->_db->table($this->_tableName)
          ->where('clinic_id', '=', $id)
          ->exists();
      } catch (\Exception $e) {
        $queryLogs = $this->_db->getQueryLog();
        throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

  public function get_list($clinic_id, $site_pathname)
  {
      try {
          $list = $this->_db->table($this->_tableName)
            ->select('clinic_id', 'image_id', 'filename', 'image_attr')
            ->where('clinic_id','=',$clinic_id)
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
