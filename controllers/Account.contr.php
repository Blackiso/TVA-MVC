<?php 

	namespace Controllers {

		use Models\Account as AccountModel;
		use Views\View as View;

		class Account extends Controller  {
		
			public static function PATCH_index() {
				$data = self::$request->body;
				if(!self::check_data($data, ['name', 'email', 'new_password', 'password'], false)) View::bad_request();
				if(!isset($data->password)) View::bad_request();

				$secret = explode('-', self::$user->secret)[0];
				$x_password = self::encrypt_password($data->password, $secret);
				if ($x_password !== self::$user->password) View::unauthorized();

				if (isset($data->email)) {
					if (!self::check_email($data->email)) View::bad_request();
				}

				if (isset($data->new_password)) {
					if (!self::filter_password($data->new_password)) View::bad_request();
				}

				unset($data->password);
				$update = [];

				foreach ($data as $key => $value) {
					if ($key == 'new_password') {
						$update['password'] = self::encrypt_password($data->new_password, $secret);
					}else {
						$update[$key] = $value;
					}
				}

				AccountModel::update_account($update, self::$user->user_id);
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

			private static function convertFormat($date) {
				setlocale(LC_TIME, "fr_FR");
				$time = strtotime($date);
				return strftime('le %d %B, %Y', $time);
			}

		}
	}
