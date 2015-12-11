<?php namespace Pipindex\Salesforce;

use Illuminate\Support\ServiceProvider;
use \Omniphx\Forrest\Providers\Laravel\ForrestServiceProvider;
use Pipindex\Salesforce\Authentications\FileStorage;
use Config;

class SalesforceServiceProvider extends ForrestServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		parent::boot();

		$this->package('pipindex/salesforce');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['salesforce'] = $this->app->share(function($app)
		{
			$settings  = Config::get('forrest::config');

			$client   = new \GuzzleHttp\Client();
			$redirect = new \Omniphx\Forrest\Providers\Laravel\LaravelRedirect();

            $path = $settings['tokenfile'];;
            $files = $this->app['files'];

			$storage  = new FileStorage($files, $path);
			$input    = new \Omniphx\Forrest\Providers\Laravel\LaravelInput();
			$event    = new \Omniphx\Forrest\Providers\Laravel\LaravelEvent();

			$authentication = '\\Pipindex\\Salesforce\\Authentications\\';
			$authentication .= $settings['authentication'];

			return new $authentication($client, $storage, $redirect, $input, $event, $settings);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
