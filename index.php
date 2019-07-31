<?php
	header('Content-Type: application/json');
	header("Access-Control-Allow-Credentials: true");
	header("Access-Control-Allow-Origin: http://localhost");
	header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE");
	header("Access-Control-Allow-Headers: Authorization, *");

	class Main {

		private static $request;
		private static $router;
		private static $authenticate;
		private static $controller;
		private static $action;
		private static $user;
		private static $uri_params;

		public static function dispatch() {
			define('MAINDIR', dirname(__FILE__));

			self::auto_loader();
			self::$request = new Request();
			self::$router = new Router(self::$request->uri);
			self::init_controller();
		}

		private static function init_controller() {
			self::$controller = 'Controllers\\'.self::$router->controller;
			self::$action = self::$router->action;
			self::$uri_params = self::$router->params;
			
			if (class_exists(self::$controller)) {
				if (self::$router->__get('auth') !== false) {
					self::$authenticate = new Authenticate(self::$request);
					if (self::$authenticate->is_auth) {
						if(!self::$authenticate->check_user_type(self::$router->types)) {
							Views\View::unauthorized();
						}
						self::$user = self::$authenticate->user;
						self::controller();
					}else {
						Views\View::not_authenticated();
					}
				}else {
					self::controller();
				}
				
			}else {
				Views\View::not_found();
			}	
		}

		private static function controller() {
			self::$controller::init(self::$request, self::$user, self::$action, self::$uri_params);
		}

		private static function auto_loader() {
			$paths = ['classes', 'controllers', 'models', 'views'];
			foreach ($paths as $path) {
				$arr_path = scandir($path);
				rsort($arr_path);
				foreach($arr_path as $arr) {
					if (strpos($arr, '.php') !== false) {
						$x = $path . '\\' .$arr;
						require_once($x);
					}
				}
			}
		}

	}

	Main::dispatch();