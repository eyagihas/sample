<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TRecommends extends Base
{
	use \Modelings\Recommends;

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_implant_recommends';
    }

	public function get_recommend_id($params)
	{
		try {
			$data =  $this->_db->table($this->_tableName.' as r')
                ->select(
					'r.recommend_id',
					'c.pref_name as pref_name',
					'c.city_name as city_name',
					'a.attribute_name as attribute_name',
					'a.attribute_pathname as attribute_pathname',
					'a.implant_attribute_type'
					)
                ->leftJoin('m_cities as c','r.city_id','=','c.city_id')
                ->leftJoin('m_attributes as a','r.attribute_id','=','a.attribute_id')
                ->where('c.pref_pathname','=',$params['pref_pathname'])
                ->where('c.city_pathname','=',$params['city_pathname'])
                ->where('a.attribute_pathname','=',$params['attribute_pathname'])
                ->first();
			return $data;
		} catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
		}
	}

	public function get_recommend_id_by_station($params)
	{
		try {
			$data =  $this->_db->table($this->_tableName.' as r')
                ->select(
					'r.recommend_id',
					'c.pref_name as pref_name',
					's.station_name as station_name',
					'a.attribute_name as attribute_name',
					'a.attribute_pathname as attribute_pathname',
					'a.implant_attribute_type'
					)
                ->leftJoin('m_station_groups as sg', 'sg.station_group_id', '=', 'r.station_group_id')
                ->leftJoin('m_stations as s', 's.station_group_id', '=', 'sg.station_group_id')
                ->leftJoin('m_cities as c','s.city_id','=','c.city_id')
                ->leftJoin('m_attributes as a','r.attribute_id','=','a.attribute_id')
                ->where('c.pref_pathname','=',$params['pref_pathname'])
                ->where('sg.station_pathname','=',$params['station_pathname'])
                ->where('a.attribute_pathname','=',$params['attribute_pathname'])
                ->where('s.is_main', '=', '1')
                ->first();
			return $data;
		} catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
		}
	}

	public function get_by_id($id, $isPreview = false)
	{
		try  {
			$postfix = ($isPreview) ? '_preview' : '';
			$recommend = $this->_db->table($this->_tableName.$postfix.' as r')
				->select(
					'r.recommend_id as recommend_id',
					'r.attribute_id as attribute_id',
					'a.attribute_name as attribute_name',
					'a.implant_attribute_type as implant_attribute_type',
					'r.city_id as city_id',
					$this->_db->raw('case when c.pref_name != "" then c.pref_name else cs.pref_name end as pref_name'),
					'c.city_name as city_name',
					'r.station_group_id as station_group_id',
					's.station_name as station_name',
					$this->_db->raw('case when c.pref_pathname != "" then c.pref_pathname else cs.pref_pathname end as pref_pathname'),
					'c.city_pathname as city_pathname',
					'sg.station_pathname as station_pathname',
					'a.attribute_pathname as attribute_pathname',
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
					'i.filename as filename'
					)
				->leftJoin('m_attributes as a','r.attribute_id','=','a.attribute_id')
				->leftJoin('m_cities as c','r.city_id','=','c.city_id')
				->leftJoin('t_implant_recommend_images as i','r.recommend_id','=','i.recommend_id')
				->leftJoin('m_stations as s','r.station_group_id','=','s.station_group_id')
				->leftJoin('m_station_groups as sg','r.station_group_id','=','sg.station_group_id')
				->leftJoin('m_cities as cs','s.city_id','=','cs.city_id')
				->where('r.recommend_id','=',$id)
				->whereRaw('case when r.station_group_id > 0 then s.is_main = 1 else 1 = 1 end')
				->first();
			$this->create_detail_model($recommend);
			return $recommend;
		} catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
		}
	}

	public function get_portal_list($request = null,$page = null,$limit = null,$type = '')
  	{
		try {
			$list = $this->_db->table($this->_tableName.' as r')
					  ->select(
						  'r.recommend_id as recommend_id',
						  'r.title as title',
						  'r.publish_at as publish_at',
						  'r.updated_at as updated_at',
						  $this->_db->raw('case when c.pref_pathname != "" then c.pref_pathname else cs.pref_pathname end as pref_pathname'),
						  'c.city_pathname as city_pathname',
						  'sg.station_pathname as station_pathname',
						  'a.attribute_pathname as attribute_pathname',
						  'i.filename as filename',
						  'i.image_attr as image_attr'
						  )
				->leftJoin('m_attributes as a','r.attribute_id','=','a.attribute_id')
				->leftJoin('m_cities as c','r.city_id','=','c.city_id')
				->leftJoin('m_station_groups as sg','r.station_group_id','=','sg.station_group_id')
				->leftJoin('m_stations as s','r.station_group_id','=','s.station_group_id')
				->leftJoin('m_cities as cs','s.city_id','=','cs.city_id')
				->leftJoin('t_implant_recommend_images as i','r.recommend_id','=','i.recommend_id')
				->where(function ($query) use ($request, $type) {
					if ($type === 'station') {
						$query->where('r.station_group_id','>',0);
					} elseif ($type === 'area') {
						$query->where('r.city_id','>',0);
					}
				})
				->whereRaw('case when r.station_group_id > 0 then s.is_main = 1 else 1 = 1 end')
				->where('r.is_published','=',1)
				->where('r.publish_at','<=',Carbon::now())
				->orderBy('r.updated_at','desc')->orderBy('r.publish_at','desc')->orderBy('r.recommend_id','desc')
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

	public function get_portal_list_by_pref($request = null,$page = null,$limit = null)
  	{
		try {
			$sub_query = $this->_db->table('m_stations')
				->select('station_group_id', 'prefecture_id', 'city_id')
				->groupBy('station_group_id');

			$list = $this->_db->table($this->_tableName.' as r')
				->select(
					'r.recommend_id as recommend_id',
					'r.title as title',
					'r.publish_at as publish_at',
					'r.updated_at as updated_at',
					$this->_db->raw('case when c.pref_pathname != "" then c.pref_pathname else cs.pref_pathname end as pref_pathname'),
					'c.city_pathname as city_pathname',
					'c.city_name as city_name',
					'sg.station_pathname as station_pathname',
					'a.attribute_pathname as attribute_pathname',
					'i.filename as filename',
					'i.image_attr as image_attr'
					)
				->leftJoin('m_attributes as a','r.attribute_id','=','a.attribute_id')
				->leftJoin('m_cities as c','r.city_id','=','c.city_id')
				->leftJoin('m_station_groups as sg','r.station_group_id','=','sg.station_group_id')
				->leftjoin($this->_db->raw('('.$sub_query->toSql().') as s'),'r.station_group_id','=','s.station_group_id')
				->mergeBindings($sub_query)
				->leftJoin('m_cities as cs','s.city_id','=','cs.city_id')
				->leftJoin('t_implant_recommend_images as i','r.recommend_id','=','i.recommend_id')
				->where('r.is_published','=',1)
				->where('r.publish_at','<=',Carbon::now())
				->where(function ($query) use ($request) {
					if ($request['type'] === 'pref_area') {
						$query->where('c.pref_id','=',$request['pref_id']);
					} elseif ($request['type'] === 'pref_station') {
						$query->where('s.prefecture_id','=',$request['pref_id']);
					}
				})
				->orderBy('r.updated_at','desc')->orderBy('r.publish_at','desc')->orderBy('r.recommend_id','desc')
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

  	public function get_portal_list_by_city($request = null,$page = null,$limit = null)
  	{
		try {
			$list = $this->_db->table($this->_tableName.' as r')
					  ->select(
						  'r.recommend_id as recommend_id',
						  'r.title as title',
						  'r.publish_at as publish_at',
						  'r.updated_at as updated_at',
						  'c.pref_pathname as pref_pathname',
						  'c.city_pathname as city_pathname',
						  'c.city_name as city_name',
						  $this->_db->raw('NULL as station_pathname'),
						  'a.attribute_pathname as attribute_pathname',
						  'i.filename as filename',
						  'i.image_attr as image_attr'
						  )
				->leftJoin('m_attributes as a','r.attribute_id','=','a.attribute_id')
				->leftJoin('m_cities as c','r.city_id','=','c.city_id')
				->leftJoin('t_implant_recommend_images as i','r.recommend_id','=','i.recommend_id')
				->where('r.is_published','=',1)
				->where('r.publish_at','<=',Carbon::now())
				->where('r.city_id', '=', $request['city_id'])
				->orderBy('r.updated_at','desc')->orderBy('r.publish_at','desc')->orderBy('r.recommend_id','desc')
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

  	public function get_portal_list_by_station($request = null,$page = null,$limit = null)
  	{
		try {
			$list = $this->_db->table($this->_tableName.' as r')
					  ->select(
						  'r.recommend_id as recommend_id',
						  'r.title as title',
						  'r.publish_at as publish_at',
						  'r.updated_at as updated_at',
						  'c.pref_pathname as pref_pathname',
						  'sg.station_pathname as station_pathname',
						  'a.attribute_pathname as attribute_pathname',
						  'i.filename as filename',
						  'i.image_attr as image_attr'
						  )
				->leftJoin('m_attributes as a','r.attribute_id','=','a.attribute_id')
				->leftJoin('m_stations as s','r.station_group_id','=','s.station_group_id')
				->leftJoin('m_station_groups as sg','s.station_group_id','=','sg.station_group_id')
				->leftJoin('m_cities as c','s.city_id','=','c.city_id')
				->leftJoin('t_implant_recommend_images as i','r.recommend_id','=','i.recommend_id')
				->where('s.is_main', '=', '1')
				->where('r.is_published','=',1)
				->where('r.publish_at','<=',Carbon::now())
				->where('r.station_group_id', '=', $request['station_group_id'])
				->orderBy('r.updated_at','desc')->orderBy('r.publish_at','desc')->orderBy('r.recommend_id','desc')
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

  	public function get_portal_list_by_clinic($request = null,$page = null,$limit = null)
  	{
		try {
			$list = $this->_db->table($this->_tableName.' as r')
					  ->select(
						  'r.recommend_id as recommend_id',
						  'r.title as title',
						  'r.publish_at as publish_at',
						  'r.updated_at as updated_at',
						  $this->_db->raw('case when c.pref_pathname != "" then c.pref_pathname else cs.pref_pathname end as pref_pathname'),
						  'c.city_pathname as city_pathname',
						  'c.city_name as city_name',
						  'sg.station_pathname as station_pathname',
						  'a.attribute_pathname as attribute_pathname',
						  'i.filename as filename',
						  'i.image_attr as image_attr'
						  )
				->leftJoin('m_attributes as a','r.attribute_id','=','a.attribute_id')
				->leftJoin('m_cities as c','r.city_id','=','c.city_id')
				->leftJoin('m_station_groups as sg','r.station_group_id','=','sg.station_group_id')
				->leftJoin('m_stations as s','r.station_group_id','=','s.station_group_id')
				->leftJoin('m_cities as cs','s.city_id','=','cs.city_id')
				->leftJoin('t_implant_recommend_images as i','r.recommend_id','=','i.recommend_id')
				->leftJoin('t_implant_recommend_clinics as rc','r.recommend_id','=','rc.recommend_id')
				->whereRaw('case when r.station_group_id > 0 then s.is_main = 1 else 1 = 1 end')
				->where('r.is_published','=',1)
				->where('r.publish_at','<=',Carbon::now())
				->whereIn('rc.clinic_id', $request['clinic_id'])
				->where('rc.price_plan', '>', 0)
				->orderByRaw('field(rc.clinic_id,'.$request['order'].') asc')
				->orderBy('r.updated_at','desc')->orderBy('r.publish_at','desc')->orderBy('r.recommend_id','desc')
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

	public function get_total_count($request = null, $type = '')
	{
		try {
			$sub_query = $this->_db->table('m_stations')
					  ->select('station_group_id', 'prefecture_id')
						->groupBy('station_group_id');
						
			return $this->_db->table($this->_tableName.' as r')
				->leftJoin('m_cities as c','r.city_id','=','c.city_id')
				->leftjoin($this->_db->raw('('.$sub_query->toSql().') as s'),'r.station_group_id','=','s.station_group_id')
				->mergeBindings($sub_query)
				->where(function ($query) use ($request, $type) {
					if (isset($request['is_published'])) {
						$query->where('r.is_published','=',1)
						      ->where('r.publish_at','<=',Carbon::now());
					}

					if ($type === 'station') {
						$query->where('r.station_group_id','>',0);
					} elseif ($type === 'area') {
						$query->where('r.city_id','>',0);
					} elseif ($type === 'pref_area') {
						$query->where('c.pref_id','=',$request['pref_id']);
					} elseif ($type === 'pref_station') {
						$query->where('s.prefecture_id','=',$request['pref_id']);
					}
				})
				->count();
		} catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
		}
  	}

  	public function get_active_city_list($city_id)
  	{
  		try {
			$list = $this->_db->table($this->_tableName.' as r')
				->select(
					'pc.city_name as parent_city_name',
					'c.pref_name as pref_name',
					'c.pref_pathname as pref_pathname',
					$this->_db->raw('group_concat(distinct c.city_id order by c.sort_order) as city_id'),
					$this->_db->raw('group_concat(distinct c.city_pathname order by c.sort_order) as city_pathname'),
					$this->_db->raw('group_concat(distinct c.city_name order by c.sort_order) as city_name')
					)
				->leftJoin('m_cities as c', 'r.city_id', '=', 'c.city_id')
				->leftJoin('m_cities as pc', 'c.parent_city_id', '=', 'pc.city_id')
				->where('c.pref_id', '=', $city_id)
				->where('r.is_published','=',1)
				->where('r.publish_at','<=',Carbon::now())
				->groupBy('c.parent_city_id')
				->orderBy('pc.sort_order','asc')
				->get();
			$this->create_portal_citylist_model($list);
			return $list;
		} catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
		}
  	}

  	public function get_active_pref_list()
  	{
		try {
			$list = $this->_db->table($this->_tableName.' as r')
				->select(
					'c.pref_pathname as pref_pathname',
					'c.pref_name as pref_name'
					)
				->leftJoin('m_cities as c', 'r.city_id', '=', 'c.city_id')
				->where('r.is_published','=',1)
				->where('r.publish_at','<=',Carbon::now())
				->where('r.city_id','>',0)
				->groupBy('c.pref_id')
				->orderBy('c.pref_id','asc')
				->get();
			$this->create_portal_preflist_model($list);
			return $list;
		} catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
		}
  	}

   	public function get_top_active_city_list()
  	{
		try {
			$list = $this->_db->table($this->_tableName.' as r')
				->select(
					'c.pref_name as pref_name',
					'c.pref_pathname as pref_pathname',
					'c.city_name as city_name',
					'c.city_pathname as city_pathname',
					$this->_db->raw('max(case when r.updated_at is not null then r.updated_at else r.created_at end) as latest_at')
					)
				->leftJoin('m_cities as c','r.city_id','=','c.city_id')
				->where('r.city_id','>',0)
				->where('r.is_published','=',1)
				->where('r.publish_at','<=',Carbon::now())
				->groupBy('r.city_id')
				->orderBy('latest_at','desc')
            	->get();

            $this->create_portal_top_citylist_model($list);
			return $list;
		} catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
    	}
  	}

  	public function get_active_station_list($city_id)
  	{
  		try {
			$list = $this->_db->table($this->_tableName.' as r')
				->select(
					's.station_name as station_name',
					'c.pref_name as pref_name',
					'c.pref_pathname as pref_pathname',
					'sg.station_pathname as station_pathname'
					)
				->leftJoin('m_stations as s', 'r.station_group_id', '=', 's.station_group_id')
				->leftJoin('m_station_groups as sg', 's.station_group_id', '=', 'sg.station_group_id')
				->leftJoin('m_cities as c', 's.city_id', '=', 'c.city_id')
				->where(function ($query) use ($city_id) {
					if ($city_id > 0) {
						$query->where('s.prefecture_id','=',$city_id);
					}
				})
				->where('s.is_main', '=', 1)
				->where('r.is_published','=',1)
				->where('r.publish_at','<=',Carbon::now())
				->groupBy('s.station_group_id')
				->orderBy('s.sort_order','asc')
				->get();
			$this->create_portal_stationlist_model($list);
			return $list;
		} catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
		}
  	}

  	public function get_active_attribute_list()
  	{
  		try {
			$list = $this->_db->table($this->_tableName.' as r')
				->select(
					'a.attribute_id as attribute_id',
					'a.attribute_name as attribute_name',
					'a.attribute_pathname as attribute_pathname'
					)
				->leftJoin('m_attributes as a', 'r.attribute_id', '=', 'a.attribute_id')
				->where('a.is_implant_attribute', '=', 1)
				->where('r.is_published','=',1)
				->where('r.publish_at','<=',Carbon::now())
				->groupBy('a.attribute_id')
				->orderBy('a.implant_sort_order','asc')
				->get();
			return $list;
		} catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
		}
  	}

  	public function get_pref_search_list()
  	{
		try {
			$active_prefs = $this->_db->table($this->_tableName.' as r')
				->select(
					$this->_db->raw('case when c.pref_id != "" then c.pref_id else cs.pref_id end as pref_id'),
					$this->_db->raw('case when c.pref_name != "" then c.pref_name else cs.pref_name end as pref_name'),
					$this->_db->raw('case when c.pref_pathname != "" then c.pref_pathname else cs.pref_pathname end as pref_pathname')
					)
				->distinct()
				->leftJoin('m_cities as c','r.city_id','=','c.city_id')
				->leftJoin('m_stations as s', 'r.station_group_id', '=', 's.station_group_id')
				->leftJoin('m_cities as cs','s.city_id','=','cs.city_id')
				->whereRaw('case when r.station_group_id > 0 then s.is_main = 1 else 1 = 1 end')
				->whereRaw('r.is_published = 1')
				->whereRaw('r.publish_at <= now()')
				->orderBy('pref_id','asc');

			$list = $this->_db->table('m_region as re')
				->select(
					're.region_id','re.region_name',
					$this->_db->raw('group_concat(sub.pref_name order by sub.pref_id asc) as pref_name'),
					$this->_db->raw('group_concat(sub.pref_pathname order by sub.pref_id asc) as pref_pathname')
					)
				->leftJoin('m_prefectures as p','re.region_id','=','p.region_id')
				->leftJoin($this->_db->raw("({$active_prefs->toSql()}) as sub"),'p.prefecture_id', '=', 'sub.pref_id')
				->groupBy('re.region_id')
				->havingRaw('pref_name is not null')
				->orderBy('re.region_id', 'asc')
            	->get();

            $this->create_search_preflist_model($list);
			return $list;
		} catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
    	}
  	}

  	public function get_area_search_list()
  	{
		try {
			$list = $this->_db->table($this->_tableName.' as r')
				->select(
					'c.pref_id','c.pref_name','c.pref_pathname',
					$this->_db->raw('group_concat(distinct ifnull(c.city_name,"") order by c.sort_order) as city_name'),
					$this->_db->raw('group_concat(distinct c.city_pathname order by c.sort_order) as city_pathname')
					)
				->leftJoin('m_cities as c','r.city_id','=','c.city_id')
				->where('r.city_id','>',0)
				->where('r.is_published','=',1)
				->where('r.publish_at','<=',Carbon::now())
				->groupBy('c.pref_id')
				->orderBy('c.pref_id','asc')
            	->get();

            $this->create_portal_arealist_model($list);
			return $list;
		} catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
    	}
  	}

 	public function get_attribute_search_list($attribute_id)
  	{
		try {
			$list = $this->_db->table($this->_tableName.' as r')
				->select(
					'r.title as title',
					$this->_db->raw('case when c.pref_pathname != "" then c.pref_pathname else cs.pref_pathname end as pref_pathname'),
					'c.city_pathname as city_pathname',
					'sg.station_pathname as station_pathname',
					'a.attribute_pathname as attribute_pathname'
					)
				->leftJoin('m_attributes as a','r.attribute_id','=','a.attribute_id')
				->leftJoin('m_cities as c','r.city_id','=','c.city_id')
				->leftJoin('m_station_groups as sg','r.station_group_id','=','sg.station_group_id')
				->leftJoin('m_stations as s','r.station_group_id','=','s.station_group_id')
				->leftJoin('m_cities as cs','s.city_id','=','cs.city_id')
				->where('r.attribute_id','=',$attribute_id)
				->whereRaw('case when r.station_group_id > 0 then s.is_main = 1 else 1 = 1 end')
				->where('r.is_published','=',1)
				->where('r.publish_at','<=',Carbon::now())
				->orderBy('r.recommend_id','asc')
            	->get();

            $this->create_portal_sitemap_model($list);
			return $list;
		} catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
    	}
  	}

}
