<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TKyouseiSelfClinicFeatures extends Base
{
    use \Modelings\SelfClinicFeatures;

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_kyousei_self_clinic_features';
    }


    public function get_by_clinic_feature_id($clinic_id, $feature_id)
    {
        try {
            $data = $this->_db->table($this->_tableName.' as f')
                ->select(
                    //'clinic_id',
                    'f.feature_id',
                    'f.feature_type_id',
                    'mf.feature_type_name',
                    'f.feature_title',
                    'f.feature_text',
                    'f.case_age',
                    'f.case_sex',
                    'f.case_chief_complaint',
                    'f.case_duration',
                    'f.case_treatment_times',
                    'f.case_consultation_fee',
                    'f.case_diagnostic_fee',
                    'f.case_treatment_fee',
                    'f.case_monthly_fee',
                    'f.case_retainer_fee',
                    'f.case_total_fee',
                    'f.case_description',
                    'f.risk_side_effects',
                    'f.case_doctor_name',
                    'f.case_comment',
                    'f.is_with_patient_consent',
                    'f.is_actual_individual_case',
                    'f.can_be_queried',
                    'f.is_not_processed'
                )
                ->join('m_feature_types as mf', 'mf.feature_type_id', '=', 'f.feature_type_id')
                ->where('clinic_id', '=', $clinic_id)
                ->where('feature_id', '=', $feature_id)->first();

            return $data;

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_list_by_clinic($clinic_id, $feature_type_id = 0)
    {
        try {
            $list = $this->_db->table($this->_tableName.' as f')
                ->select(
                    'f.clinic_id',
                    'f.feature_id',
                    'f.feature_title',
                    'f.case_chief_complaint',
                    'f.sort_order',
                    'f.case_id'
                )
                ->join('t_kyousei_self_clinics as c','f.clinic_id','=','c.clinic_id')
                ->where('f.clinic_id', '=', $clinic_id)
                ->where('c.is_draft', '=', 0)
                ->where(function ($query) use ($feature_type_id) {
                    if ($feature_type_id > 0) {
                        $query->where('f.feature_type_id','=',$feature_type_id);
                    }
                })
                ->orderBy('f.sort_order', 'asc')->get();
            $this->create_cmslist_model($list);
            return $list;

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function insert_basic_feature($request)
    {

        $columns = [];
        $columns += ['clinic_id' => $request['clinic_id']];
        $columns += ['feature_id' => $request['feature_id']];
        $columns += ['feature_type_id' => $request['feature_type_id']];
        $columns += ['feature_text' => $this->h($request['feature_text'])];
        $columns += ['sort_order' => $request['feature_id']];
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

    public function insert_case_feature($request)
    {

        $columns = [];
        $columns += ['clinic_id' => $request['clinic_id']];
        $columns += ['feature_id' => $request['feature_id']];
        $columns += ['feature_type_id' => $request['feature_type_id']];
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
        if (isset($request['is_with_patient_consent'])) {
            $columns += ['is_with_patient_consent' => 1];
        }
        if (isset($request['is_actual_individual_case'])) {
            $columns += ['is_actual_individual_case' => 1];
        }
        if (isset($request['can_be_queried'])) {
            $columns += ['can_be_queried' => 1];
        }
        if (isset($request['is_not_processed'])) {
            $columns += ['is_not_processed' => 1];
        }
        $columns += ['sort_order' => $request['feature_id']];
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

    public function delete_by_clinic_feature_id($request)
    {
        try {
            $this->_db->table($this->_tableName)
            ->where('clinic_id','=',$request['clinic_id'])
            ->where('feature_id', '=', $request['feature_id'])
            ->delete();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function update_case_id($request)
    {
        try {
            $columns = [];

            $columns += ['case_id' => $request['case_id']];

            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName)->where('clinic_id','=',$request['clinic_id'])->where('feature_id','=',$request['feature_id'])->update($columns);
            $this->_db->commit();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }
}
