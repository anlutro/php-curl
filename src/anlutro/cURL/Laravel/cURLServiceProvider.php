<?php
/**
 * PHP OOP cURL
 * 
 * @author   Andreas Lutro <anlutro@gmail.com>
 * @license  http://opensource.org/licenses/MIT
 * @package  PHP cURL
 */

namespace anlutro\cURL\Laravel;

use Illuminate\Support\ServiceProvider;

class cURLServiceProvider extends ServiceProvider
{
	protected $defer = true;

	public function register() {}
}
