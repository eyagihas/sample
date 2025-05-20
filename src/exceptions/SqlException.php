<?php
namespace Exceptions;

/* SQL系 */
class SqlException extends \Exception
{
    /**
     * Create SQL exception instance.
     *
     * @param \Exception $ex
	 * @param querylogs $logs
     * @return void
     */
	public function __construct($ex, $querylog)
	{
		\Services\Logger::write('Exception:'.$ex->getMessage());
		parent::__construct('Falied to SQL:'.dd($querylog));
	}
}
