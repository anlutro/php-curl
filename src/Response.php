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
		$this->info = $info;
		if (is_array($headers)) {
			$this->headers = $headers;
		} else {
			$this->headers = static::headerToArray($headers);
		}

		if (isset($this->headers['http/1.1'])) {
			$this->setCode($this->headers['http/1.1']);
		} elseif (isset($this->headers['http/1.0'])) {
			$this->setCode($this->headers['http/1.0']);
		}
	}

	/**
	 * Turn a header string into an array.
	 *
	 * @param  string $header
	 *
	 * @return array
	 */
	protected static function headerToArray($header)
	{
		$headerLines = explode("\r\n", $header);
		$headers = array();

		foreach ($headerLines as $header) {
			$key = null;
			$val = null;
			$delimiter = strpos($header, ': ');

			if ($delimiter !== false) {
				$key = substr($header, 0, $delimiter);
				$val = substr($header, $delimiter + 2);
			} else {
				$delimiter = strpos($header, ' ');
				if ($delimiter !== false) {
					$key = substr($header, 0, $delimiter);
					$val = substr($header, $delimiter + 1);
				}
			}

			if ($key !== null) {
				$key = strtolower($key);
				if (isset($headers[$key])) {
					if (is_array($headers[$key])) {
						$headers[$key][] = $val;
					} else {
						$headers[$key] = array($headers[$key], $val);
					}
				} else {
					$headers[$key] = $val;
				}
			}
		}

		return $headers;
	}

	/**
	 * Set the response code.
	 *
	 * @param string $code
	 */
	protected function setCode($code)
	{
		$this->statusText = $code;
		$code = explode(' ', $code);
		$this->statusCode = (int) $code[0];
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
		$key = strtolower($key);

		return array_key_exists($key, $this->headers) ?
			$this->headers[$key] : null;
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
