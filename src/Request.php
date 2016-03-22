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
 * cURL request representation class.
 */
class Request
{
	/**
	 * ENCODING_* constants, used for specifying encoding options
	 */
	const ENCODING_QUERY = 0;
	const ENCODING_JSON = 1;
	const ENCODING_RAW = 2;

	/**
	 * The HTTP method to use. Defaults to GET.
	 *
	 * @var string
	 */
	private $method = 'get';

	/**
	 * The URL the request is sent to.
	 *
	 * @var string
	 */
	private $url = '';

	/**
	 * The headers sent with the request.
	 *
	 * @var array
	 */
	private $headers = array();

	/**
	 * The cookies sent with the request.
	 *
	 * @var array
	 */
	private $cookies = array();

	/**
	 * POST data sent with the request.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Optional cURL options.
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Username to authenticate the request of cURL.
	 *
	 * @var array
	 */
	private $user = '';

	/**
	 * Password to authenticate the request of cURL.
	 *
	 * @var array
	 */
	private $pass = '';

	/**
	 * The type of processing to perform to encode the POST data
	 *
	 * @var int
	 */
	private $encoding = Request::ENCODING_QUERY;

	/**
	 * @param cURL $curl
	 */
	public function __construct(cURL $curl)
	{
		$this->curl = $curl;
	}

	/**
	 * Set the HTTP method of the request.
	 *
	 * @param string $method
	 */
	public function setMethod($method)
	{
		$method = strtolower($method);

		if (!array_key_exists($method, $this->curl->getAllowedMethods())) {
			throw new \InvalidArgumentException("Method [$method] not a valid HTTP method.");
		}

		$this->method = $method;

		return $this;
	}

	/**
	 * Get the HTTP method of the request.
	 *
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Set the URL of the request.
	 *
	 * @param string $url
	 */
	public function setUrl($url)
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * Get the URL of the request.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Set a specific header to be sent with the request.
	 *
	 * @param string $key   Can also be a string in "foo: bar" format
	 * @param mixed  $value
	 * @param boolean  $preserveCase
	 */
	public function setHeader($key, $value = null, $preserveCase = false)
	{
		if ($value === null) {
			list($key, $value) = explode(':', $value, 2);
		}

		if (!$preserveCase) {
			$key = strtolower($key);
		}
		
		$key = trim($key);
		$this->headers[$key] = trim($value);

		return $this;
	}

	/**
	 * Set the headers to be sent with the request.
	 *
	 * Pass an associative array - e.g. ['Content-Type' => 'application/json']
	 * and the correct header formatting - e.g. 'Content-Type: application/json'
	 * will be done for you when the request is sent.
	 *
	 * @param array $headers
	 */
	public function setHeaders(array $headers)
	{
		$this->headers = array();

		foreach ($headers as $key => $value) {
			$this->setHeader($key, $value);
		}

		return $this;
	}

	/**
	 * Get a specific header from the request.
	 *
	 * @param  string $key
	 *
	 * @return mixed
	 */
	public function getHeader($key)
	{
		$key = strtolower($key);

		return isset($this->headers[$key]) ? $this->headers[$key] : null;
	}

	/**
	 * Get the headers to be sent with the request.
	 *
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * Set a cookie.
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function setCookie($key, $value)
	{
		$this->cookies[$key] = $value;
		$this->updateCookieHeader();

		return $this;
	}

	/**
	 * Replace the request's cookies.
	 *
	 * @param array $cookies
	 */
	public function setCookies(array $cookies)
	{
		$this->cookies = $cookies;
		$this->updateCookieHeader();

		return $this;
	}

	/**
	 * Read the request cookies and set the cookie header.
	 *
	 * @return void
	 */
	private function updateCookieHeader()
	{
		$strings = array();

		foreach ($this->cookies as $key => $value) {
			$strings[] = "{$key}={$value}";
		}

		$this->setHeader('cookie', implode('; ', $strings));
	}

	/**
	 * Get a specific cookie from the request.
	 *
	 * @param  string $key
	 *
	 * @return string|null
	 */
	public function getCookie($key)
	{
		return isset($this->cookies[$key]) ? $this->cookies[$key] : null;
	}

