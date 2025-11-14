<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group("server")]
class cURLTest extends TestCase
{
	const URL = 'http://localhost:8080';

	public function setUp(): void
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

	#[Test]
	public function successfulResponse()
	{
		$r = $this->makeCurl()->get(static::URL.'/success.php');
		$this->assertEquals(200, $r->statusCode);
		$this->assertEquals('200 OK', $r->statusText);
		$this->assertEquals('OK', $r->body);
		$this->assertNotNull($r->headers);
		$this->assertNotNull($r->info);
	}

	#[Test]
	public function failedResponse()
	{
		$r = $this->makeCurl()->get(static::URL.'/failure.php');
		$this->assertEquals(500, $r->statusCode);
		$this->assertEquals('500 Internal Server Error', $r->statusText);
		$this->assertEquals('Failure', $r->body);
		$this->assertNotNull($r->headers);
		$this->assertNotNull($r->info);
	}

	#[Test]
	public function queryRequestBody()
	{
		$r = $this->makeCurl()->post(static::URL.'/echo.php', array('foo' => 'bar'));
		$this->assertEquals('foo=bar', $r->body);
	}

	#[Test]
	public function queryRequestEmptyArrayBody()
	{
		$r = $this->makeCurl()->post(static::URL.'/echo.php', array());
		$this->assertEquals('', $r->body);
	}

	#[Test]
	public function queryRequestEmptyObjectBody()
	{
		$r = $this->makeCurl()->post(static::URL.'/echo.php', new \stdClass());
		$this->assertEquals('', $r->body);
	}

	#[Test]
	public function jsonRequestBody()
	{
		$r = $this->makeCurl()->jsonPost(static::URL.'/echo.php', array('foo' => 'bar'));
		$this->assertEquals('{"foo":"bar"}', $r->body);
	}

	#[Test]
	public function jsonRequestEmptyArrayBody()
	{
		$r = $this->makeCurl()->jsonPost(static::URL.'/echo.php', array());
		$this->assertEquals('[]', $r->body);
	}

	#[Test]
	public function jsonRequestEmptyObjectBody()
	{
		$r = $this->makeCurl()->jsonPost(static::URL.'/echo.php', new \stdClass());
		$this->assertEquals('{}', $r->body);
	}

	#[Test]
	public function rawRequestBody()
	{
		$r = $this->makeCurl()->rawPost(static::URL.'/echo.php', '<foo/>');
		$this->assertEquals('<foo/>', $r->body);
	}

	#[Test]
	public function rawRequestEmptyBody()
	{
		$r = $this->makeCurl()->rawPost(static::URL.'/echo.php', '');
		$this->assertEquals('', $r->body);
	}

	#[Test]
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

	#[Test]
	public function digestAuth()
	{
		$curl = $this->makeCurl();
		$request = $curl->newRequest('get', static::URL . '/digest-auth.php');
		$request->auth('guest', 'guest');
		$request->setOption(CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		$response = $curl->sendRequest($request);
		$this->assertEquals(200, $response->statusCode);
	}

	#[Test]
	public function throwsExceptionOnCurlError()
	{
		$this->expectException('anlutro\cURL\cURLException', 'cURL request failed with error [7]:');
		$this->makeCurl()->get('http://0.0.0.0');
	}

	#[Test]
	public function throwsExceptionWithMissingUrl()
	{
		$this->expectException('BadMethodCallException', 'Missing argument 1 ($url) for anlutro\cURL\cURL::get');
		$this->makeCurl()->get();
	}

	#[Test]
	public function throwsExceptionWhenDataProvidedButNotAllowed()
	{
		$this->expectException('InvalidArgumentException', 'HTTP method [options] does not allow POST data.');
		$this->makeCurl()->options('http://localhost', array('foo' => 'bar'));
	}

	#[Test]
	public function defaultHeadersAreAdded()
	{
		$curl = $this->makeCurl();
		$curl->setDefaultHeaders(array('foo' => 'bar'));
		$request = $curl->newRequest('post', 'does-not-matter');
		$this->assertEquals('bar', $request->getHeader('foo'));
	}

	#[Test]
	public function defaultOptionsAreAdded()
	{
		$curl = $this->makeCurl();
		$curl->setDefaultOptions(array('foo' => 'bar'));
		$request = $curl->newRequest('post', 'does-not-matter');
		$this->assertEquals('bar', $request->getOption('foo'));
	}

	#[Test]
	public function curloptFileWorks()
	{
		$r = $this->makeCurl()
			->newRequest('get', static::URL.'/success.php')
			->setOption(CURLOPT_FILE, $fh = tmpfile())
			->send();
		$this->assertEquals(200, $r->statusCode);
		$this->assertEquals('200 OK', $r->statusText);
		$this->assertNotNull($r->headers);
		$this->assertNotNull($r->info);
		$this->assertNull($r->body);
	}
}
