<?php

namespace Models;

class MPayments extends Base
{
  use \Modelings\Payments;

  public function __construct()
  {
      parent::__construct();
      $this->_tableName = 'm_payments';
  }

  public function get_all()
  {
      try {
        $list = $this->_db->table($this->_tableName)
            ->select(
                $this->_db->raw('group_concat(payment_id order by sort_order asc) as payment_id'),
                $this->_db->raw('group_concat(payment_name order by sort_order asc) as payment_name'),
                $this->_db->raw('group_concat(free_text_ex order by sort_order asc) as free_text_ex')
                )
            ->where('is_valid', '=', 1)
            ->groupBy('parent_payment_id')->get();
        $this->create_portal_model($list);
        return $list;
      } catch (\Exception $e) {
		  $queryLogs = $this->_db->getQueryLog();
		  throw new \Exceptions\SqlException($e,$queryLogs);
      }
  }

}
