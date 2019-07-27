<?php 

	namespace Controllers {

		use Models\Users as UsersModel;
		use Views\View as View;

		class Users extends Controller  {
			
			public static function post_index() {
				$user_data = self::$request->__get('body');
				if(!self::check_data($user_data, ['email', 'password', 'companies', 'name'])) {
					View::bad_request();
				}

				if (!self::filter_email($user_data->email)) {
					View::bad_request();
				}

				if (UsersModel::check_email_exist($user_data->email)) {
					View::throw_error('email_used');
				}

				if (!self::filter_password($user_data->password)) {
					View::throw_error('password');
				}

				$user_data->master_id = self::$user->__get('user_id');
				$user_data->name = self::clear_str($user_data->name);
				$user_data->secret = self::generate_secret($user_data->email);
				$user_data->password = self::encrypt_password($user_data->password, $user_data->secret);

				$result = UsersModel::add_user($user_data);
				if ($result['added']) {
					$new_user = [
						"user_id" => $result['user_id'],
						"name" => $user_data->name,
						"email" => $user_data->email,
						"blocked" => false,
					];

					View::created($new_user);
				}
			}

			public static function get_all_users() {
				$company_id = self::$params['company-id'];
				$master_id = self::$user->__get('master_id');
				$data = UsersModel::get_users_by_company($company_id, $master_id);
				View::response($data);
			}

			private static function filter_email($email) {
				if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					return false;
				}
				if (!preg_match('/(\.edg)$/', $email)) {
					return false;
				}
				return true;
			}
		}
	}
