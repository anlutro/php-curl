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
 *
 * @method Response get(string $url) Execute a GET request
 * @method Response delete(string $url) Execute a DELETE request
 * @method Response head(string $url) Execute a HEAD request
 * @method Response post(string $url, array $data) Execute a POST request
 * @method Response put(string $url, array $data) Execute a PUT request
 * @method Response patch(string $url, array $data) Execute a PATCH request
 * @method Response jsonGet(string $url) Execute a JSON GET request
 * @method Response jsonDelete(string $url) Execute a JSON DELETE request
 * @method Response jsonHead(string $url) Execute a JSON HEAD request
 * @method Response jsonPost(string $url, array $data) Execute a JSON POST request
 * @method Response jsonPut(string $url, array $data) Execute a JSON PUT request
 * @method Response jsonPatch(string $url, array $data) Execute a JSON PATCH request
 * @method Response rawGet(string $url) Execute a raw GET request
 * @method Response rawDelete(string $url) Execute a raw DELETE request
 * @method Response rawHead(string $url) Execute a raw HEAD request
 * @method Response rawPost(string $url, array $data) Execute a raw POST request
 * @method Response rawPut(string $url, array $data) Execute a raw PUT request
 * @method Response rawPatch(string $url, array $data) Execute a raw PATCH request
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
		'head'    => false,
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
	 * The default headers.
	 *
	 * @var array
	 */
	protected $defaultHeaders = array();

	/**
	 * The default curl options.
	 *
	 * @var array
	 */
	protected $defaultOptions = array();

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
	 * Set the default headers for every request.
	 *
	 * @param array $headers
	 */
	public function setDefaultHeaders(array $headers)
	{
		$this->defaultHeaders = $headers;
	}

	/**
	 * Get the default headers.
	 *
	 * @return array
	 */
	public function getDefaultHeaders()
	{
		return $this->defaultHeaders;
	}

	/**
	 * Set the default curl options for every request.
	 *
	 * @param array $options
	 */
	public function setDefaultOptions(array $options)
	{
		$this->defaultOptions = $options;
	}

	/**
	 * Get the default options.
	 *
	 * @return array
	 */
	public function getDefaultOptions()
	{
		return $this->defaultOptions;
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
		if (empty($query)) {
			return $url;
		}

		$parts = parse_url($url);

		$queryString = '';
		if (isset($parts['query']) && $parts['query']) {
			$queryString .= $parts['query'].'&'.http_build_query($query);
		} else {
			$queryString .= http_build_query($query);
		}

		$retUrl = $parts['scheme'].'://'.$parts['host'];
		if (isset($parts['port'])) {
			$retUrl .= ':'.$parts['port'];
		}

		if (isset($parts['path'])) {
			$retUrl .= $parts['path'];
		}

		if ($queryString) {
			$retUrl .= '?' . $queryString;
		}

		return $retUrl;
	}

	/**
	 * Create a new response object and set its values.
	 *
	 * @param  string  $method    get, post, etc
	 * @param  string  $url
	 * @param  mixed   $data      POST data
	 * @param  int     $encoding  Request::ENCODING_* constant specifying how to process the POST data
	 *
	 * @return Request
	 */
	public function newRequest($method, $url, $data = array(), $encoding = Request::ENCODING_QUERY)
	{
		$class = $this->requestClass;
		$request = new $class($this);

		if ($this->defaultHeaders) {
			$request->setHeaders($this->defaultHeaders);
		}
		if ($this->defaultOptions) {
			$request->setOptions($this->defaultOptions);
		}
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
	 * @return Request
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
	 * @param  mixed  $data    request body
	 *
	 * @return Request
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
		if ($auth = $request->getUserAndPass()) {
			curl_setopt($this->ch, CURLOPT_USERPWD, $auth);
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
			$errno = curl_errno($this->ch);
			$errmsg = curl_error($this->ch);
			$msg = "cURL request failed with error [$errno]: $errmsg";
			curl_close($this->ch);
			throw new cURLException($request, $msg, $errno);
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

		$headers = substr($response, 0, $headerSize);
		$body = substr($response, $headerSize);

		$class = $this->responseClass;

		return new $class($body, $headers, $info);
	}

	/**
	 * Handle dynamic calls to the class.
	 *
	 * @param  string $func
	 * @param  array  $args
	 *
	 * @return mixed
	 */
	public function __call($func, $args)
	{
		$method = strtolower($func);

		$encoding = Request::ENCODING_QUERY;

		if (substr($method, 0, 4) === 'json') {
			$encoding = Request::ENCODING_JSON;
			$method = substr($method, 4);
		} elseif (substr($method, 0, 3) === 'raw') {
			$encoding = Request::ENCODING_RAW;
			$method = substr($method, 3);
		}

		if (!array_key_exists($method, $this->methods)) {
			throw new \BadMethodCallException("Method [$method] not a valid HTTP method.");
		}

		if (!isset($args[0])) {
			throw new \BadMethodCallException('Missing argument 1 ($url) for '.__CLASS__.'::'.$func);
		}
		$url = $args[0];

		if (isset($args[1])) {
			if (!$this->methods[$method]) {
				throw new \InvalidArgumentException("HTTP method [$method] does not allow POST data.");
			}
			$data = $args[1];
		} else {
			$data = null;
		}

		$request = $this->newRequest($method, $url, $data, $encoding);

		return $this->sendRequest($request);
	}
}
