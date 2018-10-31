<?php

/**
 * @group server
 */
class cURLTest extends PHPUnit_Framework_TestCase
{
	const URL = 'http://localhost:8080';

	public function setUp()
	{
		if (!getenv('CURL_TEST_SERVER_RUNNING')) {
			$this->markTestSkipped('The web server is not running.');
		}
		if (!extension_loaded('curl')) {
			$this->markTestSkipped('The curl extension is not installed.');
		}
	}

	private function makeCurl()
	{
		return new anlutro\cURL\cURL;
	}

	/** @test */
	public function successfulResponse()
	{
		$r = $this->makeCurl()->get(static::URL.'/success.php');
		$this->assertEquals(200, $r->statusCode);
		$this->assertEquals('200 OK', $r->statusText);
		$this->assertEquals('OK', $r->body);
		$this->assertNotNull($r->headers);
		$this->assertNotNull($r->info);
	}

	/** @test */
	public function failedResponse()
	{
		$r = $this->makeCurl()->get(static::URL.'/failure.php');
		$this->assertEquals(500, $r->statusCode);
		$this->assertEquals('500 Internal Server Error', $r->statusText);
		$this->assertEquals('Failure', $r->body);
		$this->assertNotNull($r->headers);
		$this->assertNotNull($r->info);
	}

	/** @test */
	public function queryRequestBody()
	{
		$r = $this->makeCurl()->post(static::URL.'/echo.php', array('foo' => 'bar'));
		$this->assertEquals('foo=bar', $r->body);
	}

	/** @test */
	public function queryRequestEmptyArrayBody()
	{
		$r = $this->makeCurl()->post(static::URL.'/echo.php', array());
		$this->assertEquals('', $r->body);
	}

	/** @test */
	public function queryRequestEmptyObjectBody()
	{
		$r = $this->makeCurl()->post(static::URL.'/echo.php', new \stdClass());
		$this->assertEquals('', $r->body);
	}

	/** @test */
	public function jsonRequestBody()
	{
		$r = $this->makeCurl()->jsonPost(static::URL.'/echo.php', array('foo' => 'bar'));
		$this->assertEquals('{"foo":"bar"}', $r->body);
	}

	/** @test */
	public function jsonRequestEmptyArrayBody()
	{
		$r = $this->makeCurl()->jsonPost(static::URL.'/echo.php', array());
		$this->assertEquals('[]', $r->body);
	}

	/** @test */
	public function jsonRequestEmptyObjectBody()
	{
		$r = $this->makeCurl()->jsonPost(static::URL.'/echo.php', new \stdClass());
		$this->assertEquals('{}', $r->body);
	}

	/** @test */
	public function rawRequestBody()
	{
		$r = $this->makeCurl()->rawPost(static::URL.'/echo.php', '<foo/>');
		$this->assertEquals('<foo/>', $r->body);
	}

	/** @test */
	public function rawRequestEmptyBody()
	{
		$r = $this->makeCurl()->rawPost(static::URL.'/echo.php', '');
		$this->assertEquals('', $r->body);
	}

	/** @test */
	public function fileUpload()
	{
		$file = __FILE__;
		if (function_exists('curl_file_create')) {
			$data = array('file' => curl_file_create($file));
		} else {
			$data = array('file' => '@'.$file);
		}

		$r = $this->makeCurl()->rawPost(static::URL.'/upload.php', $data);
		$this->assertEquals(basename($file)."\t".filesize($file)."\n", $r->body);
	}

	/** @test */
	public function throwsExceptionOnCurlError()
	{
		$this->setExpectedException('anlutro\cURL\cURLException', 'cURL request failed with error [7]:');
		$this->makeCurl()->get('http://0.0.0.0');
	}

	/** @test */
	public function throwsExceptionWithMissingUrl()
	{
		$this->setExpectedException('BadMethodCallException', 'Missing argument 1 ($url) for anlutro\cURL\cURL::get');
		$this->makeCurl()->get();
	}

	/** @test */
	public function throwsExceptionWhenDataProvidedButNotAllowed()
	{
		$this->setExpectedException('InvalidArgumentException', 'HTTP method [options] does not allow POST data.');
		$this->makeCurl()->options('http://localhost', array('foo' => 'bar'));
	}

	/** @test */
	public function defaultHeadersAreAdded()
	{
		$curl = $this->makeCurl();
		$curl->setDefaultHeaders(array('foo' => 'bar'));
		$request = $curl->newRequest('post', 'localhost');
		$this->assertEquals('bar', $request->getHeader('foo'));
	}

	/** @test */
	public function defaultOptionsAreAdded()
	{
		$curl = $this->makeCurl();
		$curl->setDefaultOptions(array('foo' => 'bar'));
		$request = $curl->newRequest('post', 'localhost');
		$this->assertEquals('bar', $request->getOption('foo'));
	}
}
