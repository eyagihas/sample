<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TImplantRecommendClinicFeatures extends Base
{
	use \Modelings\ClinicFeatures;

	public function __construct() {
		parent::__construct();
        $this->_tableName = 't_implant_recommend_clinic_features';
	}

  	public function get_by_clinic_id($recommend_id, $clinic_id, $isPreview = false)
  	{
        $postfix = ($isPreview) ? '_preview' : '';

		try {
			$features = $this->_db->table($this->_tableName.$postfix.' as f')
					  ->select(
						  'f.recommend_id as recommend_id',
						  'f.clinic_id as clinic_id',
						  'f.feature_id as feature_id',
						  'f.feature_title as feature_title',
						  'f.feature_text as feature_text',
                          'f.case_id as case_id',
                          'c.case_title as case_title',
                          'c.before_image_id as before_image_id',
                          'c.after_image_id as after_image_id',
                          'c.sort_order as case_sort_order',
                          'c.is_published as case_is_published',
                          'c.publish_at as case_publish_at',
						  'f.sort_order as sort_order'
						  )
            ->leftJoin('t_implant_cases'.$postfix.' as c','f.case_id','=','c.case_id')
            ->where('f.recommend_id','=',$recommend_id)
            ->where('f.clinic_id','=',$clinic_id)
			->orderBy('f.sort_order','asc')
            ->get();
            $this->create_portallist_model($features);
			return $features;
		} catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
    	}
  	}

    public function get_cms_by_clinic_id($recommend_id, $clinic_id, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        try {
            $features = $this->_db->table($this->_tableName.$postfix.' as f')
                ->select(
                    'f.recommend_id as recommend_id',
                    'f.clinic_id as clinic_id',
                    'f.feature_id as feature_id',
                    'f.feature_title as feature_title',
                    'f.feature_text as feature_text',
                    'f.case_id as case_id',
                    'a.attribute_name as attribute_name',
                    'c.case_title as case_title',
                    'f.sort_order as sort_order'
                )
                ->leftJoin('t_implant_cases'.$postfix.' as c','f.case_id','=','c.case_id')
                ->leftJoin('m_attributes as a','c.case_attribute_id','=','a.attribute_id')
                ->where('f.recommend_id','=',$recommend_id)
                ->where('f.clinic_id','=',$clinic_id)
                ->orderBy('f.sort_order','asc')
                ->get();
            return $features;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_recommend_info($case, $site, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';

        try {
            $data = $this->_db->table($this->_tableName.$postfix.' as f')
                      ->select(
                          'r.recommend_id as recommend_id',
                          'r.publish_at as publish_at',
                          $this->_db->raw('case when c.pref_name != "" then c.pref_name else cs.pref_name end as pref_name'),
                          'c.city_name as city_name',
                          's.station_name as station_name'
                          )
            ->join('t_kyousei_recommends'.$postfix.' as r', 'f.recommend_id', '=', 'r.recommend_id')
            ->leftJoin('m_cities as c','r.city_id','=','c.city_id')
            ->leftJoin('m_stations as s','r.station_group_id','=','s.station_group_id')
            ->leftJoin('m_cities as cs','s.city_id','=','cs.city_id')
            ->where('f.clinic_id','=',$case->clinic_id)
            ->where('r.attribute_id','=',$case->case_attribute_id)
            ->where('f.case_id','>',0)
            ->first();
            $this->create_case_detail_model($data, $case, $site);
            return $data;
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
        $columns += ['feature_id' => $request['feature_id']];
        $columns += ['feature_title' => $request['feature_title']];
        $columns += ['feature_text' => $request['feature_text']];
        $columns += ['sort_order' => $request['feature_id']];
        $columns += ['created_at' => Carbon::now()];
        $columns += ['updated_at' => Carbon::now()];
        $columns += ['deleted_at' => null];

        try {
            //$feature_id = $this->get_grandson_alternatekey('recommend',$request['recommend_id'],'clinic',$request['clinic_id'],'feature');
            //$this->delete_by_row($request);
            $this->delete_by_row($request, $isPreview);

            if ( !empty($request['feature_title']) || !empty($request['feature_text']) ) {
                $this->_db->table($this->_tableName.$postfix)->insert($columns);
            }
            return true;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function insert($request, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';
        $request = (is_object($request)) ? (array)$request : $request;

        $columns = [];
        $columns += ['recommend_id' => $request['recommend_id']];
        $columns += ['clinic_id' => $request['clinic_id']];
        $columns += ['feature_id' => $request['feature_id']];
        $columns += ['feature_title' => $request['feature_title']];
        $columns += ['feature_text' => $request['feature_text']];
        if (isset($request['case_id'])) {
            $columns += ['case_id' => $request['case_id']];
        } else {
            $request['case_id'] = 0;
        }
        $columns += ['sort_order' => $request['feature_id']];
        $columns += ['created_at' => Carbon::now()];
        $columns += ['updated_at' => Carbon::now()];
        $columns += ['deleted_at' => null];

        try {
            $this->delete_by_row($request, $isPreview);

            if ( !empty($request['feature_title']) || !empty($request['feature_text']) || $request['case_id'] > 0 ) {
                $this->_db->table($this->_tableName.$postfix)->insert($columns);
            }
            return true;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function delete_by_row($request, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';
        try {
            $this->_db->table($this->_tableName.$postfix)
            ->where('recommend_id','=', $request['recommend_id'])
            ->where('clinic_id', '=', $request['clinic_id'])
            ->where('feature_id', '=', $request['feature_id'])
            ->delete();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function delete_by_group($request, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';
        try {
            $this->_db->table($this->_tableName.$postfix)
            ->where('recommend_id','=', $request['recommend_id'])
            ->where('clinic_id', '=', $request['clinic_id'])
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

}