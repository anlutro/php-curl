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
class cURL
{
	/**
	 * The cURL resource.
	 */
	protected $ch;

	/**
	 * The headers to be sent with the request.
	 *
	 * @var array
	 */
	protected $headers = array();

	/**
	 * The method the request should use.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * Make a HTTP GET call.
	 *
	 * @param  string $url   
	 * @param  array  $query   optional - GET parameters/query string
	 * @param  array  $options optional - cURL options (curl_setopt_array)
	 *
	 * @return string        response body
	 */
	public function get($url, array $query = array(), array $options = array())
	{
		if (!empty($query)) {
			$url = $this->buildUrl($url, $query);
		}

		$this->init($url, $options);
		
		$this->method = 'get';

		return $this->exec();
	}

	/**
	 * Make a HTTP POST call.
	 *
	 * @param  string $url   
	 * @param  array  $data    optional - POST data
	 * @param  array  $options optional - cURL options (curl_setopt_array)
	 *
	 * @return string        response body
	 */
	public function post($url, array $data = array(), array $options = array())
	{
		$this->init($url, $options);

		$this->method = 'post';

		if (!empty($data)) {
			$this->setPostData($data);
		}

		return $this->exec();
	}

	/**
	 * Make a HTTP DELETE call.
	 *
	 * @param  string $url
	 * @param  array  $options optional - cURL options (curl_setopt_array)
	 *
	 * @return string        response body
	 */
	public function delete($url, array $options = array())
	{
		$this->init($url, $options);

		$this->method = 'delete';

		return $this->exec();
	}

	/**
	 * Make a HTTP PATCH call.
	 *
	 * @param  string $url   
	 * @param  array  $data    optional - POST data
	 * @param  array  $options optional - cURL options (curl_setopt_array)
	 *
	 * @return string        response body
	 */
	public function patch($url, array $data = array(), array $options = array())
	{
		$this->init($url, $options);

		$this->method = 'patch';

		if (!empty($data)) {
			$this->setPostData($data);
		}

		return $this->exec();
	}

	/**
	 * Make a HTTP PUT call.
	 *
	 * @param  string $url   
	 * @param  array  $data    optional - POST data
	 * @param  array  $options optional - cURL options (curl_setopt_array)
	 *
	 * @return string        response body
	 */
	public function put($url, array $data = array(), array $options = array())
	{
		$this->init($url, $options);

		$this->method = 'put';

		if (!empty($data)) {
			$this->setPostData($data);
		}

		return $this->exec();
	}

	/**
	 * Make a HTTP OPTIONS call.
	 *
	 * @param  string $url
	 * @param  array  $options optional - cURL options (curl_setopt_array)
	 *
	 * @return string        response body
	 */
	public function options($url, array $options = array())
	{
		$this->init($url, $options);

		$this->method = 'options';

		return $this->exec();
	}

	/**
	 * Add a header to the request.
	 *
	 * @param string $value
	 */
	public function addHeader($value)
	{
		$this->headers[] = $value;
	}

	/**
	 * Get info about the last executed curl statement.
	 *
	 * @return mixed
	 */
	public function getCurlInfo()
	{
		return $this->responseInfo;
	}

	/**
	 * Build an URL with an optional query string.
	 *
	 * @param  string $url   the base URL without any query string
	 * @param  array  $query array of GET parameters
	 *
	 * @return string
	 */
	public function buildUrl($url, array $query)
	{
		// append the query string
		if (!empty($query)) {
			$queryString = http_build_query($query);
			$url .= '?' . $queryString;
		}

		return $url;
	}

	/**
	 * Initialize a curl statement.
	 *
	 * @param  string $url
	 * @param  array  $options optional - cURL options (curl_setopt_array)
	 *
	 * @return void
	 */
	protected function init($url, $options = array())
	{
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_HEADER, true);
		curl_setopt($this->ch, CURLOPT_URL, $url);

		if (!empty($options)) {
			curl_setopt_array($this->ch, $options);
		}
	}

	/**
	 * Set the POST data of the call.
	 *
	 * @param array $data
	 */
	protected function setPostData($data)
	{
		$postData = http_build_query($data);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postData);
	}

	/**
	 * Execute the prepared curl resource.
	 *
	 * @return string response body
	 */
	protected function exec()
	{
		if ($this->method == 'post') {
			curl_setopt($this->ch, CURLOPT_POST, 1);
		} elseif ($this->method !== 'get') {
			curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, strtoupper($this->method));
		}

		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);

		$response = $this->createResponseObject(curl_exec($this->ch));

		curl_close($this->ch);

		return $response;
	}

	/**
	 * Extract the response info, header and body from a cURL response. Saves
	 * the data in variables stored on the object.
	 *
	 * @param  string $response
	 *
	 * @return void
	 */
	protected function createResponseObject($response)
	{
		$info = curl_getinfo($this->ch);

		$headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
		$headerText = substr($response, 0, $headerSize);
		$headers = $this->headerToArray($headerText);

		$body = substr($response, $headerSize);

		return new Response($body, $headers, $info);
	}

	/**
	 * Turn a header string into an array.
	 *
	 * @param  string $header
	 *
	 * @return array
	 */
	protected function headerToArray($header)
	{
		$tmp = explode("\r\n", $header);
		$headers = array();
		foreach ($tmp as $singleHeader) {
			$delimiter = strpos($singleHeader, ': ');
			if ($delimiter !== false) {
				$key = substr($singleHeader, 0, $delimiter);
				$val = substr($singleHeader, $delimiter + 2);
				$headers[$key] = $val;
			} else {
				$delimiter = strpos($singleHeader, ' ');
				if ($delimiter !== false) {
					$key = substr($singleHeader, 0, $delimiter);
					$val = substr($singleHeader, $delimiter + 1);
					$headers[$key] = $val;
				}
			}
		}
		return $headers;
	}
}
