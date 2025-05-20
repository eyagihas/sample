<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TKyouseiClinics extends Base
{
    use \Modelings\Clinics;
    
    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_kyousei_clinics';
    }

	public function exists($id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->where('clinic_id', '=', $id)->exists();

        } catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function exists_by_tel($tel)
    {
        try {
            return $this->_db->table($this->_tableName.' as kc')
                ->select('c.clinic_id')
                ->join('t_clinics as c', 'kc.clinic_id', '=', 'c.clinic_id')
                ->whereRaw(
                    'case when kc.tel != "" then replace(kc.tel,"-","") = "'.$tel.'" '.
                    'else replace(c.tel,"-","") = "'.$tel.'" end '
                )->first();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_by_id($id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->select(
                    'clinic_id',
                    'clinic_name',
                    'url',
                    'is_url_visible',
                    'url_sp',
                    'is_url_sp_visible',
                    'url_plus',
                    'is_url_plus_visible',
                    'teikei_page_url',
                    'is_url_teikei_visible',
                    'post_code',
                    'address',
                    'city_id',
                    'station_id_list',
                    'access',
                    'gmap_src',
                    'ope_time',
                    'holiday',
                    'holiday_token',
                    'holiday_note',
                    'reserve_url',
                    'is_reserve_url_visible',
                    'reserve_url_plus',
                    'is_reserve_url_plus_visible',
                    'tel',
                    'is_tel_visible',
                    'tel_sp',
                    'is_tel_sp_visible',
                    'reserve_tel',
                    'is_reserve_tel_visible',
                    'is_pr_reserve_tel_visible',
                    'is_pr_reserve_url_visible'
                )
                ->where('clinic_id', '=', $id)->first();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_portal_list($recommend_id, $isPreview = false)
    {
        try {
            $postfix = ($isPreview) ? '_preview' : '';
            $clinics = $this->_db->table($this->_tableName.' as ic')
                    ->select(
                        'c.clinic_id as clinic_id',
                        'c.clinic_name as master_clinic_name',
                        $this->_db->raw('case when ic.clinic_name != "" then ic.clinic_name else c.clinic_name end as clinic_name'),
                        $this->_db->raw('case when ic.gmap_src != "" then ic.gmap_src else c.gmap_src end as gmap_src'),
                        $this->_db->raw('case when ic.access != "" then ic.access else c.access end as access'),
                        $this->_db->raw('case when ic.address != "" then ic.address else c.address end as address'),
                        $this->_db->raw('case when ic.holiday_note != "" then ic.holiday_note else c.holiday_note end as holiday_note'),
                        $this->_db->raw('case when ic.url != "" then ic.url else c.url end as url'),
                        $this->_db->raw('case when ic.url_sp != "" then ic.url_sp else c.url_sp end as url_sp'),
                        $this->_db->raw('case when ic.url_plus != "" then ic.url_plus else c.url_plus end as url_plus'),
                        $this->_db->raw('case when ic.teikei_page_url != "" then ic.teikei_page_url else c.teikei_page_url end as teikei_page_url'),
                        $this->_db->raw('case when ic.reserve_url != "" then ic.reserve_url else c.reserve_url end as reserve_url'),
                        $this->_db->raw('case when ic.reserve_url_plus != "" then ic.reserve_url_plus else c.reserve_url_plus end as reserve_url_plus'),
                        $this->_db->raw('case when ic.tel != "" then ic.tel else c.tel end as tel'),
                        $this->_db->raw('case when ic.tel_sp != "" then ic.tel_sp else c.tel_sp end as tel_sp'),
                        $this->_db->raw('case when ic.reserve_tel != "" then ic.reserve_tel else c.reserve_tel end as reserve_tel'),
                        'ic.is_url_visible as is_url_visible',
                        'ic.is_url_sp_visible as is_url_sp_visible',
                        'ic.is_url_plus_visible as is_url_plus_visible',
                        'ic.is_url_teikei_visible as is_url_teikei_visible',
                        'ic.is_reserve_url_visible as is_reserve_url_visible',
                        'ic.is_reserve_url_plus_visible as is_reserve_url_plus_visible',
                        'ic.is_tel_visible as is_tel_visible',
                        'ic.is_tel_sp_visible as is_tel_sp_visible',
                        'ic.is_reserve_tel_visible as is_reserve_tel_visible',
                        'ic.is_pr_reserve_tel_visible as is_pr_reserve_tel_visible',
                        'ic.is_pr_reserve_url_visible as is_pr_reserve_url_visible',
                        'rc.price_plan as price_plan',
                        'rc.info_text as info_text',
                        'rc.treatment_times as treatment_times',
                        'rc.treatment_duration as treatment_duration',
                        'rc.mv_image_id as mv_image_id',
                        'rc.mv_image_attr as mv_image_attr',
                        'rc.mv_image_note as mv_image_note',
                        'rc.info_image_id as info_image_id',
                        'rc.info_image_attr as info_image_attr',
                        'rc.info_image_note as info_image_note',
                        'rc.feature_image_id as feature_image_id',
                        'rc.feature_image_attr as feature_image_attr',
                        'rc.feature_image_note as feature_image_note',
                        'rc.pr_image_id as pr_image_id',
                        'rc.pr_image_attr as pr_image_attr',
                        'rc.pr_image_note as pr_image_note',
                        'rc.sort_order as sort_order',
                        'a.attribute_name as attribute_name'
                        )
                    ->join('t_clinics as c','ic.clinic_id','=','c.clinic_id')
                    ->join('t_kyousei_recommend_clinics'.$postfix.' as rc','ic.clinic_id','=','rc.clinic_id')
                    ->join('t_kyousei_recommends'.$postfix.' as r', 'rc.recommend_id', '=', 'r.recommend_id')
                    ->join('m_attributes as a', 'r.attribute_id', '=', 'a.attribute_id')
                    ->where('rc.recommend_id','=',$recommend_id)
                    ->whereRaw('rc.is_deleted = 0')
                    ->inRandomOrder()
                    ->get();

            foreach ($clinics as $clinic) {
                $clinic->mv_image = $this->get_image_filename($clinic->clinic_id, $clinic->mv_image_id);
                $clinic->info_image = $this->get_image_filename($clinic->clinic_id, $clinic->info_image_id);
                $clinic->feature_image = $this->get_image_filename($clinic->clinic_id, $clinic->feature_image_id);
                $clinic->pr_image = $this->get_image_filename($clinic->clinic_id, $clinic->pr_image_id);
            }
            $this->create_portallist_model($clinics);
            return $clinics;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_basic_clinic_info($clinic_id, $recommend_id, $isPreview = false)
    {
        try {
            $postfix = ($isPreview) ? '_preview' : '';
            $clinic = $this->_db->table($this->_tableName.' as ic')
                    ->select(
                        'c.clinic_id as clinic_id',
                        'c.clinic_name as master_clinic_name',
                        $this->_db->raw('case when ic.clinic_name != "" then ic.clinic_name else c.clinic_name end as clinic_name'),
                        $this->_db->raw('case when ic.gmap_src != "" then ic.gmap_src else c.gmap_src end as gmap_src'),
                        $this->_db->raw('case when ic.access != "" then ic.access else c.access end as access'),
                        $this->_db->raw('case when ic.address != "" then ic.address else c.address end as address'),
                        $this->_db->raw('case when ic.holiday_note != "" then ic.holiday_note else c.holiday_note end as holiday_note'),
                        $this->_db->raw('case when ic.url != "" then ic.url else c.url end as url'),
                        $this->_db->raw('case when ic.url_sp != "" then ic.url_sp else c.url_sp end as url_sp'),
                        $this->_db->raw('case when ic.url_plus != "" then ic.url_plus else c.url_plus end as url_plus'),
                        $this->_db->raw('case when ic.teikei_page_url != "" then ic.teikei_page_url else c.teikei_page_url end as teikei_page_url'),
                        $this->_db->raw('case when ic.reserve_url != "" then ic.reserve_url else c.reserve_url end as reserve_url'),
                        $this->_db->raw('case when ic.reserve_url_plus != "" then ic.reserve_url_plus else c.reserve_url_plus end as reserve_url_plus'),
                        $this->_db->raw('case when ic.tel != "" then ic.tel else c.tel end as tel'),
                        $this->_db->raw('case when ic.tel_sp != "" then ic.tel_sp else c.tel_sp end as tel_sp'),
                        $this->_db->raw('case when ic.reserve_tel != "" then ic.reserve_tel else c.reserve_tel end as reserve_tel'),
                        'ic.is_url_visible as is_url_visible',
                        'ic.is_url_sp_visible as is_url_sp_visible',
                        'ic.is_url_plus_visible as is_url_plus_visible',
                        'ic.is_url_teikei_visible as is_url_teikei_visible',
                        'ic.is_reserve_url_visible as is_reserve_url_visible',
                        'ic.is_reserve_url_plus_visible as is_reserve_url_plus_visible',
                        'ic.is_tel_visible as is_tel_visible',
                        'ic.is_tel_sp_visible as is_tel_sp_visible',
                        'ic.is_reserve_tel_visible as is_reserve_tel_visible',
                        'ic.is_pr_reserve_tel_visible as is_pr_reserve_tel_visible',
                        'ic.is_pr_reserve_url_visible as is_pr_reserve_url_visible',
                        'rc.mv_image_id as mv_image_id',
                        'rc.mv_image_attr as mv_image_attr',
                        'rc.mv_image_note as mv_image_note',
                        'rc.price_plan as price_plan',
                        'a.attribute_name as attribute_name'
                        )
                    ->join('t_clinics as c','ic.clinic_id','=','c.clinic_id')
                    ->leftJoin('t_kyousei_recommend_clinics'.$postfix.' as rc', function ($join) use ($recommend_id) {
                        $join->on('c.clinic_id', '=', 'rc.clinic_id')->where('rc.recommend_id', '=', $recommend_id);
                    })
                    ->leftJoin('t_kyousei_recommends'.$postfix.' as r', 'r.recommend_id', '=', 'rc.recommend_id')
                    ->leftJoin('m_attributes as a', 'r.attribute_id', '=', 'a.attribute_id')
                    ->where('ic.clinic_id','=',$clinic_id)
                    ->first();

            $clinic->mv_image = $this->get_image_filename($clinic->clinic_id, $clinic->mv_image_id);

            $this->create_basic_info_model($clinic);
            return $clinic;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_image_filename($clinic_id, $image_id)
    {
        try {
            $value = $this->_db->table('t_kyousei_clinic_images')
                    ->select('filename')
                    ->where('clinic_id','=',$clinic_id)
                    ->where('image_id','=',$image_id)
                    ->first();
            return (!empty($value)) ? $value->filename : '';
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_cms_list($request = null,$page = null,$limit = null)
    {
        try {
            $list = $this->_db->table($this->_tableName.' as sc')
                ->select(
                    'sc.clinic_id',
                    $this->_db->raw('case when sc.clinic_name != "" then sc.clinic_name else c.clinic_name end as clinic_name'),
                    'c.attribute_num'
                    )
                ->where(function ($query) use ($request) {
                    if (isset($request['search_text']) && !preg_match("/^[0-9]+$/",$request['search_text'])) {
                        $list = explode(' ', str_replace('　', ' ', $request['search_text']));
                        foreach ($list as $text) {
                            $query->where('c.clinic_name', 'like', '%'.$text.'%')
                                ->orWhere('sc.clinic_name', 'like', '%'.$text.'%');
                        }
                    } elseif (isset($request['search_text']) && preg_match("/^[0-9]+$/",$request['search_text'])) {
                        $query->where('sc.clinic_id', '=', $request['search_text']);
                    }
                })
                ->join('t_clinics as c','sc.clinic_id','=','c.clinic_id')
                ->orderBy('sc.id','desc')
                ->get();
            
            if ( $page !== null && $limit !== null ) {
                $list = collect($list);
                $list = $list->forPage($page,$limit);
            }

            foreach ($list as $row) {
                $row->post_num = \Services\Factory::get_instance('kyousei_recommend_clinic')->get_postnum_by_clinic($row->clinic_id);
                $row->pr_num = \Services\Factory::get_instance('kyousei_recommend_clinic')->get_postnum_by_clinic($row->clinic_id, true);
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
                            $query->where('clinic_name', 'like', '%'.$text.'%');
                        }
                    }
                })
                ->count();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_cms_clinic_name($id)
    {
        try {
            return $this->_db->table($this->_tableName.' as kc')
                ->select(
                    'kc.clinic_id as clinic_id',
                    $this->_db->raw('case when kc.clinic_name != "" then kc.clinic_name else c.clinic_name end as clinic_name')
                )
                ->join('t_clinics as c', 'kc.clinic_id', '=', 'c.clinic_id')
                ->where('kc.clinic_id', '=', $id)->first();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function insert($request)
    {
      $columns = [];
      $columns += ['clinic_id' => $request->clinic_id];
      $columns += ['is_url_visible' => $this->_db->raw(0)];
      $columns += ['is_url_sp_visible' => $this->_db->raw(0)];
      $columns += ['is_url_plus_visible' => $this->_db->raw(0)];
      $columns += ['is_url_teikei_visible' => 1];
      $columns += ['is_reserve_url_visible' => 1];
      $columns += ['is_reserve_url_plus_visible' => $this->_db->raw(0)];
      $columns += ['is_tel_visible' => 1];
      $columns += ['is_tel_sp_visible' => $this->_db->raw(0)];
      $columns += ['is_reserve_tel_visible' => $this->_db->raw(0)];
      $columns += ['is_pr_reserve_tel_visible' => $this->_db->raw(0)];
      $columns += ['is_pr_reserve_url_visible' => $this->_db->raw(0)];
      $columns += ['sort_order' => $request->clinic_id];
      $columns += ['created_at' => Carbon::now()];
      $columns += ['updated_at' => Carbon::now()];
      $columns += ['deleted_at' => null];

      try {
          $this->_db->beginTransaction();
          $this->_db->table($this->_tableName)->insert($columns);
          $this->_db->commit();
          return true;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
    }

    public function update($request)
    {
      $columns = [
        'clinic_name' => $request['clinic_name'],
        'url' => $request['url'],
        'is_url_visible' => $this->_db->raw((int)$request['is_url_visible']),
        'url_sp' => NULL,
        'is_url_sp_visible' => $this->_db->raw(0),
        'url_plus' => $request['url_plus'],
        'is_url_plus_visible' => $this->_db->raw((int)$request['is_url_plus_visible']),
        'teikei_page_url' => $request['teikei_page_url'],
        'is_url_teikei_visible' => $this->_db->raw((int)$request['is_url_teikei_visible']),
        'post_code' => $request['post_code'],
        'address' => $request['address'],
        'city_id' => $request['city_id'],
        'station_id_list' => $request['station_id_list'],
        'access' => $request['access'],
        'gmap_src' => $request['gmap_src'],
        'holiday_note' => $request['holiday_note'],
        'reserve_url' => $request['reserve_url'],
        'is_reserve_url_visible' => $this->_db->raw((int)$request['is_reserve_url_visible']),
        'reserve_url_plus' => $request['reserve_url_plus'],
        'is_reserve_url_plus_visible' => $this->_db->raw((int)$request['is_reserve_url_plus_visible']),
        'tel' => $request['tel'],
        'is_tel_visible' => $this->_db->raw((int)$request['is_tel_visible']),
        'tel_sp' => NULL,
        'is_tel_sp_visible' => $this->_db->raw(0),
        'reserve_tel' => $request['reserve_tel'],
        'is_reserve_tel_visible' => $this->_db->raw((int)$request['is_reserve_tel_visible']),
        'is_pr_reserve_tel_visible' => $this->_db->raw((int)$request['is_pr_reserve_tel_visible']),
        'is_pr_reserve_url_visible' => $this->_db->raw((int)$request['is_pr_reserve_url_visible']),
        'updated_at' => Carbon::now()
        ];

      try {
          $this->_db->beginTransaction();
          $this->_db->table($this->_tableName)->where('clinic_id','=',$request['clinic_id'])->update($columns);
          $this->_db->commit();
          return true;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
    }

    public function update_specific($clinic_id, $request)
    {
      try {
          $this->_db->beginTransaction();
          $this->_db->table($this->_tableName)->where('clinic_id','=',$clinic_id)->update($request);
          $this->_db->commit();
          return true;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
    }

}
