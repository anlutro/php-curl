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
	 * @var string|null
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
	 * @param string|null $body
	 * @param string      $headers
	 * @param mixed       $info
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
		if ($header === "") {
			throw new \UnexpectedValueException('Empty header string passed!');
		}
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
		if (count($headers) === 0) {
			throw new \UnexpectedValueException('No headers passed!');
		}

		$this->headers = array();

		// find and set the HTTP status code and reason
		$firstHeader = array_shift($headers);
		if (!preg_match('/^HTTP\/\d(\.\d)? [0-9]{3}/', $firstHeader)) {
			throw new \UnexpectedValueException('Invalid response header');
		}
		list(, $status) = explode(' ', $firstHeader, 2);
		$code = explode(' ', $status);
		$code = (int) $code[0];

		// special handling for HTTP 100 responses
		if ($code === 100) {
			// remove empty header lines between 100 and actual HTTP status
			foreach ($headers as $idx => $header) {
				if ($header) {
					break;
				}
			}

			// start the process over with the 100 continue header stripped away
			return $this->parseHeaders(array_slice($headers, $idx));
		}

		// handle cases where CURLOPT_HTTPAUTH is being used, in which case
		// curl_exec may cause two HTTP responses
		if (
			array_key_exists(CURLINFO_HTTPAUTH_AVAIL, $this->info) &&
			$this->info[CURLINFO_HTTPAUTH_AVAIL] > 0 &&
			$code === 401
		) {
			$foundAuthenticateHeader = false;
			$foundSecondHttpResponse = false;
			foreach ($headers as $idx => $header) {
				if ($foundAuthenticateHeader === false && strpos(strtolower($header), 'www-authenticate:') === 0) {
					$foundAuthenticateHeader = true;
				}
				if ($foundAuthenticateHeader && preg_match('/^HTTP\/\d(\.\d)? [0-9]{3}/', $header)) {
					$foundSecondHttpResponse = true;
					break;
				}
			}

			// discard the original response.
			if ($foundAuthenticateHeader && $foundSecondHttpResponse) {
				$headers = array_slice($headers, $idx);
				return $this->parseHeaders($headers);
			}
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
	 * Get the response body.
	 *
	 * @return string
	 */
	public function getBody()
	{
		// usually because CURLOPT_FILE is set
		if ($this->body === null) {
			throw new \UnexpectedValueException("Response has no body!");
		}

		return $this->body;
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
		return (string) $this->getBody();
	}
}
