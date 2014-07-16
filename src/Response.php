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
	 * @deprecated
	 * @see  $statusText, $statusCode
	 */
	public $code;

	/**
	 * The response code including text, e.g. '200 OK'.
	 *
	 * @var string
	 */
	public $statusText;

	/**
	 * The response code.
	 *
	 * @var int
	 */
	public $statusCode;

	/**
	 * @param string $body
	 * @param array  $headers
	 * @param mixed  $info
	 */
	public function __construct($body, $headers, $info = array())
	{
		$this->body = $body;
		$this->headers = $headers;
		$this->info = $info;

		if (isset($this->headers['HTTP/1.1'])) {
			$this->setCode($this->headers['HTTP/1.1']);
		} elseif (isset($this->headers['HTTP/1.0'])) {
			$this->setCode($this->headers['HTTP/1.0']);
		}
	}

	/**
	 * Set the response code.
	 *
	 * @param string $code
	 */
	protected function setCode($code)
	{
		$this->code = $code;
		$this->statusText = $code;
		list($this->statusCode, ) = explode(' ', $code);
	}

	/**
	 * Get a specific header from the response.
	 *
	 * @param  string $key
	 *
	 * @return mixed
	 */
	public function getHeader($key)
	{
		return array_key_exists($key, $this->headers) ? $this->headers[$key] : null;
	}

	/**
	 * Gets all the headers of the response.
	 *
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
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
