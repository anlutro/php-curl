<?php
namespace anlutro\cURL\Laravel;

use Illuminate\Support\Facades\Facade;

class cURL extends Facade
{
	public static function getFacadeAccessor()
	{
		return 'curl';
	}
}
