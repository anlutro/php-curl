<?php

use Mockery as m;

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

		$r->setJson(true);
		$this->assertEquals('{"foo":"bar","bar":"baz"}', $r->encodeData());
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidMethod()
	{
		$r = $this->makeRequest();

		$r->setMethod('foo');
	}

	public function makeRequest($curl = null)
	{
		if ($curl === null) {
			// $curl = m::mock('anlutro\cURL\cURL');
			$curl = new anlutro\cURL\cURL;
		}

		return new anlutro\cURL\Request($curl);
	}
}
