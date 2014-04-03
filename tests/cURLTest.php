<?php
class cURLTest extends PHPUnit_Framework_TestCase
{
	public function testPhpNet()
	{
		$curl = $this->makeCurl();

		$r = $curl->get('https://www.php.net');

		$this->assertEquals('200 OK', $r->code);
		$this->assertNotNull($r->body);
		$this->assertNotNull($r->headers);
		$this->assertNotNull($r->info);
	}

	public function testConvenienceBuilders()
	{
		$curl = $this->makeCurl();
		$r = $curl->newRequest('get', 'https://www.php.net', array('foo' => 'bar'));
		$this->assertEquals('get', $r->getMethod());
		$this->assertEquals('https://www.php.net', $r->getUrl());
		$this->assertEquals(array('foo' => 'bar'), $r->getData());
		$this->assertEquals(\anlutro\cURL\Request::ENCODING_URL, $r->getEncoding());

		$r = $curl->newJsonRequest('get', 'https://www.php.net', array('foo' => 'bar'));
		$this->assertEquals('get', $r->getMethod());
		$this->assertEquals('https://www.php.net', $r->getUrl());
		$this->assertEquals(array('foo' => 'bar'), $r->getData());
		$this->assertEquals(\anlutro\cURL\Request::ENCODING_JSON, $r->getEncoding());

		$r = $curl->newRawRequest('get', 'https://www.php.net', array('foo' => 'bar'));
		$this->assertEquals('get', $r->getMethod());
		$this->assertEquals('https://www.php.net', $r->getUrl());
		$this->assertEquals(array('foo' => 'bar'), $r->getData());
		$this->assertEquals(\anlutro\cURL\Request::ENCODING_RAW, $r->getEncoding());
	}

	public function makeCurl()
	{
		return new \anlutro\cURL\cURL;
	}
}
