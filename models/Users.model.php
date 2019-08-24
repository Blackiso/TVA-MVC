<?php 
	namespace Models {

		class Users extends Model {

			private static $user = 'user_accounts';
			private static $companies_table = 'users_companies';

			public static function generate_id() {
				return self::generate_unique_ids(self::$user, 'user_id');
			}

			public static function check_email_exist($email) {
				return self::check_row(self::$user, ['email' => $email]);
			}

			public static function get_secret($id) {
				$get_query = self::select_query_constructor(['secret'], self::$user, ['user_id' => $user_id]);
				return self::$database->select($get_query);
			}

			public static function add_user($data) {
				$user_query = self::insert_query_constructor($data, self::$user);
				$result = self::$database->insert($user_query);
				return $result->query;
			}
			
			public static function get_users_by_company($company_id, $master_id) {
				$users_query = "SELECT u.user_id, u.name, u.email, u.blocked FROM ".self::$user." AS u INNER JOIN ".self::$companies_table." AS c WHERE u.user_id = c.user_id AND c.id = '$company_id' AND u.master_id = '$master_id' ORDER BY user_id DESC";
				return self::$database->select($users_query);
			}

			public static function add_users_to_company($company_id, $user_id) {
				$update_query = self::update_query_constructor(
					['companies' => ','.$company_id], 
					self::$user, 
					['user_id' => $user_id]
				);
				$str_id = strval($company_id);
				$concat = "concat(companies,',$str_id')";				
				$update_query = str_replace("',$company_id'", $concat, $update_query);
				self::$database->update($update_query);
			}

			public static function get_companies($user_id) {
				$get_query = self::select_query_constructor(['companies'], self::$user, ['user_id' => $user_id]);
				return self::$database->select($get_query);
			}

			public static function search_user($master_id, $keyword) {
				$search_query = self::search_query_constructor(['email', 'user_id', 'name'], self::$user, ['name' => $keyword], ['master_id' => $master_id]);
				return self::$database->select($search_query);
			}

			public static function update_user($user_id, $data) {
				$update = self::update_query_constructor($data, self::$user, ['user_id' => $user_id]);
				self::$database->update($update);
			}

			public static function check_user($user_id, $master_id) {
				return self::check_row(self::$user, ['user_id' => $user_id, 'master_id' => $master_id]);
			}
		}
	}