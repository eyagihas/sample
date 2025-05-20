<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TRecommendTags extends Base
{
    use \Modelings\RecommendTags;

    public function __construct($sitename = '')
    {
        parent::__construct();
        $this->_tableName = 't_'.$sitename.'_recommend_tags';
        $this->_siteName = $sitename;
    }

    public function get_postnum_by_tag($tag_id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->where('is_deleted', '=', 0)
                ->where('tag_id', '=', $tag_id)
                ->count();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_by_tag_id($tag_id)
    {
        try {
            $list = $this->_db->table($this->_tableName.' as rt')
                ->select(
                    'rt.tag_id',
                    'r.recommend_id',
                    'r.title',
                    'r.updated_at'
                )
                ->leftJoin('t_'.$this->_siteName.'_recommends as r', 'rt.recommend_id', '=', 'r.recommend_id')
                ->where('rt.tag_id', '=', $tag_id)
                ->get();
            $this->create_cmslist_model($list);
            return $list;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function delete_by_tag($tag_id, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';
        try {
            $this->_db->table($this->_tableName.$postfix)
            ->where('tag_id','=',$tag_id)
            ->delete();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

}
