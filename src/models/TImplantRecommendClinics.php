<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TImplantRecommendClinics extends Base
{
    use \Modelings\ImplantRecommendClinics;

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_implant_recommend_clinics';
    }

    public function exists($recommend_id, $clinic_id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->where('recommend_id', '=', $recommend_id)
                ->where('clinic_id', '=', $clinic_id)
                ->exists();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function exists_pr($recommend_id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->where('recommend_id', '=', $recommend_id)
                ->where('price_plan', '>', 0)
                ->exists();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function is_never_updated($recommend_id, $clinic_id)
    {
        try {
            return $this->_db->table($this->_tableName.'_preview')
                ->where('recommend_id', '=', $recommend_id)
                ->where('clinic_id', '=', $clinic_id)
                ->where('updated_at', '=', NULL)
                ->exists();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_list($recommend_id, $isPreview = false, $isCms = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        try {
            $sub_query = $this->_db->table('t_implant_recommend_clinic_features'.$postfix)
                ->select(
                    'recommend_id',
                    'clinic_id',
                    $this->_db->raw('group_concat(feature_title order by sort_order asc) as feature_title'),
                    $this->_db->raw('group_concat(feature_text order by sort_order asc) as feature_text'),
                    $this->_db->raw('group_concat(case_id order by sort_order asc) as case_id')
                    )
                ->where('recommend_id','=',$recommend_id)
                ->groupBy('clinic_id');
            return $this->_db->table($this->_tableName.$postfix.' as rc')
                ->select(
                    'c.clinic_id as clinic_id',
                    $this->_db->raw('case when kc.clinic_name != "" then kc.clinic_name else c.clinic_name end as clinic_name'),
                    'c.attribute_num as specifiedFlgNums',
                    'rc.price_plan as price_plan',
                    'rc.contract_start_on as contract_start_on',
                    'rc.contract_end_on as contract_end_on',
                    'rc.mv_image_id as mv_image_id',
                    'rc.mv_image_attr as mv_image_attr',
                    'rc.mv_image_note as mv_image_note',
                    'rc.info_image_id as info_image_id',
                    'rc.info_image_attr as info_image_attr',
                    'rc.info_image_note as info_image_note',
                    'rc.info_text as info_text',
                    'rc.treatment_times as treatment_times',
                    'rc.treatment_duration as treatment_duration',
                    'rc.feature_image_id as feature_image_id',
                    'rc.feature_image_attr as feature_image_attr',
                    'rc.feature_image_note as feature_image_note',
                    'rc.pr_image_id as pr_image_id',
                    'rc.pr_image_attr as pr_image_attr',
                    'rc.pr_image_note as pr_image_note',
                    'rc.is_deleted as is_deleted',
                    'f.feature_title as feature_title',
                    'f.feature_text as feature_text',
                    'f.case_id as case_id',
                    'rc.is_deleted as is_deleted',
                    'rc.sort_order as sort_order',
                    $this->_db->raw('1 as "exists"'),
                    $this->_db->raw('0 as "is_edited"')
                    )
                ->join('t_clinics as c', 'rc.clinic_id', '=', 'c.clinic_id')
                ->join('t_implant_clinics as kc', 'rc.clinic_id', '=', 'kc.clinic_id')
                ->leftjoin($this->_db->raw('('.$sub_query->toSql().') as f'),'rc.clinic_id','=','f.clinic_id')
                ->mergeBindings($sub_query)
                ->where(function ($query) use ($isCms) {
                    if (!$isCms) {
                        $query->where('is_deleted','=',0);
                    }
                })
                ->where('rc.recommend_id', '=', $recommend_id)
                ->orderByRaw('rc.sort_order is null asc')->orderBy('rc.sort_order','asc')->orderBy('clinic_id', 'asc')
                ->get();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_recommend_clinic_orders($recommend_id, $isCms = false)
    {
        try {
            $list = $this->_db->table($this->_tableName.'_preview as rc')
                ->select('sort_order')
                ->where('recommend_id', '=', $recommend_id)
                ->where(function ($query) use ($isCms) {
                    if (!$isCms) {
                        $query->where('is_deleted','=',0);
                    }
                })
                ->get();
            return $this->create_list_model($list);
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_postnum_by_clinic($clinic_id, $is_pr = false)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->where('is_deleted', '=', 0)
                ->where('clinic_id', '=', $clinic_id)
                ->where(function ($query) use ($is_pr) {
                    if ($is_pr) {
                        $query->where('price_plan','>',0);
                    }
                })
                ->count();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_list_by_clinic($clinic_id, $page = null, $limit = null, $recommend_id = 0, $is_pr = false)
    {
        try {
            $list = $this->_db->table($this->_tableName.' as rc')
                ->select(
                    'rc.recommend_id as recommend_id',
                    'rc.price_plan as price_plan',
                    'r.title as title',
                    'r.publish_at as publish_at',
                    'r.updated_at as updated_at',
                    'a.implant_attribute_type as attribute_type'
                )
                ->join('t_implant_recommends as r', 'rc.recommend_id', '=', 'r.recommend_id')
                ->join('m_attributes as a', 'r.attribute_id', '=', 'a.attribute_id')
                ->where('rc.clinic_id', '=', $clinic_id)
                ->where(function ($query) use ($recommend_id, $is_pr) {
                    if ($recommend_id > 0) {
                        $query->where('r.recommend_id','=',$recommend_id);
                    }
                    if ($is_pr) {
                        $query->where('rc.price_plan','=',1);
                    }
                })
                ->orderBy('r.publish_at', 'desc')
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

    public function get_total_by_clinic($clinic_id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->where('clinic_id', '=', $clinic_id)
                ->count();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_detail($recommend_id, $clinic_id, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        try {
            $data = $this->_db->table($this->_tableName.$postfix.' as rc')
                ->select(
                    'rc.recommend_id',
                    'rc.clinic_id',
                    'r.title',
                    'r.updated_at as recommend_updated_at',
                    'rc.price_plan',
                    'rc.contract_start_on',
                    'rc.contract_end_on',
                    'rc.updated_at',
                    'rc.mv_image_id',
                    'rc.mv_image_attr',
                    'rc.mv_image_note',
                    'rc.info_image_id',
                    'rc.info_image_attr',
                    'rc.info_image_note',
                    'rc.info_text',
                    'rc.treatment_times',
                    'rc.treatment_duration',
                    'rc.feature_image_id',
                    'rc.feature_image_attr',
                    'rc.feature_image_note',
                    'rc.pr_image_id',
                    'rc.pr_image_attr',
                    'rc.pr_image_note',
                    $this->_db->raw('case when ic.clinic_name != "" then ic.clinic_name else c.clinic_name end as clinic_name'),
                    'rc.is_deleted',
                    'rc.sort_order',
                    'a.attribute_name'
                )
                ->join('t_implant_recommends as r','rc.recommend_id', '=', 'r.recommend_id')
                ->join('t_clinics as c','rc.clinic_id', '=', 'c.clinic_id')
                ->join('t_implant_clinics as ic','rc.clinic_id', '=', 'ic.clinic_id')
                ->join('m_attributes as a','r.attribute_id', '=', 'a.attribute_id')
                ->where('rc.recommend_id', '=', $recommend_id)
                ->where('rc.clinic_id', '=', $clinic_id)
                ->first();

                $this->create_detail_model($data);
                return $data;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_row($recommend_id, $clinic_id, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        try {
            return $this->_db->table($this->_tableName.$postfix.' as rc')
                ->select(
                    'recommend_id',
                    'clinic_id',
                    'price_plan',
                    'contract_start_on',
                    'contract_end_on',
                    'mv_image_id',
                    'mv_image_attr',
                    'mv_image_note',
                    'info_image_id',
                    'info_image_attr',
                    'info_image_note',
                    'info_text',
                    'treatment_times',
                    'treatment_duration',
                    'feature_image_id',
                    'feature_image_attr',
                    'feature_image_note',
                    'pr_image_id',
                    'pr_image_attr',
                    'pr_image_note',
                    'is_deleted',
                    'sort_order'
                )
                ->where('recommend_id', '=', $recommend_id)
                ->where('clinic_id', '=', $clinic_id)
                ->first();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function insert_for_import($request, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        $columns = [];
        $columns += ['recommend_id' => $request['recommend_id']];
        $columns += ['clinic_id' => $request['clinic_id']];
        $price_plan = (!empty($request['price_plan'])) ? $request['price_plan'] : 0;
        $columns += ['price_plan' => $this->_db->raw($price_plan)];
        $columns += ['mv_image_id' => $request['mv_image_id']];
        $columns += ['mv_image_note' => $request['mv_image_note']];
        $columns += ['info_image_id' => $request['info_image_id']];
        $columns += ['info_image_note' => $request['info_image_note']];
        $columns += ['info_text' => $request['info_text']];
        $columns += ['feature_image_id' => $request['feature_image_id']];
        $columns += ['feature_image_note' => $request['feature_image_note']];
        if (!empty($request['pr_image_id'])) {
            $columns += ['pr_image_id' => $request['pr_image_id']];
            $columns += ['pr_image_note' => $request['pr_image_note']];
        }
        $sort_order = (!empty($request['sort_order'])) ? $request['sort_order'] : null;
        $columns += ['sort_order' => $sort_order];
        $columns += ['is_deleted' => $this->_db->raw(0)];
        $columns += ['created_at' => Carbon::now()];
        $columns += ['updated_at' => null];
        $columns += ['deleted_at' => null];

        try {
            //$this->delete_by_row($request['recommend_id'], $request['clinic_id']);
            $this->delete_by_row($request['recommend_id'], $request['clinic_id'], $isPreview);
            $this->_db->table($this->_tableName.$postfix)->insert($columns);
            return true;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function insert($request, $isPreview = false)
    {
        $columns = [];
        $columns += ['recommend_id' => $request['recommend_id']];
        $columns += ['clinic_id' => $request['clinic_id']];
        $columns += ['price_plan' => 0];
        $columns += ['is_deleted' => $this->_db->raw(0)];
        $columns += ['created_at' => Carbon::now()];
        $columns += ['updated_at' => null];
        $columns += ['deleted_at' => null];

        try {
            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName.'_preview')->insert($columns);
            if ($isPreview) $columns['is_deleted'] = 1;
            $this->_db->table($this->_tableName)->insert($columns);
            $this->_db->commit();
            return true;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function update($request, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        $request = (is_object($request)) ? (array)$request : $request;

        foreach ($request as $key => $value) {
            if ( empty($value) ) $request[$key] = null;
        }

        /*
        $contract_start_on = (!empty($request['contract_start_on'])) ? $request['contract_start_on'] :  null;
        $contract_end_on = (!empty($request['contract_end_on'])) ? $request['contract_end_on'] :  null;
        */

        $columns = [
            'price_plan' => $request['price_plan'],
            'contract_start_on' => $request['contract_start_on'],
            'contract_end_on' => $request['contract_end_on'],
            'mv_image_id' => $request['mv_image_id'],
            'mv_image_attr' => $request['mv_image_attr'],
            'mv_image_note' => $request['mv_image_note'],
            'info_image_id' => $request['info_image_id'],
            'info_image_attr' => $request['info_image_attr'],
            'info_image_note' => $request['info_image_note'],
            'info_text' => $request['info_text'],
            'feature_image_id' => $request['feature_image_id'],
            'feature_image_attr' => $request['feature_image_attr'],
            'feature_image_note' => $request['feature_image_note'],
            'is_deleted' => $this->_db->raw((int)$request['is_deleted']),
            'sort_order' => $request['sort_order'],
            'updated_at' => Carbon::now()
        ];

        if (!empty($request['treatment_times'])) $columns += ['treatment_times' => $request['treatment_times']];
        if (!empty($request['treatment_duration'])) $columns += ['treatment_duration' => $request['treatment_duration']];

        if (!empty($request['pr_image_id'])) {
            $columns += ['pr_image_id' => $request['pr_image_id']];
            //$columns += ['pr_image_note' => $request['pr_image_note']];
        }

        try {
            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName.$postfix)
                ->where('recommend_id','=',$request['recommend_id'])
                ->where('clinic_id','=',$request['clinic_id'])
                ->update($columns);
            $this->_db->commit();
            return true;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function update_order($request, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';
        try {
            $recommend_id = $request['recommend_id'];
            $clinic_id = $request['clinic_id'];
            $sort_order = (!empty($request['sort_order'])) ? $request['sort_order'] : null;

            $columns = [];
            $columns+= ['sort_order' => $sort_order];
            $columns+= ['updated_at' => Carbon::now()];

            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName.$postfix)
                        ->where('recommend_id','=',$recommend_id)
                        ->where('clinic_id', '=', $clinic_id)
                        ->update($columns);
            $this->_db->commit();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function delete_by_row($recommend_id, $clinic_id, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';
        try {
            $this->_db->table($this->_tableName.$postfix)
            ->where('recommend_id','=',$recommend_id)
            ->where('clinic_id','=',$clinic_id)
            ->delete();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

}
