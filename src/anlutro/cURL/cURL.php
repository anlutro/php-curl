<?php
namespace anlutro\cURL;

class cURL
{
	protected $headers = array();

	protected $method = 'get';

	protected $lastResponse;

	protected $lastResponseInfo;

	protected $lastResponseHeaders;

	/**
	 * Make a HTTP GET call.
	 *
	 * @param  string $url   
	 * @param  array  $query 
	 *
	 * @return string        response body
	 */
	public function get($url, array $query = array())
	{
		$this->init($url, $query);

		return $this->exec();
	}

	/**
	 * Make a HTTP POST call.
	 *
	 * @param  string $url   
	 * @param  array  $query 
	 * @param  array  $data  
	 *
	 * @return string        response body
	 */
	public function post($url, array $query = array(), array $data = array())
	{
		$this->init($url, $query);

		$this->method = 'post';
		$this->setPostData($data);

		return $this->exec();
	}

	/**
	 * Make a HTTP DELETE call.
	 *
	 * @param  string $url   
	 * @param  array  $query 
	 * @param  array  $data  
	 *
	 * @return string        response body
	 */
	public function delete($url, array $query = array(), array $data = array())
	{
		$this->init($url, $query);

		$this->method = 'delete';
		$this->setPostData($data);

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
	 * Get all or a specific header from the last curl statement.
	 *
	 * @param  string $header Name of the header to get. If not provided, gets
	 * all headers from the last response.
	 *
	 * @return array
	 */
	public function getHeaders($header = null)
	{
		if (!$header) {
			return $this->lastResponseHeaders;
		}

		if (array_key_exists($header, $this->lastResponseHeaders)) {
			return $this->lastResponseHeaders[$header];
		}
	}

	/**
	 * Get info about the last executed curl statement.
	 *
	 * @return mixed
	 */
	public function getInfo()
	{
		return $this->lastResponseInfo;
	}

	/**
	 * Initialize a curl statement.
	 *
	 * @param  string $url
	 * @param  array  $query GET parameters only!
	 *
	 * @return void
	 */
	protected function init($url = null, $query = null)
	{
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_HEADER, true);

		if ($url && $query) {
			$queryString = http_build_query($query);
			$url .= '?' . $queryString;
		}

		if ($url) {
			curl_setopt($this->ch, CURLOPT_URL, $url);
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
		} elseif ($this->method == 'delete') {
			curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		}

		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);

		$response = curl_exec($this->ch);

		$this->extractCurlInfo($response);

		curl_close($this->ch);

		return $this->lastResponseBody;
	}

	/**
	 * Extract the response info, header and body from a cURL response. Saves
	 * the data in variables stored on the object.
	 *
	 * @param  string $response
	 *
	 * @return void
	 */
	protected function extractCurlInfo($response)
	{
		$this->lastResponse = $response;

		$this->lastResponseInfo = curl_getinfo($this->ch);

		$headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
		$headerText = substr($response, 0, $headerSize);
		$this->lastResponseHeaders = $this->headerToArray($headerText);

		$this->lastResponseBody = substr($response, $headerSize);
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