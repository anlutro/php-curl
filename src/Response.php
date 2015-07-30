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
	 * Parse a header string.
	 *
	 * @param  string $header
	 *
	 * @return void
	 */
	protected function parseHeader($header)
	{
		$headers = explode("\r\n", trim($header));
		$this->parseHeaders($headers);
	}

	/**
	 * Parse an array of headers.
	 *
	 * @param  array  $headers
	 *
	 * @return void
	 */
	protected function parseHeaders(array $headers)
	{
		$this->headers = array();

		// find and set the HTTP status code and reason
		$firstHeader = array_shift($headers);
		if (!preg_match('/^HTTP\/\d\.\d [0-9]{3}/', $firstHeader)) {
			throw new \InvalidArgumentException('Invalid response header');
		}
		list(, $status) = explode(' ', $firstHeader, 2);
		$code = explode(' ', $status);
		$code = (int) $code[0];

		// special handling for HTTP 100 responses
		if ($code === 100) {
			// remove empty header lines between 100 and actual HTTP status
			foreach ($headers as $key => $header) {
				if ($header) {
					break;
				}
				unset($headers[$key]);
			}

			// start the process over with the 100 continue header stripped away
			return $this->parseHeaders($headers);
		}

		$this->statusText = $status;
		$this->statusCode = $code;

		foreach ($headers as $header) {
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

			if (isset($this->headers[$key])) {
				if (is_array($this->headers[$key])) {
					$this->headers[$key][] = $val;
				} else {
					$this->headers[$key] = array($this->headers[$key], $val);
				}
			} else {
				$this->headers[$key] = $val;
			}
		}
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
