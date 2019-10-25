<?php 

	namespace Controllers {

		use Models\Account as AccountModel;
		use Views\View as View;

		class Account extends Controller  {

			private static $domain_ = "http://localhost:4200/reset";
			private static $domain = "https://paramanagers.com/reset";
		
			public static function PATCH_index() {
				$data = self::$request->body;
				$update = [];
				if(!self::check_data($data, ['name', 'email', 'new_password', 'password'], false)) View::bad_request();
				if(!isset($data->password)) View::bad_request();

				$secret = explode('-', self::$user->secret)[0];
				$x_password = self::encrypt_password($data->password, $secret);
				if ($x_password !== self::$user->password) View::unauthorized();

				if (isset($data->email)) {
					if (!self::check_email($data->email)) View::bad_request();
					$update['active'] = 0;
				}

				if (isset($data->new_password)) {
					if (!self::filter_password($data->new_password)) View::bad_request();
				}

				unset($data->password);

				foreach ($data as $key => $value) {
					if ($key == 'new_password') {
						$update['password'] = self::encrypt_password($data->new_password, $secret);
					}else {
						$update[$key] = $value;
					}
				}

				AccountModel::update_account($update, self::$user->user_id);
				self::$user->new_jwt();
				View::response();
			}

			public static function GET_index() {
				$data = AccountModel::plan_details(self::$user->master_id);
				$exp = strtotime($data['expire_date']);
				$data['start_date'] = mb_convert_encoding(self::convertFormat($data['start_date']), "UTF-8", "HTML-ENTITIES");
				$data['expire_date'] = mb_convert_encoding(self::convertFormat($data['expire_date']), "UTF-8", "HTML-ENTITIES");
				$data['time_left'] = (int)(($exp - time())/86400);
				View::response($data);
			}

			public static function POST_index() {
				$data = self::$request->body;
				if(!self::check_data($data, ['name', 'email', 'subject', 'body'])) View::bad_request();
				if (!self::check_email($data->email)) View::bad_request();
				$mailer = new \SendMail();
				$mailer->send_support_mail($data->body, $data->email, $data->name, $data->subject);
				View::response();
			}

			public static function POST_send_email() {
				$user_id = self::$user->master_id;
				if (self::$user->active) View::response();
				$code = AuthModel::set_activation_code($user_id);
				$name = self::$user->name;

				$message = file_get_contents('email_templates/email_verification.html');
				eval('$message = \''.$message.'\';');

				$mailer = new \SendMail();
				$mailer->send_html($message, self::$user->email, 'Email Activation', $code);
				View::response();
			}

			public static function POST_verify_email() {
				$code = self::$request->body->code ?? View::bad_request();
				$user_id = self::$user->master_id;
				if (self::$user->active) View::response();
				$verification = AuthModel::get_activation_code($user_id, $code);
				if ($verification) {
					AuthModel::set_active($user_id, $code);
					self::$user->active = true;
					self::$user->new_jwt();
					$info = self::$user->info;
					View::response($info);
				}else {
					View::response(['verification' => false]);
				}
			}

			public static function POST_reset_password() {
				$data = self::$request->body;
				$ip = self::$request->ip;
				$time = time();
				$user = new \User();

				if (!self::check_data($data, ['email'])) View::bad_request();
				if (!$user->init_email($data->email)) View::bad_request();
				if ($user->user_type !== "master") View::bad_request();

				$secret = $user->secret;
				$hash = hash_hmac('sha256', $data->email."-".$ip."-".$time, $secret);
				$URL  = self::$domain."?key=".$hash."&email=".$data->email."&cts=".$time;
				$message = file_get_contents('email_templates/reset_password.html');
				eval('$message = \''.$message.'\';');
				$mailer = new \SendMail();
				$mailer->send_html($message, $data->email, 'Réinitialisation du mot de passe', $URL);
				View::response();
			}

			public static function PATCH_reset_password() {
				$data = self::$request->body;
				$ip = self::$request->ip;
				$time = time();
				$user = new \User();
			
				if (!self::check_data($data, ['email', 'key', 'password', 'cts'])) View::bad_request();
				if (!$user->init_email($data->email)) View::bad_request();

				$secret = $user->secret;
				$hash = hash_hmac('sha256', $data->email."-".$ip."-".$data->cts, $secret);
				if ($hash !== $data->key) View::bad_request();
				if (($time - $data->cts) > 900) View::bad_request();

				if ($user->user_type !== "master") View::bad_request();

				if (!self::filter_password($data->password)) View::bad_request();
				$password = self::encrypt_password($data->password, $secret);
				AccountModel::update_account(['password' => $password], $user->user_id);
				View::response();
			}

			private static function convertFormat($date) {
				setlocale(LC_TIME, "fr_FR");
				$time = strtotime($date);
				return strftime('le %d %B, %Y', $time);
			}

		}
	}
