<?php

namespace Models;

use Carbon\Carbon as Carbon;

class MCities extends Base
{
    use \Modelings\Cities;

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 'm_cities';
    }

    public function get_list($request = null)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->select(
                    'city_id',
                    'pref_name',
                    'city_name'
                    )
                ->where(function ($query) use ($request) {
                    if ($request['condition'] === 'perfect') {
                        $query->where('city_name','=',$request['search_text']);
                    }
                    if ($request['condition'] === 'prefix') {
                        $query->where('city_name','like',$request['search_text'].'%');
                    }
                    if ($request['condition'] === 'postfix') {
                        $query->where('city_name','like','%'.$request['search_text']);
                    }
                    if ($request['condition'] === 'partial') {
                        $query->where('city_name','like','%'.$request['search_text'].'%');
                    }
                })
                ->orderBy('sort_order', 'asc')->get();
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
                    'city_id',
                    'pref_name',
                    'city_name'
                    )
                ->where(function ($query) use ($request) {
                    if (isset($request['search_text'])) {
                        $list = explode(' ', str_replace('　', ' ', $request['search_text']));
                        foreach ($list as $text) {
                            $query->where('pref_name', 'like', '%'.$text.'%')
                            ->orWhere('city_name', 'like', '%'.$text.'%');
                        }
                    }
                })
                ->orderBy('sort_order','asc')
                ->get();

            if ( $page !== null && $limit !== null ) {
                $list = collect($list);
                $list = $list->forPage($page,$limit);
            }

            foreach ($list as $row) {
                $row->post_num = \Services\Factory::get_instance($request['site_pathname'].'_recommend')->get_postnum_by_city($row->city_id);
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
                            $query->where('pref_name', 'like', '%'.$text.'%')
                            ->orWhere('city_name', 'like', '%'.$text.'%');
                        }
                    }
                })
                ->count();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_by_id($city_id, $site_pathname)
    {
        try {
            $city = $this->_db->table($this->_tableName)
                ->select(
                    'city_id',
                    'pref_name',
                    'pref_pathname',
                    'city_name',
                    'city_pathname',
                    'kyousei_lead',
                    'implant_lead',
                    'shinbi_lead'
                    )
                ->where('city_id', '=', $city_id)
                ->first();
            $this->create_edit_model($city, $site_pathname);
            return $city;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_by_pathname($request)
    {
        try {
            $city = $this->_db->table($this->_tableName)
                ->select(
                    'city_id',
                    'pref_id',
                    'pref_name',
                    'pref_pathname',
                    'city_name',
                    'city_pathname',
                    'kyousei_lead',
                    'implant_lead',
                    'shinbi_lead',
                    'has_ward'
                    )
                ->where('pref_pathname', '=', $request['pref_pathname'])
                ->where('city_pathname', '=', $request['city_pathname'])
                ->first();
            if (!empty($city)) $this->create_portal_city_model($city);
            return $city;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function update($request)
    {
        $columns = [];

        if (isset($request['kyousei_lead'])) {
            $columns += ['kyousei_lead'=> $request['kyousei_lead']];
        } elseif (isset($request['implant_lead'])) {
            $columns += ['implant_lead'=> $request['implant_lead']];
        } elseif (isset($request['shinbi_lead'])) {
            $columns += ['shinbi_lead'=> $request['shinbi_lead']];
        }

        $columns += ['updated_at' => Carbon::now()];

        try {
            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName)
                ->where('city_id','=',$request['city_id'])
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
