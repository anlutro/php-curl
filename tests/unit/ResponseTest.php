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
	public function parsesHttp2ResponseCorrectly()
	{
		$r = $this->makeResponse('', 'HTTP/2 200 OK');
		$this->assertEquals(200, $r->statusCode);
		$this->assertEquals('200 OK', $r->statusText);
	}

	/** @test */
	public function parsesHeaderStringCorrectly()
	{
		$header = "HTTP/1.1 200 OK\r\nContent-Type: text/plain\r\nContent-Length: 0";
		$r = $this->makeResponse('', $header);
		$this->assertEquals('text/plain', $r->getHeader('content-type'));
		$this->assertEquals('0', $r->getHeader('content-length'));
		$this->assertEquals(null, $r->getHeader('x-nonexistant'));
	}

	/** @test */
	public function duplicateHeadersAreHandled()
	{
		$header = "HTTP/1.1 200 OK\r\nX-Var: A\r\nX-Var: B\r\nX-Var: C";
		$r = $this->makeResponse('', $header);
		$this->assertEquals(array('A', 'B', 'C'), $r->getHeader('X-Var'));
	}

	/** @test */
	public function httpContinueResponsesAreHandled()
	{
		$header = "HTTP/1.1 100 Continue\r\n\r\nHTTP/1.1 200 OK\r\nx-var: foo";
		$r = $this->makeResponse('', $header);
		$this->assertEquals(200, $r->statusCode);
		$this->assertEquals('foo', $r->getHeader('x-var'));
	}

	/** @test */
	public function throwsExceptionIfHeaderDoesntStartWithHttpStatus()
	{
		$this->setExpectedException('UnexpectedValueException', 'Invalid response header');
		$this->makeResponse('', 'x-var: foo');
	}

	/** @test */
	public function httpUnauthorizedResponsesContainingMultipleStatusesAreHandled()
	{
		$header = "HTTP/1.1 401 Unauthorized\r\nwww-authenticate: digest something\r\n\r\nHTTP/1.1 200 OK\r\nx-var: foo";
		$r = $this->makeResponse('', $header, [CURLINFO_HTTPAUTH_AVAIL => CURLAUTH_DIGEST]);
		$this->assertEquals(200, $r->statusCode);
		$this->assertEquals('foo', $r->getHeader('x-var'));
	}
}
