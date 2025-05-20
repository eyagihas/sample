<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TProfiles extends Base
{
    use \Modelings\Profiles;

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_profiles';
    }

	public function exists($id)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->where('profile_id', '=', $id)->exists();

        } catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function exists_preview($id)
    {
        try {
            return $this->_db->table($this->_tableName.'_preview')
                ->where('profile_id', '=', $id)->exists();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_by_id($id, $isEdited = false, $site_pathname = null)
    {
        $postfix = ($isEdited) ? '_preview' : '';
        try {
            $profile = $this->_db->table($this->_tableName.$postfix.' as p')
                ->select(
                    'p.profile_id as profile_id',
                    'p.doctor_name as doctor_name',
                    'p.doctor_name_kana as doctor_name_kana',
                    'p.doctor_en_name as doctor_en_name',
                    'p.profile_text as profile_text',
                    'p.license_text as license_text',
                    'p.major_department as major_department',
                    'p.profile_image_id as profile_image_id',
                    'p.is_'.$site_pathname.'_published as is_published',
                    'pi.filename as filename',
                    $this->_db->raw('group_concat(case when sc.clinic_name != "" then sc.clinic_name else c.clinic_name end order by pc.sort_order asc) as clinic_name')
                )
                ->leftJoin('t_profile_images as pi', function ($join) {
                    $join->on('p.profile_id', '=', 'pi.profile_id')->on('p.profile_image_id', '=', 'pi.image_id');
                })
                ->leftJoin('t_profile_clinics as pc', 'p.profile_id', '=', 'pc.profile_id')
                ->leftJoin('t_clinics as c', 'pc.clinic_id', '=', 'c.clinic_id')
                ->leftJoin('t_'.$site_pathname.'_clinics as sc', 'pc.clinic_id', '=', 'sc.clinic_id')
                ->where('p.profile_id', '=', $id)->first();

                $this->create_detail_model($profile);
                return $profile;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_by_pathname($pathname)
    {
        try {
            return $this->_db->table($this->_tableName.' as p')
                ->select(
                    'p.profile_id as profile_id',
                    'p.doctor_name as doctor_name',
                    'p.doctor_name_kana as doctor_name_kana',
                    'p.doctor_en_name as doctor_en_name',
                    'p.profile_image_id as profile_image_id',
                    'p.profile_text as profile_text',
                    'p.license_text as license_text',
                    'p.major_department as major_department'
                )
                ->where('p.doctor_en_name', '=', $pathname)->first();

        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_by_clinic_id($id, $isPreview = false, $site_pathname = 'kyousei')
    {
        $postfix = ($isPreview) ? '_preview' : '';
        try {
            $profile = $this->_db->table($this->_tableName.$postfix.' as p')
                ->select(
                    'p.profile_id as profile_id',
                    'pc.clinic_id as clinic_id',
                    'p.doctor_name as doctor_name',
                    'p.doctor_en_name as doctor_en_name'
                )
                ->join('t_profile_clinics'.$postfix.' as pc', 'p.profile_id', '=', 'pc.profile_id')
                ->where(function ($query) use ($isPreview, $site_pathname) {
                    if (!$isPreview) {
                        $query->where('p.is_'.$site_pathname.'_published','=',1);
                    } 
                })
                ->where('pc.clinic_id', '=', $id)->first();

            $this->create_banner_model($profile);
            return $profile;
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
                    'profile_id',
                    'doctor_name',
                    'is_'.$request['site_pathname'].'_published as is_published'
                    )
                ->where(function ($query) use ($request) {
                    if (isset($request['search_text'])) {
                        $list = explode(' ', str_replace('　', ' ', $request['search_text']));
                        foreach ($list as $text) {
                            $query->where('doctor_name', 'like', '%'.$text.'%');
                        }
                    }
                    if (isset($request['profile_id'])) {
                        $query->where('profile_id','=',$request['profile_id']);
                    }
                })
                ->orderBy('profile_id','asc')
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

    public function get_portal_list($request = null,$page = null,$limit = null)
    {
        try {
            $order = (isset($request['desc'])) ? 'desc' : 'asc';
            $site_pathname = (isset($request['site_pathname'])) ? $request['site_pathname'] : 'implant';

            $list = $this->_db->table($this->_tableName.' as p')
                ->select(
                    'p.profile_id as profile_id',
                    'p.doctor_name as doctor_name',
                    'p.doctor_en_name as doctor_en_name',
                    'p.profile_image_id as profile_image_id',
                    'pi.filename as filename',
                    $this->_db->raw('count(e.is_supervised = 1 or NULL) as supervised_num'),
                    $this->_db->raw('count(e.is_written = 1 or NULL) as written_num')
                    )
                ->leftJoin('t_profile_images as pi', function ($join) {
                    $join->on('p.profile_id', '=', 'pi.profile_id')->on('p.profile_image_id', '=', 'pi.image_id');
                })
                ->leftJoin('t_'.$site_pathname.'_explanations as e', 'p.profile_id', '=', 'e.profile_id')
                ->where('p.is_'.$site_pathname.'_published','=',1)
                ->groupBy('p.profile_id')
                ->orderBy('profile_id',$order)
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
                    if (isset($request['is_kyousei_published'])) {
                        $query->where('is_kyousei_published','=',1);
                    }
                    if (isset($request['is_implant_published'])) {
                        $query->where('is_implant_published','=',1);
                    }
                    if (isset($request['is_shinbi_published'])) {
                        $query->where('is_shinbi_published','=',1);
                    }
                })
                ->count();
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function get_profile_id($doctor_en_name)
    {
        try {
            $data =  $this->_db->table($this->_tableName.' as p')
                ->select(
                    'p.profile_id',
                    'p.doctor_en_name as doctor_en_name'
                    )
                ->where('p.doctor_en_name','=',$doctor_en_name)
                ->first();
            return $data;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function insert($request)
    {
        $columns = [];
        $columns += ['doctor_name' => $request['doctor_name']];
        $columns += ['doctor_name_kana' => $request['doctor_name_kana']];
        $columns += ['doctor_en_name' => $this->mbtrim($request['doctor_en_name'])];
        $columns += ['profile_text' => $request['profile_text']];
        //$columns += ['license_text' => $request['license_text']];
        //$columns += ['major_department' => $request['major_department']];
        $columns += ['created_at' => Carbon::now()];
        $columns += ['updated_at' => Carbon::now()];
        $columns += ['deleted_at' => null];

        $profile_id = $this->get_alternatekey('profile');
        $columns += ['profile_id' => $profile_id];
        $columns += ['sort_order' => $profile_id];

      try {
          $this->_db->beginTransaction();
          $this->_db->table($this->_tableName)->insert($columns);
          $this->_db->table($this->_tableName.'_preview')->insert($columns);
          $this->_db->commit();
          return $profile_id;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }
    }

    public function update($request, $isPreview = false)
    {
        $postfix = ($isPreview) ? '_preview' : '';
        $columns = [
            'doctor_name' => $request['doctor_name'],
            'doctor_name_kana' => $request['doctor_name_kana'],
            'doctor_en_name' => $this->mbtrim($request['doctor_en_name']),
            'profile_text' => $request['profile_text'],
            'profile_image_id' => $request['profile_image_id'],
            //'major_department' => $request['major_department'],
            'updated_at' => Carbon::now()
            ];

        try {
            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName.$postfix)->where('profile_id','=',$request['profile_id'])->update($columns);
            $this->_db->commit();
            return true;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

    public function reset_profile_image_id($request)
    {
        $columns = ['profile_image_id' => null];

      try {
          $this->_db->table($this->_tableName)
            ->where('profile_id','=',$request['profile_id'])
            ->where('profile_image_id', '=', $request['image_id'])
            ->update($columns);
          return true;
      } catch (\Exception $e) {
          $queryLogs = $this->_db->getQueryLog();
          $this->_db->rollback();
          throw new \Exceptions\SqlException($e,$queryLogs);
      }

    }

    public function publish($request)
    {
        $columns = ['is_'.$request['site_pathname'].'_published' => $this->_db->raw(1)];

        try {
            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName)->where('profile_id','=',$request['profile_id'])->update($columns);
            $this->_db->table($this->_tableName.'_preview')->where('profile_id','=',$request['profile_id'])->update($columns);
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
        $columns = ['is_'.$request['site_pathname'].'_published' => $this->_db->raw(0)];

        try {
            $this->_db->beginTransaction();
            $this->_db->table($this->_tableName)->where('profile_id','=',$request['profile_id'])->update($columns);
            $this->_db->table($this->_tableName.'_preview')->where('profile_id','=',$request['profile_id'])->update($columns);
            $this->_db->commit();
            return true;
        } catch (\Exception $e) {
            $queryLogs = $this->_db->getQueryLog();
            $this->_db->rollback();
            throw new \Exceptions\SqlException($e,$queryLogs);
        }
        
    }

}
