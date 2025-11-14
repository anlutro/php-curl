<?php

use anlutro\cURL\Request;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RequestTest extends TestCase
{
	private function makeRequest($curl = null)
	{
		return new Request($curl ?: new \anlutro\cURL\cURL);
	}

	#[Test]
	public function settersAndGetters()
	{
		$r = $this->makeRequest();

		$r->setUrl('foo');
		$this->assertEquals('foo', $r->getUrl());

		$r->setMethod('post');
		$this->assertEquals('post', $r->getMethod());

		$r->setData(array('foo' => 'bar'));
		$this->assertEquals(array('foo' => 'bar'), $r->getData());

		$r->setOptions(array('bar' => 'baz'));
		$this->assertEquals(array('bar' => 'baz'), $r->getOptions());

		$r->setHeaders(array('baz' => 'foo'));
		$this->assertEquals(array('baz' => 'foo'), $r->getHeaders());

		$r->setHeader('bar', 'baz');
		$this->assertEquals(array('baz' => 'foo', 'bar' => 'baz'), $r->getHeaders());
	}

	#[Test]
	public function encodeData()
	{
		$r = $this->makeRequest();
		$r->setMethod('post');

		$r->setData(array('foo' => 'bar', 'bar' => 'baz'));
		$this->assertEquals('foo=bar&bar=baz', $r->encodeData());

		$r->setEncoding(Request::ENCODING_JSON);
		$this->assertEquals('{"foo":"bar","bar":"baz"}', $r->encodeData());

		$r->setEncoding(Request::ENCODING_RAW);
		$r->setData('<rawData>ArbitraryValue</rawData>');
		$this->assertEquals('<rawData>ArbitraryValue</rawData>', $r->encodeData());
	}

	#[Test]
	public function formatHeaders()
	{
		$r = $this->makeRequest();

		$r->setHeaders(array('foo' => 'bar', 'bar' => 'baz'));
		$this->assertEquals(array('foo: bar', 'bar: baz'), $r->formatHeaders());

		$r->setHeaders(array('foo: bar', 'bar: baz'));
		$this->assertEquals(array('foo: bar', 'bar: baz'), $r->formatHeaders());
	}

	#[Test]
	public function headersAreCaseInsensitive()
	{
		$r = $this->makeRequest();

		$r->setHeader('foo', 'bar');
		$r->setHeader('Foo', 'bar');
		$r->setHeader('FOO', 'bar');
		$this->assertEquals(array('foo' => 'bar'), $r->getHeaders());
	}

	#[Test]
	public function invalidMethod()
	{
		$this->expectException("InvalidArgumentException");
		$this->makeRequest()->setMethod('foo');
	}

	#[Test]
	public function invalidEncoding()
	{
		$this->expectException("InvalidArgumentException");
		$this->makeRequest()->setEncoding(999);
	}

	#[Test]
	public function userAndPass()
	{
		$r = $this->makeRequest();
		$this->assertEquals(null, $r->getUserAndPass());
		$r->setUser('foo');
		$this->assertEquals('foo:', $r->getUserAndPass());
		$r->setPass('bar');
		$this->assertEquals('foo:bar', $r->getUserAndPass());
	}

	#[Test]
	public function cookies()
	{
		$r = $this->makeRequest();

		$this->assertEquals(array(), $r->getCookies());

		$r->setCookie('foo', 'bar');
		$this->assertEquals(array('foo' => 'bar'), $r->getCookies());
		$this->assertEquals('bar', $r->getCookie('foo'));
		$this->assertEquals('foo=bar', $r->getHeader('cookie'));

		$r->setCookie('bar', 'baz');
		$this->assertEquals(array('foo' => 'bar', 'bar' => 'baz'), $r->getCookies());
		$this->assertEquals('baz', $r->getCookie('bar'));
		$this->assertEquals('foo=bar; bar=baz', $r->getHeader('cookie'));

		$r->setCookies(array('baz' => 'foo'));
		$this->assertEquals(array('baz' => 'foo'), $r->getCookies());
		$this->assertEquals('foo', $r->getCookie('baz'));
		$this->assertEquals('baz=foo', $r->getHeader('cookie'));
	}

	#[Test]
	public function emptyJsonGetRequestHasNoData()
	{
		$r = $this->makeRequest();
		$r->setEncoding(Request::ENCODING_JSON);
		$r->setMethod('get');

		$this->assertFalse($r->hasData());
	}
}
