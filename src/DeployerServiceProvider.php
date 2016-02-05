<?php

	namespace MikeVrind\Deployer;

	use Illuminate\Routing\Router;
	use Illuminate\Support\ServiceProvider;

	class DeployerServiceProvider extends ServiceProvider
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
			$configPath = __DIR__ . '/../config/deployer.php';
			$this->mergeConfigFrom( $configPath, 'deployer' );

			$this->app['command.deployer.deploy'] = $this->app->share(
				function ($app) {
					return new Console\DeployCommand();
				}
			);
			$this->commands(array('command.debugbar.clear'));

		}

		/**
		 * Bootstrap the application events.
		 *
		 * @return void
		 */
		public function boot()
		{

			$configPath = __DIR__ . '/../config/deployer.php';
			$this->publishes( [ $configPath => config_path( 'deployer.php' ) ], 'config' );

			$routeConfig = [
				'namespace'  => 'MikeVrind\Deployer\Controllers',
				'prefix'     => '_deployer',
				'middleware' => 'MikeVrind\Deployer\Middleware\DeployerMiddleware',
			];

			$this->getRouter()->group( $routeConfig, function ( $router )
			{
				$router->post( 'deploy', [
					'uses' => 'DeployController@handle',
					'as'   => 'deployer.deployhandler',
				] );

			} );

			# Tell Laravel where to load the views from
			$this->loadViewsFrom( __DIR__ . '/Views', 'deployer' );

		}

		/**
		 * Get the active router.
		 *
		 * @return Router
		 */
		protected function getRouter()
		{
			return $this->app['router'];
		}

		/**
		 * Get the services provided by the provider.
		 *
		 * @return array
		 */
		public function provides()
		{
			return array('deployer', 'command.deployer.deploy');
		}

	}
