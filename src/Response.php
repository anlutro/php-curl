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
	 * @param string $headers
	 * @param mixed  $info
	 */
	public function __construct($body, $headers, $info = array())
	{
		$this->body = $body;
		$this->info = $info;
		$this->parseHeader($headers);
	}

	/**
	 * Read a header string.
	 *
	 * @param  string $header
	 */
	protected  function parseHeader($header)
	{
		$headerLines = explode("\r\n", trim($header));
		$headers = array();

		if (!preg_match('/^HTTP\/\d\.\d [0-9]{3}/', $headerLines[0])) {
			throw new \InvalidArgumentException('Invalid response header');
		}
		$this->setStatus($headerLines[0]);
		unset($headerLines[0]);

		foreach ($headerLines as $header) {
			// skip empty lines
			if (!$header) {
				continue;
			}

			$delimiter = strpos($header, ':');
			if (!$delimiter) {
				continue;
			}

			$key = trim(strtolower(substr($header, 0, $delimiter)));
			$val = ltrim(substr($header, $delimiter + 1));

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

		$this->headers = $headers;
	}

	/**
	 * Set the response code.
	 *
	 * @param string $code
	 */
	protected function setStatus($status)
	{
		list(, $code) = explode(' ', $status, 2);
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
