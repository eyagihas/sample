<?php

namespace Models;

class TAccounts extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->_tableName = 't_accounts';
    }

	public function validate_account($request)
    {
        try {
            return $this->_db->table($this->_tableName)
                ->select(
                    'account_id',
                    'account_name',
					'account_nickname',
					'site_id',
                    'role_id',
                    'clinic_id'
                    )
                ->where('account_name','=',$request['account_name'])
                ->where('password','=',$request['password'])
                ->where('site_id','=',$request['site_id'])
                ->where('is_valid','=',1)
				->first();
        } catch (\Exception $e) {
			$queryLogs = $this->_db->getQueryLog();
			throw new \Exceptions\SqlException($e,$queryLogs);
        }
    }

}
