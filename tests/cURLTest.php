<?php
class cURLTest extends PHPUnit_Framework_TestCase
{
	public function testGoogle()
	{
		$curl = $this->makeCurl();

		$r = $curl->get('https://www.google.com');

		$this->assertNotNull($r->body);
		$this->assertNotNull($r->headers);
		$this->assertNotNull($r->info);
	}

	public function makeCurl()
	{
		return new \anlutro\cURL\cURL;
	}
}
