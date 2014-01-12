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
	 * Whether the response is JSON or not.
	 *
	 * @var boolean
	 */
	private $json = false;

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
		$this->headers = $headers;

		return $this;
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
	 * Set a specific header to be sent with the request.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function setHeader($key, $value)
	{
		$this->headers[$key] = $value;

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
		return isset($this->headers[$key]) ? $this->headers[$key] : null;
	}

	/**
	 * Set the POST data to be sent with the request.
	 *
	 * @param array $data
	 */
	public function setData(array $data)
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
	 * Get the cURL options for the request.
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
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
	 * Encode the POST data as a string.
	 *
	 * @return string
	 */
	public function encodeData()
	{
		if ($this->json) {
			return json_encode($this->data);
		} else {
			return http_build_query($this->data);
		}
	}

	/**
	 * Whether the response is JSON or not.
	 *
	 * @return boolean
	 */
	public function isJson()
	{
		return $this->json === true;
	}

	/**
	 * Set whether the response should be JSON or not.
	 *
	 * @param boolean $toggle
	 */
	public function setJson($toggle)
	{
		$this->json = (bool) $toggle;

		if ($this->json && !$this->getHeader('Content-Type')) {
			$this->setHeader('Content-Type', 'application/json');
		}

		return $this;
	}

	/**
	 * Send the request.
	 *
	 * @return mixed
	 */
	public function send()
	{
		return $this->curl->sendRequest($this);
	}
}
