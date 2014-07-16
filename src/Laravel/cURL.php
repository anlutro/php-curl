<?php
/**
 * PHP OOP cURL
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  PHP cURL
 */

namespace anlutro\cURL\Laravel;

use Illuminate\Support\Facades\Facade;

/**
 * cURL facade class.
 */
class cURL extends Facade
{
	public static function getFacadeAccessor()
	{
		return 'anlutro\cURL\cURL';
	}
}
