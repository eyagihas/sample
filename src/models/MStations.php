<?php

namespace Models;

use Carbon\Carbon as Carbon;

class MStations extends Base
{
    use \Modelings\Stations;

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 'm_stations';
    }

    public function get_cms_group_list($request = null,$page = null,$limit = null)
    {
        try {
            $list = $this->_db->table($this->_tableName.' as s')
                ->select(
                    's.station_group_id',
                    's.station_name',
                    's.station_simple_name',
                    'c.pref_name',
                    'c.city_name'
                    )
                ->join('m_cities as c','s.city_id','=','c.city_id')
                ->where(function ($query) use ($request) {
                    if (isset($request['search_text']) && !preg_match("/^[0-9]+$/",$request['search_text'])) {
                        $list = explode(' ', str_replace('　', ' ', $request['search_text']));
                        foreach ($list as $text) {
                            $query->where('s.station_name', 'like', '%'.$text.'%')
                                ->orWhere('s.station_simple_name', 'like', '%'.$text.'%');
                        }
                    } elseif (isset($request['search_text']) && preg_match("/^[0-9]+$/",$request['search_text'])) {
                        $query->where('s.station_group_id', '=', $request['search_text']);
                    }
                })
                ->where('s.is_main', '=', 1)
                ->orderBy('s.city_id','asc')
                ->get();
            
            if ( $page !== null && $limit !== null ) {
                $list = collect($list);
                $list = $list->forPage($page,$limit);
            }

            if (isset($request['site_pathname'])) {
                foreach ($list as $row) {
                    $row->post_num = \Services\Factory::get_instance($request['site_pathname'].'_recommend')->get_postnum_by_station($row->station_group_id);
                }             
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
                    if (isset($request['search_text']) && !preg_match("/^[0-9]+$/",$request['search_text'])) {
                        $list = explode(' ', str_replace('　', ' ', $request['search_text']));
                        foreach ($list as $text) {
                            $query->where('station_name', 'like', '%'.$text.'%')
                                ->orWhere('station_simple_name', 'like', '%'.$text.'%');
                        }
                    } elseif (isset($request['search_text']) && preg_match("/^[0-9]+$/",$request['search_text'])) {
                        $query->where('station_group_id', '=', $request['search_text']);
                    }
                })
                ->where('is_main', '=', 1)
                ->count();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_by_group_id($station_group_id, $site_pathname)
    {
        try {
            $station_group = $this->_db->table($this->_tableName.' as s')
                ->select(
                    's.station_group_id',
                    's.station_name',
                    's.station_simple_name',
                    'sg.station_pathname',
                    'sg.kyousei_lead',
                    'sg.implant_lead',
                    'sg.shinbi_lead',
                    'c.pref_name',
                    'c.pref_pathname',
                    'c.city_name'
                    )
                ->leftJoin('m_station_groups as sg', 's.station_group_id', '=', 'sg.station_group_id')
                ->leftJoin('m_cities as c', 's.city_id', '=', 'c.city_id')
                ->where('s.station_group_id', '=', $station_group_id)
                ->where('s.is_main', '=', 1)
                ->first();
            $this->create_edit_model($station_group, $site_pathname);
            return $station_group;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_by_pathname($request)
    {
        try {
            $station = $this->_db->table($this->_tableName.' as s')
                ->select(
                    'sg.station_group_id',
                    'c.pref_name',
                    'c.pref_pathname',
                    's.station_name',
                    's.station_simple_name',
                    'sg.station_pathname',
                    'sg.kyousei_lead',
                    'sg.implant_lead',
                    'sg.shinbi_lead'
                    )
                ->leftJoin('m_station_groups as sg','s.station_group_id','=','sg.station_group_id')
                ->leftJoin('m_cities as c','s.city_id','=','c.city_id')
                ->where('s.is_main', '=', 1)
                ->where('c.pref_pathname', '=', $request['pref_pathname'])
                ->where('sg.station_pathname', '=', $request['station_pathname'])
                ->first();
            if (!empty($station)) $this->create_portal_station_model($station);
            return $station;
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
            $this->_db->table('m_station_groups')
                ->where('station_group_id','=',$request['station_group_id'])
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
