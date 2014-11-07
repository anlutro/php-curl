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
	 * Allowed methods => allows postdata
	 *
	 * @var array
	 */
	protected $methods = array(
		'get'     => false,
		'post'    => true,
		'put'     => true,
		'patch'   => true,
		'delete'  => false,
		'options' => false,
	);

	/**
	 * The request class to use.
	 *
	 * @var string
	 */
	protected $requestClass = 'anlutro\cURL\Request';

	/**
	 * The response class to use.
	 *
	 * @var string
	 */
	protected $responseClass = 'anlutro\cURL\Response';

	/**
	 * Get allowed methods.
	 *
	 * @return array
	 */
	public function getAllowedMethods()
	{
		return $this->methods;
	}

	/**
	 * Set the request class.
	 *
	 * @param string $class
	 */
	public function setRequestClass($class)
	{
		$this->requestClass = $class;
	}

	/**
	 * Set the response class.
	 *
	 * @param string $class
	 */
	public function setResponseClass($class)
	{
		$this->responseClass = $class;
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
	 * Create a new response object and set its values.
	 *
	 * @param  string  $method    get, post, etc
	 * @param  string  $url
	 * @param  mixed   $data      POST data
	 * @param  int     $encoding  Request::ENCODING_* constant specifying how to process the POST data
	 *
	 * @return mixed
	 */
	public function newRequest($method, $url, $data = array(), $encoding = Request::ENCODING_QUERY)
	{
		$class = $this->requestClass;
		$request = new $class($this);

		$request->setMethod($method);
		$request->setUrl($url);
		$request->setData($data);
		$request->setEncoding($encoding);

		return $request;
	}

	/**
	 * Create a new JSON request and set its values.
	 *
	 * @param  string $method  get, post etc
	 * @param  string $url
	 * @param  array  $data    POST data
	 *
	 * @return mixed
	 */
	public function newJsonRequest($method, $url, array $data = array())
	{
		return $this->newRequest($method, $url, $data, Request::ENCODING_JSON);
	}

	/**
	 * Create a new raw request and set its values.
	 *
	 * @param  string $method  get, post etc
	 * @param  string $url
	 * @param  array  $data    POST data
	 *
	 * @return mixed
	 */
	public function newRawRequest($method, $url, $data = '')
	{
		return $this->newRequest($method, $url, $data, Request::ENCODING_RAW);
	}

	/**
	 * Prepare the curl resource for sending a request.
	 *
	 * @param  Request $request
	 *
	 * @return void
	 */
	public function prepareRequest(Request $request)
	{
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_HEADER, true);
        if($request->getUserAndPass()) {
		    curl_setopt($this->ch, CURLOPT_USERPWD, $request->getUserAndPass());
        }
		curl_setopt($this->ch, CURLOPT_URL, $request->getUrl());

		$options = $request->getOptions();
		if (!empty($options)) {
			curl_setopt_array($this->ch, $options);
		}

		$method = $request->getMethod();
		if ($method === 'post') {
			curl_setopt($this->ch, CURLOPT_POST, 1);
		} elseif ($method !== 'get') {
			curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		}

		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $request->formatHeaders());

		if ($this->methods[$method] === true) {
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $request->encodeData());
		}
	}

	/**
	 * Send a request.
	 *
	 * @param  Request $request
	 *
	 * @return Response
	 */
	public function sendRequest(Request $request)
	{
		$this->prepareRequest($request);

		$result = curl_exec($this->ch);

		if ($result === false) {
			$exception = new \RuntimeException("cURL request failed with error: " . curl_error($this->ch));
			curl_close($this->ch);
			throw $exception;
		}

		$response = $this->createResponseObject($result);

		curl_close($this->ch);

		return $response;
	}

	/**
	 * Extract the response info, header and body from a cURL response. Saves
	 * the data in variables stored on the object.
	 *
	 * @param  string $response
	 *
	 * @return Response
	 */
	protected function createResponseObject($response)
	{
		$info = curl_getinfo($this->ch);

		$headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
		$headerText = substr($response, 0, $headerSize);
		$headers = $this->headerToArray($headerText);

		$body = substr($response, $headerSize);

		$class = $this->responseClass;
		$obj = new $class($body, $headers, $info);

		return $obj;
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

	/**
	 * Handle dynamic calls to the class.
	 *
	 * @param  string $method
	 * @param  array  $args
	 *
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		$method = strtolower($method);

		$encoding = Request::ENCODING_QUERY;

		if (substr($method, 0, 4) === 'json') {
			$encoding = Request::ENCODING_JSON;
			$method = substr($method, 4);
		} elseif (substr($method, 0, 3) === 'raw') {
			$encoding = Request::ENCODING_RAW;
			$method = substr($method, 3);
		}

		if (!array_key_exists($method, $this->methods)) {
			throw new \InvalidArgumentException("Method [$method] not a valid HTTP method.");
		}

		$url = $args[0];

		$allowData = $this->methods[$method];
		if ($allowData && isset($args[1])) {
			$data = $args[1];
		} else {
			$data = array();
		}

		$request = $this->newRequest($method, $url, $data, $encoding);

		return $this->sendRequest($request);
	}
}
