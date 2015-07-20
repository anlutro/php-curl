<?php
/**
 * PHP OOP cURL
 *
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  PHP cURL
 */

namespace anlutro\cURL;

use Exception;
use RuntimeException;

class cURLException extends RuntimeException
{
	/**
	 * The request that triggered the exception.
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Constructor.
	 *
	 * @param Request|null   $request
	 * @param string         $message
	 * @param integer        $code
	 */
	public function __construct(Request $request, $message = "", $code = 0)
	{
		parent::__construct($message, $code);
		$this->request = $request;
	}

	/**
	 * Get the request that triggered the exception.
	 *
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}
}
