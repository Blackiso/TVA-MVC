<?php 
	namespace Controllers {

		use Views\View as View;
		use Models\Authentication as AuthModel;

		class Authentication extends Controller  { 

			protected static function POST_login() {
				$user_data = self::$request->body;
				$user = new \User();

				if (!self::check_data($user_data, ['email', 'password'])) {
					View::bad_request();
				}

				if ($user->init_email($user_data->email)) {
					$secret = $user->secret;
					$user_data->password = self::encrypt_password($user_data->password, $secret);
					if ($user_data->password == $user->password) {
						if ($user->blocked == true) View::throw_error('account_blocked');
						$user->new_jwt();
						$info = $user->info;
						View::response($info);
					}else {
						self::auth_error();
					}
				}else {
					self::auth_error();
				}
			}

			protected static function POST_register() {
				$user = new \User();
				$user_ip = self::$request->ip;
				$user_agent = self::$request->agent;
				$account_type = "premium";
				$user_data = self::$request->body;

				if (!self::check_data($user_data, ['name','email', 'password'])) {
					View::bad_request();
				}

				if (!filter_var($user_data->email, FILTER_VALIDATE_EMAIL)) {
					View::bad_request();
				}

				if (AuthModel::check_email_exist($user_data->email)) {
					View::throw_error('email_used');
				}

				if (!self::filter_password($user_data->password)) {
					View::throw_error('password');
				}

				$secret = self::generate_secret($user_data->email);
				$user_data->password = self::encrypt_password($user_data->password, $secret);

				$register_data = [
					'name' => $user_data->name,
					'email' => $user_data->email,
					'password' => $user_data->password,
					'secret' => $secret,
					'account_type' => $account_type,
					'user_agent' => $user_agent,
					'user_ip' => $user_ip,
				];

				$register = AuthModel::register_user($register_data);
				if ($register['registred']) {
					$user_data->user_id = $register['user_id'];
					$user_data->account_type = $account_type;
					$user_data->user_type = 'matser';
					$user_data->secret = $secret;
					if ($user->init_data($user_data)) {
						$user->new_jwt(false);
						$info = $user->info;
						View::created($info);
					}
				}
			}

			protected static function POST_logout() {
				self::$user->revoke_secret();
				self::$user->remove_JWT();
				View::response();
			}

			protected static function GET_authenticate() {
				self::$user->new_jwt();
				$info = self::$user->info;
				View::response($info);
			}

			private static function auth_error() {
				View::throw_error('authentication');
			}
		}
	}
