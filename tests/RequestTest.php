<?php
namespace anlutro\cURL\Tests;

use PHPUnit_Framework_TestCase;
use anlutro\cURL\Request;

class RequestTest extends PHPUnit_Framework_TestCase
{
	public function testSettersAndGetters()
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

	public function testEncodeData()
	{
		$r = $this->makeRequest();

		$r->setData(array('foo' => 'bar', 'bar' => 'baz'));
		$this->assertEquals('foo=bar&bar=baz', $r->encodeData());

		$r->setEncoding(Request::ENCODING_JSON);
		$this->assertEquals('{"foo":"bar","bar":"baz"}', $r->encodeData());

		$r->setEncoding(Request::ENCODING_RAW);
		$r->setData('<rawData>ArbitraryValue</rawData>');
		$this->assertEquals('<rawData>ArbitraryValue</rawData>', $r->encodeData());
	}

	public function testJsonConvenienceMethod()
	{
		$r = $this->makeRequest();

		$r->setJson(true);
		$this->assertEquals(Request::ENCODING_JSON, $r->getEncoding());
	}

	public function testFormatHeaders()
	{
		$r = $this->makeRequest();

		$r->setHeaders(array('foo' => 'bar', 'bar' => 'baz'));
		$this->assertEquals(array('foo: bar', 'bar: baz'), $r->formatHeaders());

		$r->setHeaders(array('foo: bar', 'bar: baz'));
		$this->assertEquals(array('foo: bar', 'bar: baz'), $r->formatHeaders());
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidMethod()
	{
		$r = $this->makeRequest();

		$r->setMethod('foo');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidEncoding()
	{
		$r = $this->makeRequest();

		$r->setEncoding(999);
	}

	/** @test */
	public function userAndPass()
	{
		$r = $this->makeRequest();
		$this->assertEquals(null, $r->getUserAndPass());
		$r->setUser('foo');
		$this->assertEquals(null, $r->getUserAndPass());
		$r->setPass('bar');
		$this->assertEquals('foo:bar', $r->getUserAndPass());
	}

	public function makeRequest($curl = null)
	{
		return new Request($curl ?: new \anlutro\cURL\cURL);
	}
}
