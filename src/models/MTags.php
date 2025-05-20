<?php

namespace Models;

use Carbon\Carbon as Carbon;

class MTags extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 'm_tags';
    }

    public function get_cms_list($request = null,$page = null,$limit = null)
    {
        try {
            $list = $this->_db->table($this->_tableName)
                ->select(
                    'tag_id',
                    'tag_name'
                    )
                ->where(function ($query) use ($request) {
                    if (isset($request['search_text'])) {
                        $list = explode(' ', str_replace('　', ' ', $request['search_text']));
                        foreach ($list as $text) {
                            $query->where('tag_name', 'like', '%'.$text.'%');
                        }
                    } 
                    if (isset($request['site_pathname'])) {
                        $query->where('is_'.$request['site_pathname'], '=', 1);
                    }
                })
                ->orderBy('sort_order','asc')
                ->get();
            
            if ( $page !== null && $limit !== null ) {
                $list = collect($list);
                $list = $list->forPage($page,$limit);
            }

            foreach ($list as $row) {
                $row->post_num = \Services\Factory::get_instance('recommend_tag', $request['site_pathname'])->get_postnum_by_tag($row->tag_id);
            }

            return $list;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_total_count($request = null)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->where(function ($query) use ($request) {
                    if (isset($request['search_text'])) {
                        $list = explode(' ', str_replace('　', ' ', $request['search_text']));
                        foreach ($list as $text) {
                            $query->where('tag_name', 'like', '%'.$text.'%');
                        }
                    } 
                    if (isset($request['site_pathname'])) {
                        $query->where('is_'.$request['site_pathname'], '=', 1);
                    }
                })
                ->count();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_by_id($tag_id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->select(
                    'tag_id',
                    'tag_name',
                    'is_kyousei',
                    'is_implant',
                    'is_shinbi',
                    'is_valid'
                    )
                ->where('tag_id', '=', $tag_id)
                ->first();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function insert($request)
    {
        $tag_id = $this->get_alternatekey('tag');
        $columns = [];
        $columns += ['tag_id' => $tag_id];
        $columns += ['tag_name' => $request['tag_name']];
        $columns += ['is_'.$request['site_pathname'] => 1];
        $columns += ['sort_order' => $tag_id];
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

    public function delete_by_id($tag_id)
    {
        try {
            $this->_db->table($this->_tableName)
            ->where('tag_id','=',$tag_id)
            ->delete();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function update($request)
    {
      $columns = [
        'tag_name' => $request['tag_name'],
        'updated_at' => Carbon::now()
        ];

      try {
          $this->_db->beginTransaction();
          $this->_db->table($this->_tableName)
            ->where('tag_id','=',$request['tag_id'])
            ->update($columns);
          $this->_db->commit();
          return true;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
    }

}
