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

			public static function check_data($arr, $keys) {
				foreach ($keys as $key) {
					if(!array_key_exists($key, $arr)) {
						return false;
					}
				}
				foreach ($arr as $key => $value) {
					if ($value == "" || $value == " ") {
						return false;
					}
				}

				return true;
			}
		}

	}
