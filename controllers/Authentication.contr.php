<?php 
	namespace Controllers {

		use Views\View as View;
		use models\Authentication as AuthModel;

		class Authentication extends Controller  {

			private static $encryption = 'sha256';

			protected static function post_login() {
				
				$user_data = self::$request->__get('body');
				$user = new \User();

				if (!isset($user_data->email) || !isset($user_data->password) || !self::check_email($user_data->email)) {
					View::bad_request();
				}

				if ($user->init_email($user_data->email)) {
					$secret = $user->__get('secret');
					$user_data->password = self::encrypt_password($user_data->password, $secret);
					if ($user_data->password == $user->__get('password')) {
						if ($user->__get('blocked') == true) View::throw_error('Your account is blocked!');

						echo "string";
					}else {
						self::auth_error();
					}
				}else {
					self::auth_error();
				}
			}

			protected static function post_regitser() {
				
			}

			protected static function post_logout() {
				
			}

			protected static function get_autenticate() {
				
			}

			private static function encrypt_password($pass, $secret) {
				$secret = explode('-', $secret)[0];
				return hash_hmac(self::$encryption, $pass, $secret);
			}

			private static function check_email($email) {
				return filter_var($email, FILTER_VALIDATE_EMAIL);
			}

			private static function auth_error() {
				View::throw_error('Authentication Error!');
			}
		}
	}
