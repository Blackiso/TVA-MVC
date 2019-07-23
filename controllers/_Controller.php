<?php 
	namespace Controllers {

		use Views\view as View;

		abstract class Controller {

			private static $default = 'index';
			protected static $user;
			protected static $request;
			
			public static function init($request, $user, $controller, $action, $params) {
				\Models\Model::init();
				self::$request = $request;
				self::$user = $user;

				if ($action !== null) {
					$action = self::construct_action($action);

					if (method_exists($controller, $action)) {
						$controller::$action();
					}else {
						self::not_found();
					}
				}else {
					$controller::$default();
				}
			}

			private static function construct_action($action) {
				return self::$request->__get('method').'_'.$action;
			}

			protected static function not_found() {
				View::not_found();
			}
		}

	}
