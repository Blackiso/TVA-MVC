<?php 

	namespace Controllers {

		use Models\Users as UsersModel;
		use Models\Companies as CompaniesModel;
		use Views\View as View;

		class Users extends Controller  {
			
			public static function POST_index() {
				$user_data = self::$request->body;
				if(!self::check_data($user_data, ['email', 'password', 'company', 'name'])) {
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

				if (!self::check_master_company($user_data->company)) {
					View::bad_request();
				}

				if (UsersModel::count_users($user_data->company) >= 20) {
					View::bad_request();
				}

				$user_data->user_id = UsersModel::generate_id();
				$user_data->master_id = self::$user->user_id;
				$user_data->name = self::clear_str($user_data->name);
				$user_data->secret = self::generate_secret($user_data->email);
				$user_data->password = self::encrypt_password($user_data->password, $user_data->secret);
				$company_id = $user_data->company;
				unset($user_data->company);

				if (UsersModel::add_user($user_data)) {
					CompaniesModel::add_user_company($company_id, $user_data->user_id);
					$user_data->account_type = "user";
					unset($user_data->password);
					unset($user_data->secret);
					unset($user_data->master_id);
					$user_data->blocked = 0;
					View::created($user_data);
				}
			}

			public static function POST_add_users() {
				$company_id = self::$request->body->companies;
				$user_id = self::$params['user-id'];
				if (!self::check_master_company($company_id)) View::bad_request();
				if (self::check_user_company($company_id, $user_id)) View::throw_error('company_set');
				self::check_user($user_id);
				CompaniesModel::add_user_company($company_id, $user_id);
				View::response();
			}

			public static function GET_all_users() {
				$company_id = self::$params['company-id'];
				$master_id = self::$user->master_id;
				if (!self::check_master_company($company_id)) View::bad_request();
				$data = UsersModel::get_users_by_company($company_id, $master_id);
				if (!empty($data) && !isset($data[0])) $data = [$data];
				View::response($data);
			}

			public static function DELETE_users() {
				$user_id = self::$params['user-id'];
				$company_id = self::$request->body->companies;
				if (!self::check_master_company($company_id)) View::bad_request();
				if (!self::check_user_company($company_id, $user_id)) View::bad_request();
				CompaniesModel::delete_company_from_user($company_id, $user_id);
				View::response();
			}

			public static function POST_block_user() {
				$user_id = self::$params['user-id'];
				self::check_user($user_id);
				UsersModel::block_user($user_id, 1);
				View::response();
			}


			public static function POST_unblock_user() {
				$user_id = self::$params['user-id'];
				self::check_user($user_id);
				UsersModel::block_user($user_id, 0);
				View::response();
			}

			public static function GET_search_users() {
				$keyword = self::$request->querys['keyword'] ?? View::bad_request();
				$master_id = self::$user->master_id;
				$result = UsersModel::search_user($master_id, $keyword);
				if (!empty($result) && !isset($result[0])) $result = [$result];
				View::response($result);
			}

			public static function PATCH_update_user() {
				$user_id = self::$params['user-id'];
				$user_data = self::$request->body;
				self::check_user($user_id);
				if(!self::check_data($user_data, ['email', 'password', 'name'], false)) View::bad_request();
				if (isset($user_data->email)) {
					if (!self::filter_email($user_data->email)) {
						View::bad_request();
					}
					if (UsersModel::check_email_exist($user_data->email)) {
						View::throw_error('email_used');
					}
				}
				if (isset($user_data->password)) {
					if (!self::filter_password($user_data->password)) {
						View::throw_error('password');
					}
					$user_secret = UsersModel::get_secret($user_id)['secret'];
					$user_secret = explode('-', $user_secret)[0];
					$user_data->password = self::encrypt_password($user_data->password, $user_secret);
				}
				UsersModel::update_user($user_id, $user_data);
				View::response();
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

			private static function check_user($user_id) {
				$master_id = self::$user->master_id;
				if (!UsersModel::check_user($user_id, $master_id)) {
					View::bad_request();
				}
			}
		}
	}
