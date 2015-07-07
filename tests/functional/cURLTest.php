<?php

class cURLTest extends PHPUnit_Framework_TestCase
{
	const URL = 'http://localhost:8080';

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
	public function jsonRequestBody()
	{
		$r = $this->makeCurl()->jsonPost(static::URL.'/echo.php', array('foo' => 'bar'));
		$this->assertEquals('{"foo":"bar"}', $r->body);
	}

	/** @test */
	public function rawRequestBody()
	{
		$r = $this->makeCurl()->rawPost(static::URL.'/echo.php', '<foo/>');
		$this->assertEquals('<foo/>', $r->body);
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
}
