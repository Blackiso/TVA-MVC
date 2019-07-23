<?php 
	namespace Controllers {

		use Views\View as View;
		use models\Authentication as AuthModel;

		class Authentication extends Controller  {

			protected static function post_login() {
				
				$user_data = self::$request->__get('body');
				$user = new \User();

				if (!isset($user_data->email) || !isset($user_data->password)) {
					View::bad_request();
				}

				$type = preg_match('/(\.edg)$/', $user_data->email) ? 'user' : 'master';
				$user->__set('user_type', $type);

				$secret = $user->__get('secret');

				if ($user->check_user($user_data)) {
					echo "string";
				}

				//New aproche to validate user creds!!!
				//Init user with email!!!

			}

			protected static function post_regitser() {
				
			}

			protected static function post_logout() {
				
			}

			protected static function get_autenticate() {
				
			}
		}
	}
