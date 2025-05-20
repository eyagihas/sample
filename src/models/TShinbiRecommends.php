<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TShinbiRecommends extends Base
{
    use \Modelings\ShinbiRecommends;

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_shinbi_recommends';
    }

    public function exists_preview($id)
    {
        try {
            return $this->_db->table($this->_tableName.'_preview')
                ->where('recommend_id', '=', $id)->exists();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

	public function get_recommend_id($request)
    {
        try {
            $result = $this->_db->table('t_shinbi_recommends as r')
                ->select('r.recommend_id')
                ->leftJoin('m_attributes as a','r.attribute_id','=','a.attribute_id')
                ->where(function ($query) use ($request) {
                    if (!empty($request['city_id'])) {
                        if ($request['city_id'] === 'pref') {
                            $query->where('r.city_id', '=', $request['pref_id']);
                        } else {
                            $query->where('r.city_id', '=', $request['city_id']);
                        }     
                    } 
                    if (!empty($request['station_group_id'])) {
                        $query->where('r.station_group_id', '=', $request['station_group_id']);
                    }
                })
                ->where('a.attribute_flgname', '=', $request['attribute_flgname'])
                ->first();
            return ($result) ? $result->recommend_id : 0;
        } catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_url_by_id($recommend_id)
    {
        try {
            $data = $this->_db->table('t_shinbi_recommends as r')
                ->select(
                    $this->_db->raw('case when c.pref_pathname != "" then c.pref_pathname else cs.pref_pathname end as pref_pathname'),
                    'c.city_pathname',
                    'sg.station_pathname',
                    'a.attribute_pathname'
                )
                ->leftJoin('m_cities as c','r.city_id','=','c.city_id')
                ->leftJoin('m_attributes as a','r.attribute_id','=','a.attribute_id')
                ->leftJoin('m_stations as s','r.station_group_id','=','s.station_group_id')
                ->leftJoin('m_station_groups as sg','r.station_group_id','=','sg.station_group_id')
                ->leftJoin('m_cities as cs','s.city_id','=','cs.city_id')
                ->where('r.recommend_id', '=', $recommend_id)
                ->whereRaw('case when r.station_group_id > 0 then s.is_main = 1 else 1 = 1 end')
                ->first();
            return $this->get_url($data);
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_by_id($id, $isEdited = false, $clinics = null)
    {
        $postfix = ($isEdited) ? '_preview' : '';
        try  {
            $recommend = $this->_db->table($this->_tableName.$postfix.' as r')
                ->select(
                    'r.recommend_id as recommend_id',
                    'r.attribute_id as attribute_id',
                    'r.railway_id as railway_id',
                    'r.station_group_id as station_group_id',
                    'r.title as title',
                    'r.keyword as keyword',
                    'r.description as description',
                    'r.lead_text as lead_text',
                    'r.publish_at as publish_at',
                    'r.updated_at as updated_at',
                    'r.is_published as is_published',
                    'i.image_id as image_id',
                    'i.image_attr as image_attr',
                    'i.filename as filename',
                    $this->_db->raw('case when c.pref_name != "" then c.pref_name else cs.pref_name end as pref_name'),
                    $this->_db->raw('case when c.pref_pathname != "" then c.pref_pathname else cs.pref_pathname end as pref_pathname'),
                    'c.city_pathname as city_pathname',
                    'c.city_name as city_name',
                    'sg.station_pathname as station_pathname',
                    'a.attribute_pathname as attribute_pathname',
                    'a.attribute_name as attribute_name',
                    'a.shinbi_attribute_type as attribute_type',
                    's.station_name as station_name',
                    's.station_simple_name as station_simple_name',
                    $this->_db->raw('group_concat(t.tag_id order by t.sort_order asc) as tag_id')
                    ,
                    $this->_db->raw('group_concat(t.tag_name order by t.sort_order asc) as tag_name')
                    )
                ->leftJoin('t_shinbi_recommend_images as i','r.recommend_id','=','i.recommend_id')
                ->leftJoin('m_cities as c','r.city_id','=','c.city_id')
                ->leftJoin('m_attributes as a','r.attribute_id','=','a.attribute_id')
                ->leftJoin('m_stations as s','r.station_group_id','=','s.station_group_id')
                ->leftJoin('m_station_groups as sg','r.station_group_id','=','sg.station_group_id')
                ->leftJoin('m_cities as cs','s.city_id','=','cs.city_id')
                ->leftJoin('t_shinbi_recommend_tags'.$postfix.' as rt','r.recommend_id','=','rt.recommend_id')
                ->leftJoin('m_tags as t','rt.tag_id','=','t.tag_id')
                ->where('r.recommend_id','=',$id)
                ->whereRaw('case when r.station_group_id > 0 then s.is_main = 1 else 1 = 1 end')
                ->first();
            $this->create_edit_model($recommend, $clinics);
            return $recommend;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_cms_list($request = null,$page = null,$limit = null)
    {
        try {
            $list = $this->_db->table($this->_tableName.' as r')
                ->select(
                    'r.recommend_id',
                    'r.title',
                    'r.publish_at',
                    'r.updated_at',
                    'r.is_published'
                    )
                ->join('m_attributes as a','r.attribute_id','=','a.attribute_id')
                ->where(function ($query) use ($request) {
                    if (isset($request['is_published'])) {
                        $query->where('is_published','=',1)
                              ->where('publish_at','<=',Carbon::now());
                    } 
                    if (isset($request['search_text'])) {
                        $list = explode(' ', str_replace('　', ' ', $request['search_text']));
                        foreach ($list as $text) {
                            $query->where('title', 'like', '%'.$text.'%');
                        }
                    }
                    if (isset($request['recommend_id'])) {
                        $query->where('recommend_id','=',$request['recommend_id']);
                    }
                    if (isset($request['city_id'])) {
                        $query->where('city_id','=',$request['city_id']);
                    }
                    if (isset($request['station_group_id'])) {
                        $query->where('station_group_id','=',$request['station_group_id']);
                    }
                    /* タイプ2記事対応 */
                    if (isset($request['type'])) {
                        $query->where('a.shinbi_attribute_type','=',$request['type']);
                    } else {
                        $query->where('a.shinbi_attribute_type','=',1);
                    }
                })
                ->orderBy('publish_at','desc')
                ->get();
            
            if ( $page !== null && $limit !== null ) {
                $list = collect($list);
                $list = $list->forPage($page,$limit);
            }

            $this->create_cmslist_model($list);
            return $list;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_total_count($request = null)
    {
        try {
            return $this->_db->table($this->_tableName.' as r')
                ->join('m_attributes as a','r.attribute_id','=','a.attribute_id')
                ->where(function ($query) use ($request) {
                    if (isset($request['is_published'])) {
                        $query->where('is_published','=',1)
                              ->where('publish_at','<=',Carbon::now());
                    }
                    if (isset($request['search_text'])) {
                        $list = explode(' ', str_replace('　', ' ', $request['search_text']));
                        foreach ($list as $text) {
                            $query->where('title', 'like', '%'.$text.'%');
                        }
                    }
                    /* タイプ2記事対応 */
                    if (isset($request['type'])) {
                        $query->where('a.shinbi_attribute_type','=',$request['type']);
                    } else {
                        $query->where('a.shinbi_attribute_type','=',1);
                    }
                })
                ->count();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_postnum_by_city($city_id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->where('is_published', '=', 1)
                ->where('city_id', '=', $city_id)
                ->count();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_postnum_by_station($station_group_id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->where('is_published', '=', 1)
                ->where('station_group_id', '=', $station_group_id)
                ->count();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_attribute_id($recommend_id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->select('attribute_id')
                ->where('recommend_id', '=', $recommend_id)
                ->first();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function insert($request)
    {
        $columns = [];
        $columns += ['attribute_id' => $request['attribute_id']];
        $columns += ['city_id' => $request['city_id']];
        $columns += ['station_group_id' => $request['station_group_id']];
        $columns += ['railway_id' => 0];
        $columns += ['station_group_id' => 0];
        $columns += ['title' => $request['title']];
        $columns += ['is_published' => $this->_db->raw(0)];
        $columns += ['publish_at' => Carbon::now()->format('Y-m-d')];
        $columns += ['created_at' => Carbon::now()];
        $columns += ['updated_at' => Carbon::now()->format('Y-m-d')];
        $columns += ['deleted_at' => null];

        $recommend_id = $this->get_alternatekey('recommend');
        $columns += ['recommend_id' => $recommend_id];
        $columns += ['sort_order' => $recommend_id];

        try {
            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName)->insert($columns);
            $this->_db->table($this->_tableName.'_preview')->insert($columns);
            $this->_db->commit();
            return $recommend_id;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function publish($request)
    {
        $columns = ['is_published' => $this->_db->raw(1)];

        try {
            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName)->where('recommend_id','=',$request['recommend_id'])->update($columns);
            $this->_db->table($this->_tableName.'_preview')->where('recommend_id','=',$request['recommend_id'])->update($columns);
            $this->_db->commit();
            return $recommend_id;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
        
    }

    public function unpublish($request)
    {
        $columns = ['is_published' => $this->_db->raw(0)];

        try {
            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName)->where('recommend_id','=',$request['recommend_id'])->update($columns);
            $this->_db->table($this->_tableName.'_preview')->where('recommend_id','=',$request['recommend_id'])->update($columns);
            $this->_db->commit();
            return $recommend_id;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
        
    }

    public function update($request, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';
        try {
            $recommend_id = $request['recommend_id'];

            $columns = [];

            $column_name = [
                'title',
                'keyword',
                'description',
                'lead_text',
                'publish_at',
                'updated_at'
            ];
            if (!$isPreview) $column_name += ['is_published'];

            foreach ( $column_name  as $value ) {
                if ( isset($request[$value]) ) {
                    if ( $request[$value] !== '' ) {
                        if ( in_array($value,['is_published']) ) {
                            $columns += [ $value => $this->_db->raw((int)$request[$value]) ];
                        } else {
                            $columns += [ $value => $request[$value] ];
                        }
                    } else {
                        $columns += [ $value => null ];
                    }
                } else {
                    $columns += [ $value => null ];
                }
            }

            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName.$postfix)->where('recommend_id','=',$recommend_id)->update($columns);
            $this->_db->commit();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

}
