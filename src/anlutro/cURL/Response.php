<?php
/**
 * PHP OOP cURL
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  PHP cURL
 */

namespace anlutro\cURL;

/**
 * cURL wrapper class.
 */
class Response
{
	/**
	 * The response headers.
	 *
	 * @var array
	 */
	public $headers = array();

	/**
	 * The response body.
	 *
	 * @var string
	 */
	public $body;

	/**
	 * The results of curl_getinfo on the response request.
	 *
	 * @var array|false
	 */
	public $info;

	/**
	 * Create a new Eloquent model instance.
	 *
	 * @param  array  $attributes
	 * @return void
	 */
	public function __construct($body, $headers, $info)
	{
		$this->body = $body;
		$this->headers = $headers;
		$this->info = $info;
	}

	/**
	 * Get all or a specific header from the last curl statement.
	 *
	 * @param  string $header Name of the header to get. If not provided, gets
	 * all headers from the last response.
	 *
	 * @return array
	 */
	public function getHeader($key)
	{
		return $this->headers[$key];
	}

	/**
	 * Convert the model instance to an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'headers' => $this->headers,
			'body' => $this->body,
			'info' => $this->info
		);
	}

	/**
	 * Convert the object to its string representation, by returning responseBody as string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->body;
	}
}
