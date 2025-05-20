<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TKyouseiCases extends Base
{
    use \Modelings\Cases;
    
    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_kyousei_cases';
    }

    public function is_never_updated($case_id)
    {
        try {
            return $this->_db->table($this->_tableName.'_preview')
                ->where('case_id', '=', $case_id)
                ->where('updated_at', '=', NULL)
                ->exists();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_page_detail($clinic_id, $attribute_pathname, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        try {
            return $this->_db->table($this->_tableName.$postfix.' as c')
                ->select(
                    'c.case_id as case_id',
                    'c.clinic_id as clinic_id',
                    $this->_db->raw('case when kc.clinic_name != "" then kc.clinic_name else mc.clinic_name end as clinic_name'),
                    'c.case_attribute_id as case_attribute_id',
                    'a.attribute_pathname as attribute_pathname',
                    'a.attribute_name as attribute_name'
                )
                ->join('t_clinics as mc','c.clinic_id', '=', 'mc.clinic_id')
                ->join('t_kyousei_clinics as kc','c.clinic_id', '=', 'kc.clinic_id')
                ->join('m_attributes as a', 'a.attribute_id', '=', 'c.case_attribute_id')
                ->where('c.clinic_id', '=', $clinic_id)
                ->where('a.attribute_pathname', '=', $attribute_pathname)
                ->where(function ($query) use ($isPreview) {
                    if (!$isPreview) {
                        $query->where('c.is_published', '=', 1)
                            ->where('c.publish_at','<=',Carbon::now());
                    }
                })
                ->first();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }   
    }

    public function get_detail($case_id, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        try {
            $data = $this->_db->table($this->_tableName.$postfix.' as c')
                ->select(
                    'c.case_id',
                    'c.clinic_id',
                    $this->_db->raw('case when kc.clinic_name != "" then kc.clinic_name else mc.clinic_name end as clinic_name'),
                    'c.case_attribute_id',
                    'a.attribute_name',
                    'c.case_title as case_title',
                    'c.before_image_id as before_image_id',
                    'c.before_image_attr as before_image_attr',
                    'c.before_image_note as before_image_note',
                    'c.after_image_id as after_image_id',
                    'c.after_image_attr as after_image_attr',
                    'c.after_image_note as after_image_note',
                    'c.case_age as case_age',
                    'c.case_sex as case_sex',
                    'c.case_chief_complaint',
                    'c.case_duration',
                    'c.case_treatment_times',
                    'c.case_consultation_fee',
                    'c.case_diagnostic_fee',
                    'c.case_treatment_fee',
                    'c.case_monthly_fee',
                    'c.case_retainer_fee',
                    'c.case_total_fee',
                    'c.case_description',
                    'c.risk_side_effects',
                    'c.case_doctor_name',
                    'c.case_comment',
                    'c.doctor_id',
                    'c.publish_at',
                    'c.sort_order'
                )
                ->join('t_clinics as mc','c.clinic_id', '=', 'mc.clinic_id')
                ->join('t_kyousei_clinics as kc','c.clinic_id', '=', 'kc.clinic_id')
                ->join('m_attributes as a','c.case_attribute_id', '=', 'a.attribute_id')
                ->where('c.case_id', '=', $case_id)
                ->first();

                $this->create_detail_model($data);
                return $data;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_row($case_id, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        try {
            $data = $this->_db->table($this->_tableName.$postfix)
                ->select(
                    'case_id',
                    'clinic_id',
                    'case_attribute_id',
                    'case_title',
                    'before_image_id',
                    'before_image_attr',
                    'before_image_note',
                    'after_image_id',
                    'after_image_attr',
                    'after_image_note',
                    'case_age',
                    'case_sex',
                    'case_chief_complaint',
                    'case_duration',
                    'case_treatment_times',
                    'case_consultation_fee',
                    'case_diagnostic_fee',
                    'case_treatment_fee',
                    'case_monthly_fee',
                    'case_retainer_fee',
                    'case_total_fee',
                    'case_description',
                    'risk_side_effects',
                    'doctor_id',
                    'case_comment',
                    'case_doctor_name',
                    'publish_at',
                    'sort_order'
                )
                ->where('case_id', '=', $case_id)
                ->first();
                return $data;
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
                    'clinic_id',
                    'case_id',
                    'case_attribute_id',
                    'case_title',
                    'is_published',
                    'sort_order'
                    )
                ->where(function ($query) use ($request) {
                    if (isset($request['clinic_id'])) {
                        $query->where('clinic_id', '=', $request['clinic_id']);
                    }
                })
                ->orderBy('sort_order','asc')
                ->get();
            
            if ( $page !== null && $limit !== null ) {
                $list = collect($list);
                $list = $list->forPage($page,$limit);
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
                    if (isset($request['clinic_id'])) {
                        $query->where('clinic_id', '=', $request['clinic_id']);
                    }
                })
                ->count();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_by_clinic_id($clinic_id, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        try {
            $cases = $this->_db->table($this->_tableName.$postfix.' as c')
                      ->select(
                          'c.clinic_id as clinic_id',
                          'c.case_id as case_id'
                          )
            ->where('c.clinic_id','=',$clinic_id)
            ->orderBy('c.sort_order','asc')
            ->get();
            $this->create_portallist_model($cases);
            return $cases;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_cms_by_clinic_id($clinic_id, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        try {
            $cases = $this->_db->table($this->_tableName.$postfix.' as c')
                      ->select(
                          'c.clinic_id as clinic_id',
                          'c.case_id as case_id',
                          'c.case_attribute_id as case_attribute_id',
                          'a.attribute_name as attribute_name',
                          'c.case_title as case_title',
                          'c.sort_order as sort_order'
                          )
            ->join('m_attributes as a', 'c.case_attribute_id','=','a.attribute_id')
            ->where('c.clinic_id','=',$clinic_id)
            ->orderBy('c.sort_order','asc')
            ->get();
            return $cases;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_list_by_clinic($clinic_id, $case_attribute_id = null, $page = null, $limit = null, $isPreview = false, $isPortal = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        try {
            $list = $this->_db->table($this->_tableName.$postfix.' as c')
                ->select(
                    'c.id as id',
                    'c.clinic_id as clinic_id',
                    'c.case_id as case_id',
                    'c.case_attribute_id as case_attribute_id',
                    'a.attribute_name as attribute_name',
                    'a.attribute_pathname',
                    'c.case_title as case_title',
                    'c.before_image_id as before_image_id',
                    'c.before_image_attr as before_image_attr',
                    'c.before_image_note as before_image_note',
                    'c.after_image_id as after_image_id',
                    'c.after_image_attr as after_image_attr',
                    'c.after_image_note as after_image_note',
                    'c.case_age as case_age',
                    'c.case_sex as case_sex',
                    'c.case_chief_complaint as case_chief_complaint',
                    'c.case_duration as case_duration',
                    'c.case_treatment_times as case_treatment_times',
                    'c.case_consultation_fee as case_consultation_fee',
                    'c.case_diagnostic_fee as case_diagnostic_fee',
                    'c.case_treatment_fee as case_treatment_fee',
                    'c.case_monthly_fee as case_monthly_fee',
                    'c.case_retainer_fee as case_retainer_fee',
                    'c.case_total_fee as case_total_fee',
                    'c.case_description as case_description',
                    'c.risk_side_effects as risk_side_effects',
                    'c.case_doctor_name as case_doctor_name',
                    'c.case_comment as case_comment',
                    'c.doctor_id as doctor_id',
                    'c.publish_at as publish_at',
                    'p.doctor_name as doctor_name',
                    'p.doctor_en_name as doctor_en_name',
                    'p.profile_image_id as profile_image_id',
                    'pi.filename as profile_filename',
                    'p.is_kyousei_published as profile_is_published',
                    'c.sort_order as sort_order',
                    'c.is_published as is_published',
                    'c.publish_at as publish_at'
                )
                ->join('m_attributes as a', 'c.case_attribute_id', '=', 'a.attribute_id')
                ->leftJoin('t_profiles as p', 'c.doctor_id', '=', 'p.profile_id')
                ->leftJoin('t_profile_images as pi', function ($join) {
                    $join->on('p.profile_id', '=', 'pi.profile_id')->on('p.profile_image_id', '=', 'pi.image_id');
                })
                ->where(function ($query) use ($case_attribute_id, $isPortal, $isPreview) {
                    if ($isPortal) {
                        if (!$isPreview) {
                         $query->where('c.is_published', '=',1)
                            ->where('c.publish_at','<=',Carbon::now());
                        }
                    }
                    if (!empty($case_attribute_id)) {
                        $query->where('c.case_attribute_id', '=', $case_attribute_id);
                    }
                })
                ->where('c.clinic_id', '=', $clinic_id)
                ->orderBy('c.case_attribute_id', 'asc')
                ->orderBy('c.case_id', 'asc')
                ->get();

            if ( $page !== null && $limit !== null ) {
                $list = collect($list);
                $list = $list->forPage($page,$limit);
            }

            $this->create_list_model($list);
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

    public function get_pub_upd_at(&$detail, $isPreview = false, $isPortal = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        try {
            $data = $this->_db->table($this->_tableName.$postfix)
                ->select(
                    $this->_db->raw('min(publish_at) as publish_at'),
                    $this->_db->raw('max(publish_at) as updated_at')
                )
                ->where(function ($query) use ($isPortal, $isPreview) {
                    if ($isPortal) {
                        if (!$isPreview) {
                         $query->where('is_published', '=',1)
                            ->where('publish_at','<=',Carbon::now());
                        }
                    }
                })
                ->where('clinic_id', '=', $detail->clinic_id)
                ->where('case_attribute_id', '=', $detail->case_attribute_id)
                ->first();

            $this->create_pub_upd_at_model($detail,$data);
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_cms_for_feature($clinic_id, $attribute_id, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        try {
            $cases = $this->_db->table($this->_tableName.$postfix.' as c')
                      ->select(
                          'c.clinic_id as clinic_id',
                          'c.case_id as case_id',
                          'c.case_attribute_id as case_attribute_id',
                          'a.attribute_name as attribute_name',
                          'c.case_title as case_title',
                          'c.sort_order as sort_order'
                          )
            ->join('m_attributes as a', 'c.case_attribute_id','=','a.attribute_id')
            ->where('c.clinic_id','=',$clinic_id)
            ->where('c.case_attribute_id','=',$attribute_id)
            ->orderBy('c.sort_order','asc')
            ->get();
            return $cases;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_case_orders($clinic_id, $case_attribute_id)
    {
        try {
            $list = $this->_db->table($this->_tableName.'_preview as c')
                ->select('sort_order')
                ->where('clinic_id', '=', $clinic_id)
                ->where('case_attribute_id', '=', $case_attribute_id)
                ->get();
            return $this->create_orderlist_model($list);
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function insert($request, $self_data = null, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';
        $request = (is_object($request)) ? (array)$request : $request;
        $case_id = $this->get_alternatekey('case');

        $columns = [];
        $columns += ['clinic_id' => $request['clinic_id']];
        $columns += ['case_id' => $case_id];
        $columns += ['case_attribute_id' => $request['case_attribute_id']];
        $columns += ['case_title' => $request['case_title']];
        $columns += ['sort_order' => $request['sort_order']];
        $columns += ['created_at' => Carbon::now()];
        $columns += ['updated_at' => null];
        $columns += ['deleted_at' => null];

        if (!empty($self_data)) {
            $columns += ['case_age' => $self_data->case_age];
            $columns += ['case_sex' => $self_data->case_sex];
            $columns += ['case_chief_complaint' => $self_data->case_chief_complaint];
            $columns += ['case_duration' => $self_data->case_duration];
            $columns += ['case_treatment_times' => $self_data->case_treatment_times];
            $columns += ['case_consultation_fee' => $self_data->case_consultation_fee];
            $columns += ['case_diagnostic_fee' => $self_data->case_diagnostic_fee];
            $columns += ['case_treatment_fee' => $self_data->case_treatment_fee];
            $columns += ['case_monthly_fee' => $self_data->case_monthly_fee];
            $columns += ['case_retainer_fee' => $self_data->case_retainer_fee];
            $columns += ['case_total_fee' => $self_data->case_total_fee];
            $columns += ['case_description' => $self_data->case_description];
            $columns += ['risk_side_effects' => $self_data->risk_side_effects];
            $columns += ['case_doctor_name' => $self_data->case_doctor_name];
            $columns += ['case_comment' => $self_data->case_comment];
        }

        try {
            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName.$postfix)->insert($columns);
            $this->_db->commit();

            return $case_id;
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

        $columns = [];
        $columns += ['case_attribute_id' => $request['case_attribute_id']];
        $columns += ['case_title' => $request['case_title']];
        $columns += ['before_image_id' => $request['before_image_id']];
        $columns += ['before_image_attr' => $request['before_image_attr']];
        $columns += ['before_image_note' => $request['before_image_note']];
        $columns += ['after_image_id' => $request['after_image_id']];
        $columns += ['after_image_attr' => $request['after_image_attr']];
        $columns += ['after_image_note' => $request['after_image_note']];
        $columns += ['case_age' => $this->h($request['case_age'])];
        $columns += ['case_sex' => $request['case_sex']];
        $columns += ['case_chief_complaint' => $this->h($request['case_chief_complaint'])];
        $columns += ['case_duration' => $this->h($request['case_duration'])];
        $columns += ['case_treatment_times' => $this->h($request['case_treatment_times'])];
        $columns += ['case_consultation_fee' => $this->h($request['case_consultation_fee'])];
        $columns += ['case_diagnostic_fee' => $this->h($request['case_diagnostic_fee'])];
        $columns += ['case_treatment_fee' => $this->h($request['case_treatment_fee'])];
        $columns += ['case_monthly_fee' => $this->h($request['case_monthly_fee'])];
        $columns += ['case_retainer_fee' => $this->h($request['case_retainer_fee'])];
        $columns += ['case_total_fee' => $this->h($request['case_total_fee'])];
        $columns += ['case_description' => $this->h($request['case_description'])];
        $columns += ['risk_side_effects' => $this->h($request['risk_side_effects'])];
        $columns += ['case_doctor_name' => $this->h($request['case_doctor_name'])];
        $columns += ['case_comment' => $this->h($request['case_comment'])];
        $columns += ['sort_order' => $request['sort_order']];
        $columns += ['updated_at' => Carbon::now()];

        $doctor_id = (!empty($request['doctor_id'])) ? $request['doctor_id'] : 0;
        $columns += ['doctor_id' => $doctor_id];

        $publish_at = (!empty($request['publish_at'])) ? $request['publish_at'] : null;
        $columns += ['publish_at' => $publish_at];


        try {
            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName.$postfix)
                ->where('case_id','=',$request['case_id'])
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
            $case_id = $request['case_id'];
            $sort_order = (!empty($request['sort_order'])) ? $request['sort_order'] : null;

            $columns = [];
            $columns+= ['sort_order' => $sort_order];
            $columns+= ['updated_at' => Carbon::now()];

            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName.$postfix)
                        ->where('case_id','=',$case_id)
                        ->update($columns);
            $this->_db->commit();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function update_image_id($request, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';
        try {

            $columns = [];
            $columns+= [$request['case_type'].'_image_id' => $request['image_id']];
            $columns+= ['updated_at' => Carbon::now()];

            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName.$postfix)
                        ->where('case_id','=',$request['case_id'])
                        ->update($columns);
            $this->_db->commit();

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
            $this->_db->table($this->_tableName)->where('case_id','=',$request['case_id'])->update($columns);
            $this->_db->table($this->_tableName.'_preview')->where('case_id','=',$request['case_id'])->update($columns);
            $this->_db->commit();
            return true;
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
            $this->_db->table($this->_tableName)->where('case_id','=',$request['case_id'])->update($columns);
            $this->_db->table($this->_tableName.'_preview')->where('case_id','=',$request['case_id'])->update($columns);
            $this->_db->commit();
            return true;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
        
    }

}
