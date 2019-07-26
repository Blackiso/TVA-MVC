<?php 
	namespace Controllers {

		use Views\View as View;
		use Models\Authentication as AuthModel;

		class Authentication extends Controller  {

			private static $encryption = 'sha256';
			private static $pass_regex = '/^(?=.*[A-Za-z])(?=.*[0-9])[A-Za-z\d@$!%*#?é^&â ]{5,20}$/';

			private static $errors = [
				'password' => 'Password Requires a Mix of Numbers and Letters Min Characters 5 and Max Characters 20',
				'email_used' => 'Email already used!',
				'account_blocked' => 'Your account is blocked!',
				'authentication' => 'Authentication Error!'
			]; 

			protected static function post_login() {
				$user_data = self::$request->__get('body');
				$user = new \User();

				if (!self::check_data($user_data, ['email', 'password'])) {
					View::bad_request();
				}

				if ($user->init_email($user_data->email)) {
					$secret = $user->__get('secret');
					$user_data->password = self::encrypt_password($user_data->password, $secret);
					if ($user_data->password == $user->__get('password')) {
						if ($user->__get('blocked') == true) View::throw_error(self::$errors['account_blocked']);
						$user->new_jwt();
						$info = $user->__get('info');
						View::response($info);
					}else {
						self::auth_error();
					}
				}else {
					self::auth_error();
				}
			}

			protected static function post_register() {
				$user = new \User();
				$user_ip = self::$request->__get('ip');
				$user_agent = self::$request->__get('agent');
				$account_type = "premium";
				$user_data = self::$request->__get('body');

				if (!self::check_data($user_data, ['name','email', 'password'])) {
					View::bad_request();
				}

				if (!filter_var($user_data->email, FILTER_VALIDATE_EMAIL)) {
					View::bad_request();
				}

				if (AuthModel::check_email_exist($user_data->email)) {
					View::throw_error(self::$errors['email_used']);
				}

				if (!preg_match(self::$pass_regex, $user_data->password)) {
					View::throw_error(self::$errors['password']);
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
						$info = $user->__get('info');
						View::response($info);
					}
				}
			}

			protected static function post_logout() {
				self::$user->revoke_secret();
				self::$user->remove_JWT();
				View::response();
			}

			protected static function get_authenticate() {
				self::$user->new_jwt();
				$info = self::$user->__get('info');
				View::response($info);
			}

			private static function encrypt_password($pass, $secret) {
				$secret = explode('-', $secret)[0];
				return hash_hmac(self::$encryption, $pass, $secret);
			}

			private static function generate_secret($salt) {
				$rand_num = time();
				$secret = hash_hmac('sha256', $salt, $rand_num);
				$add_secret = uniqid("KY");
				return $secret."-".$add_secret;
			}

			private static function check_email($email) {
				return filter_var($email, FILTER_VALIDATE_EMAIL);
			}

			private static function auth_error() {
				View::throw_error(self::$errors['authentication']);
			}
		}
	}
