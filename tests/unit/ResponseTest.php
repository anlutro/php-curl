<?php

use anlutro\cURL\Response;

class ResponseTest extends PHPUnit_Framework_TestCase
{
	private function makeResponse($body, $headers, $info = array())
	{
		return new Response($body, $headers, $info);
	}

	/** @test */
	public function parsesHttpResponseCodeCorrectly()
	{
		$r = $this->makeResponse('', 'HTTP/1.1 200 OK');
		$this->assertEquals(200, $r->statusCode);
		$this->assertEquals('200 OK', $r->statusText);

		$r = $this->makeResponse('', 'HTTP/1.1 302 Found');
		$this->assertEquals(302, $r->statusCode);
		$this->assertEquals('302 Found', $r->statusText);
	}

	/** @test */
	public function parsesHeaderStringCorrectly()
	{
		$header = "Content-Type: text/plain\r\nContent-Length: 0";
		$r = $this->makeResponse('', $header);
		$this->assertEquals('text/plain', $r->getHeader('content-type'));
		$this->assertEquals('0', $r->getHeader('content-length'));
		$this->assertEquals(null, $r->getHeader('x-nonexistant'));
	}

	/** @test */
	public function duplicateHeadersAreHandled()
	{
		$header = "Cache-Control: max-age=60\r\nCache-Control: public";
		$r = $this->makeResponse('', $header);
		$this->assertEquals(array('max-age=60', 'public'), $r->getHeader('cache-control'));
	}
}
