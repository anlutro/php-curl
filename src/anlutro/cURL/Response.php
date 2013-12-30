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
 * cURL response representation class.
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
	 * @param string $body
	 * @param array  $headers
	 * @param mixed  $info
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
	public function getHeader($header = null)
	{
		return $header === null ? $this->headers : $this->headers[$header];
	}

	/**
	 * Convert the response instance to an array.
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
	 * Convert the response object to a JSON string.
	 *
	 * @return string
	 */
	public function toJson()
	{
		return json_encode($this->toArray());
	}

	/**
	 * Convert the object to its string representation by returning the body.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->body;
	}
}
