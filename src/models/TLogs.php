<?php

namespace Models;

use Carbon\Carbon as Carbon;

class TLogs extends Base
{

    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_logs';
    }

	public function insert($request)
	{
		$log_id = $this->get_alternatekey('log');
		$columns = [];
		$columns += ['log_id' => $log_id];
		$columns += ['account_id' => $request['account_id']];
		$columns += ['site_id' => $request['site_id']];
		$columns += ['recommend_id' => $request['recommend_id']];
		$columns += ['clinic_id' => $request['clinic_id']];
		$columns += ['feature_id' => $request['feature_id']];
		$columns += ['updated_item' => $request['updated_item']];
		$columns += ['sort_order' => $log_id];
		$columns += ['created_at' => Carbon::now()];
		$columns += ['updated_at' => Carbon::now()];
		$columns += ['deleted_at' => null];

		try {
			$this->_db->table($this->_tableName)->insert($columns);
		} catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			$this->_db->rollback();
			throw new \Exceptions\SqlException($e,$queryLogs);
		}
  }

}
