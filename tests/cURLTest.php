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

	public function makeCurl()
	{
		return new \anlutro\cURL\cURL;
	}
}
