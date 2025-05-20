<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TShinbiExplanations extends Base
{
    use \Modelings\Explanations;

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_shinbi_explanations';
    }

    public function exists_preview($id)
    {
        try {
            return $this->_db->table($this->_tableName.'_preview')
                ->where('explanation_id', '=', $id)->exists();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

	public function get_explanation_id($pathname)
    {
        try {
            $result = $this->_db->table($this->_tableName)
                ->select('explanation_id')
                ->where('pathname', '=', $pathname)
                ->first();
            return ($result) ? $result->explanation_id : 0;
        } catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_url_by_id($explanation_id)
    {
        try {
            $result = $this->_db->table($this->_tableName)
                ->select('pathname')
                ->where('explanation_id', '=', $explanation_id)
                ->first();
            return ($result) ? '/explanation/'.$result->pathname.'/' : '';
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_by_id($id, $isEdited = false)
    {
        $postfix = ($isEdited) ? '_preview' : '';
        try  {
            $explanation = $this->_db->table($this->_tableName.$postfix.' as e')
                ->select(
                    'e.explanation_id as explanation_id',
                    'e.pathname as pathname',
                    'e.title as title',
                    'e.keyword as keyword',
                    'e.description as description',
                    'e.lead_text as lead_text',
                    'e.publish_at as publish_at',
                    'e.updated_at as updated_at',
                    'e.is_published as is_published',
                    'p.doctor_name as doctor_name',
                    'p.doctor_en_name as doctor_en_name',
                    'e.profile_id as profile_id',
                    'e.is_supervised as is_supervised',
                    'e.is_written as is_written'
                )
                ->leftJoin('t_profiles as p','e.profile_id','=','p.profile_id')
                ->where('e.explanation_id','=',$id)->first();
            $this->create_edit_model($explanation);
            return $explanation;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_cms_list($request = null,$page = null,$limit = null)
    {
        try {
            $list = $this->_db->table($this->_tableName)
                ->select(
                    'explanation_id',
                    'title',
                    'publish_at',
                    'updated_at',
                    'is_published'
                    )
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
                    if (isset($request['explanation_id'])) {
                        $query->where('explanation_id','=',$request['explanation_id']);
                    }
                    if (isset($request['profile_id'])) {
                        $query->where('profile_id','=',$request['profile_id']);
                    }
                })
                ->orderBy('publish_at','desc')
                ->get();
            
            if ( $page !== null && $limit !== null ) {
                $list = collect($list);
                $list = $list->forPage($page,$limit);
            }

            //$this->create_cmslist_model($list);
            return $list;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_portal_list($request = null,$page = null,$limit = null)
    {
        try {
            $list = $this->_db->table($this->_tableName.' as e')
                ->select(
                    'e.explanation_id as explanation_id',
                    'e.pathname as pathname',
                    'e.title',
                    'p.doctor_name as doctor_name',
                    'p.doctor_en_name as doctor_en_name',
                    'pi.filename as filename',
                    'e.profile_id as profile_id',
                    'e.is_supervised as is_supervised',
                    'e.is_written as is_written'
                )
                ->leftJoin('t_profiles as p','e.profile_id','=','p.profile_id')
                ->leftJoin('t_profile_images as pi', function ($join) {
                    $join->on('p.profile_id', '=', 'pi.profile_id')->on('p.profile_image_id', '=', 'pi.image_id');
                })
                ->where(function ($query) use ($request) {
                    if (isset($request['profile_id'])) {
                        $query->where('p.profile_id','=',$request['profile_id']);
                    }

                    $query->where('e.is_published','=',1);
                    $query->where('e.publish_at','<=',Carbon::now());
                 })
                ->orderBy('e.publish_at','desc')
                ->get();
            
            if ( $page !== null && $limit !== null ) {
                $list = collect($list);
                $list = $list->forPage($page,$limit);
            }

            $this->create_portallist_model($list);
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
                })
                ->count();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_postnum_by_profile($profile_id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->where('is_published', '=', 1)
                ->where('profile_id', '=', $profile_id)
                ->count();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function insert($request)
    {
        $columns = [];
        $columns += ['pathname' => $request['pathname']];
        $columns += ['title' => $request['title']];
        $columns += ['profile_id' => $request['profile_id']];
        $columns += ['is_supervised' => $this->_db->raw(0)];
        $columns += ['is_written' => $this->_db->raw(0)];
        $columns += ['is_published' => $this->_db->raw(0)];
        $columns += ['publish_at' => Carbon::now()->format('Y-m-d')];
        $columns += ['created_at' => Carbon::now()];
        $columns += ['updated_at' => null];
        $columns += ['deleted_at' => null];

        $explanation_id = $this->get_alternatekey('explanation');
        $columns += ['explanation_id' => $explanation_id];
        $columns += ['sort_order' => $explanation_id];

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
            $this->_db->table($this->_tableName)->where('explanation_id','=',$request['explanation_id'])->update($columns);
            $this->_db->table($this->_tableName.'_preview')->where('explanation_id','=',$request['explanation_id'])->update($columns);
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
            $this->_db->table($this->_tableName)->where('explanation_id','=',$request['explanation_id'])->update($columns);
            $this->_db->table($this->_tableName.'_preview')->where('explanation_id','=',$request['explanation_id'])->update($columns);
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
            $explanation_id = $request['explanation_id'];

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
            $this->_db->table($this->_tableName.$postfix)->where('explanation_id','=',$explanation_id)->update($columns);
            $this->_db->commit();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

}
