<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TSiteClinics extends Base
{
    public function __construct($sitename = '')
    {
        parent::__construct();
        $this->_siteName = $sitename;
        $this->_tableName = 't_'.$sitename.'_clinics';
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

    public function insert($row, $images, $opeTimes)
    {
        $columns = [];
        $columns += ['clinic_id' => $row->clinic_id];
        $columns += ['clinic_name' => $row->clinic_name];
        $columns += ['is_url_visible' => $this->_db->raw(0)];
        $columns += ['is_url_sp_visible' => $this->_db->raw(0)];
        $columns += ['address' => $row->address];
        $columns += ['access' => $row->access];
        $columns += ['gmap_src' => $row->gmap_src];
        $columns += ['holiday_note' => $row->holiday_note];
        $columns += ['is_tel_sp_visible' => $this->_db->raw(0)];
        $columns += ['sort_order' => $row->clinic_id];
        $columns += ['created_at' => Carbon::now()];
        $columns += ['updated_at' => Carbon::now()];
        $columns += ['deleted_at' => null];

        if (!empty($row->url_plus)) {
            $columns += ['url_plus' => $row->url_plus];
            $columns += ['is_url_plus_visible' => 1];
            $columns += ['is_url_teikei_visible' => $this->_db->raw(0)];
        } else {
            $columns += ['is_url_plus_visible' => $this->_db->raw(0)];
            $columns += ['is_url_teikei_visible' => 1];
        }

        if (!empty($row->reserve_url_plus)) {
            $columns += ['reserve_url_plus' => $row->reserve_url_plus];
            $columns += ['is_reserve_url_plus_visible' => 1];
            $columns += ['is_reserve_url_visible' => $this->_db->raw(0)];
        } else {
            $columns += ['is_reserve_url_plus_visible' => $this->_db->raw(0)];
            $columns += ['is_reserve_url_visible' => 1];
        }

        if (!empty($row->reserve_tel)) {
            $columns += ['reserve_tel' => $row->reserve_tel];
            $columns += ['is_reserve_tel_visible' => 1];
            $columns += ['is_tel_visible' => $this->_db->raw(0)];
        } else {
            $columns += ['is_reserve_tel_visible' => $this->_db->raw(0)];
            $columns += ['is_tel_visible' => 1];
        }

        try {
            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName)->insert($columns);

            foreach ($images as $image) {
                if (!\Services\Factory::get_instance('clinic_image', $this->_siteName)->exists($image['clinic_id'], $image['image_id'])) {
                    \Services\Factory::get_instance('clinic_image', $this->_siteName)->insert($image);
                }
            }

            foreach ($opeTimes as $opeTime) {
                \Services\Factory::get_instance($this->_siteName.'_operation_time')->insert($opeTime);
            }

            $this->_db->commit();
            return $row->clinic_id;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function update($row, $images, $opeTimes)
    {
        $columns = [];
        $columns += ['clinic_name' => $row->clinic_name];
        $columns += ['is_url_visible' => $this->_db->raw(0)];
        $columns += ['is_url_sp_visible' => $this->_db->raw(0)];
        $columns += ['address' => $row->address];
        $columns += ['access' => $row->access];
        $columns += ['gmap_src' => $row->gmap_src];
        $columns += ['holiday_note' => $row->holiday_note];
        $columns += ['is_tel_sp_visible' => $this->_db->raw(0)];
        $columns += ['updated_at' => Carbon::now()];
        $columns += ['deleted_at' => null];

        if (!empty($row->url_plus)) {
            $columns += ['url_plus' => $row->url_plus];
            $columns += ['is_url_plus_visible' => 1];
            $columns += ['is_url_teikei_visible' => $this->_db->raw(0)];
        } else {
            $columns += ['is_url_plus_visible' => $this->_db->raw(0)];
            $columns += ['is_url_teikei_visible' => 1];
        }

        if (!empty($row->reserve_url_plus)) {
            $columns += ['reserve_url_plus' => $row->reserve_url_plus];
            $columns += ['is_reserve_url_plus_visible' => 1];
            $columns += ['is_reserve_url_visible' => $this->_db->raw(0)];
        } else {
            $columns += ['is_reserve_url_plus_visible' => $this->_db->raw(0)];
            $columns += ['is_reserve_url_visible' => 1];
        }

        if (!empty($row->reserve_tel)) {
            $columns += ['reserve_tel' => $row->reserve_tel];
            $columns += ['is_reserve_tel_visible' => 1];
            $columns += ['is_tel_visible' => $this->_db->raw(0)];
        } else {
            $columns += ['is_reserve_tel_visible' => $this->_db->raw(0)];
            $columns += ['is_tel_visible' => 1];
        }

        try {
            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName)->where('clinic_id','=',$row->clinic_id)->update($columns);

            foreach ($images as $image) {
                if (!\Services\Factory::get_instance('clinic_image', $this->_siteName)->exists($image['clinic_id'], $image['image_id'])) {
                    \Services\Factory::get_instance('clinic_image', $this->_siteName)->insert($image);
                }
            }

            \Services\Factory::get_instance($this->_siteName.'_operation_time')->delete_by_clinic_id($row->clinic_id);
            foreach ($opeTimes as $opeTime) {
                \Services\Factory::get_instance($this->_siteName.'_operation_time')->insert($opeTime);
            }

            $this->_db->commit();
            return $row->clinic_id;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

}
