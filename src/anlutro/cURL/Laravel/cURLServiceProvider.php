<?php
namespace anlutro\cURL\Laravel;

use anlutro\cURL\cURL;
use Illuminate\Support\ServiceProvider;

class cURLServiceProvider extends ServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['curl'] = $this->app->share(function($app) {
			return new cURL;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('curl');
	}

}