	/**
	 * Get all the request's cookies.
	 *
	 * @return string[]
	 */
	public function getCookies()
	{
		return $this->cookies;
	}

	/**
	 * Format the headers to an array of 'key: val' which can be passed to
	 * curl_setopt.
	 *
	 * @return array
	 */
	public function formatHeaders()
	{
		$headers = array();

		foreach ($this->headers as $key => $val) {
			if (is_string($key)) {
				$headers[] = $key . ': ' . $val;
			} else {
				$headers[] = $val;
			}
		}

		return $headers;
	}

	/**
	 * Set the POST data to be sent with the request.
	 *
	 * @param mixed $data
	 */
	public function setData($data)
	{
		$this->data = $data;

		return $this;
	}

	/**
	 * Get the POST data to be sent with the request.
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	* Set the encoding to use on the POST data, and (possibly) associated Content-Type headers
	*
	* @param int  $encoding  a Request::ENCODING_* constant
	*/
	public function setEncoding($encoding)
	{
		$encoding = intval($encoding);

		if (
			$encoding !== static::ENCODING_QUERY &&
			$encoding !== static::ENCODING_JSON &&
			$encoding !== static::ENCODING_RAW
		) {
			throw new \InvalidArgumentException("Encoding [$encoding] not a known Request::ENCODING_* constant");
		}

		if ($encoding === static::ENCODING_JSON && !$this->getHeader('Content-Type')) {
			$this->setHeader('Content-Type', 'application/json');
		}

		$this->encoding = $encoding;

		return $this;
	}

	/**
	 * Get the current encoding which will be used on the POST data
	 *
	 * @return int  a Request::ENCODING_* constant
	 */
	public function getEncoding()
	{
		return $this->encoding;
	}

	/**
	 * Encode the POST data as a string.
	 *
	 * @return string
	 */
	public function encodeData()
	{
		switch ($this->encoding) {
			case static::ENCODING_JSON:
				return json_encode($this->data);
			case static::ENCODING_QUERY:
				return http_build_query($this->data);
			case static::ENCODING_RAW:
				return $this->data;
			default:
				throw new \UnexpectedValueException("Encoding [$encoding] not a known Request::ENCODING_* constant");
		}
	}

	/**
	 * Set a specific curl option for the request.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function setOption($key, $value)
	{
		$this->options[$key] = $value;

		return $this;
	}

	/**
	 * Set the cURL options for the request.
	 *
	 * @param array $options
	 */
	public function setOptions(array $options)
	{
		$this->options = $options;

		return $this;
	}

	/**
	 * Get a specific curl option from the request.
	 *
	 * @param  string $key
	 *
	 * @return mixed
	 */
	public function getOption($key)
	{
		return isset($this->options[$key]) ? $this->options[$key] : null;
	}

	/**
	 * Get the cURL options for the request.
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Set the HTTP basic username and password.
	 *
	 * @param  string $user
	 * @param  string $pass
	 *
	 * @return string
	 */
	public function auth($user, $pass)
	{
		$this->user = $user;
		$this->pass = $pass;

		return $this;
	}

	/**
	 * Set an username to authenticate the request of curl.
	 *
	 * @param  string $user
	 *
	 * @return static
	 */
	public function setUser($user)
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * Set a password to authenticate the request of curl.
	 *
	 * @param  string $pass
	 *
	 * @return static
	 */
	public function setPass($pass)
	{
		$this->pass = $pass;

		return $this;
	}

	/**
	 * If username and password is set, returns a string of 'username:password'.
	 * If not, returns null.
	 *
	 * @return string|null
	 */
	public function getUserAndPass()
	{
		if ($this->user) {
			return $this->user . ':' . $this->pass;
		}

		return null;
	}

	/**
	 * Whether the request is JSON or not.
	 *
	 * @return boolean
	 */
	public function isJson()
	{
		return $this->encoding === static::ENCODING_JSON;
	}

	/**
	 * Send the request.
	 *
	 * @return Response
	 */
	public function send()
	{
		return $this->curl->sendRequest($this);
	}
}
