<?php
namespace Exceptions;

/* 500系 */
class ErrorException extends \Exception
{
    /**
     * Create 500 Exception
     *
     * @param \Exception $ex
     * @return void
     */
	public function __construct($ex)
	{
		//500エラー
		\Services\Logger::write('Exception:'.$ex->getMessage());
		parent::__construct('Falied to :'.$ex->getMessage());
	}
}
