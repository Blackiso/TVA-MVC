<?php 
	namespace Controllers {

		use Views\View as View;
		use Models\Authentication as AuthModel;
		use Models\Payment as PaymentModel;

		class Authentication extends Controller  { 

			public static function POST_login() {
				$user_data = self::$request->body;
				$user = new \User();

				if (!self::check_data($user_data, ['email', 'password'])) {
					View::bad_request();
				}

				if ($user->init_email($user_data->email)) {
					$secret = $user->secret;
					$user_data->password = self::encrypt_password($user_data->password, $secret);
					if ($user_data->password == $user->password) {
						$user->check_expire();
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

			public static function POST_register() {
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

				$user_id = AuthModel::generate_id();
				$secret = self::generate_secret($user_data->email);
				$user_data->password = self::encrypt_password($user_data->password, $secret);

				$register_data = [
					'user_id' => $user_id,
					'name' => $user_data->name,
					'email' => $user_data->email,
					'password' => $user_data->password,
					'secret' => $secret,
					'account_type' => $account_type,
					'user_agent' => $user_agent,
					'user_ip' => $user_ip,
				];

				if (AuthModel::register_user($register_data)) {
					$user_data->user_id = $user_id;
					$user_data->account_type = $account_type;
					$user_data->user_type = 'matser';
					$user_data->secret = $secret;
					if ($user->init_data($user_data)) {
						self::set_free_plan($user_id);
						$user->new_jwt(false);
						$info = $user->info;
						View::created($info);
					}
				}
			}

			public static function POST_logout() {
				self::$user->revoke_secret();
				self::$user->remove_JWT();
				View::response();
			}

			public static function GET_authenticate() {
				self::$user->new_jwt();
				$info = self::$user->info;
				View::response($info);
			}

			private static function auth_error() {
				View::throw_error('authentication');
			}

			private static function set_free_plan($user_id) {
				$expire_date = strtotime("+7 days");
				$expire_date = date("Y-m-d H:i:s", $expire_date);
				$active_plan = [
					'master_id' => $user_id,
					'payment_id' => 'FREEMIUM',
					'reference_id' => 'free_plan',
					'duration' => 0,
					'start_date' => date('Y-m-d h:i:s'),
					'expire_date' => $expire_date
				];

				PaymentModel::set_active_plan($active_plan);
			}
		}
	}
