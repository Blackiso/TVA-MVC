<?php 
	namespace Models {

		class Users extends Model {

			private static $user = 'user_accounts';
			private static $custom_query = " WHERE companies REGEXP '(?<![0-9]):comp-id(?![0-9])'";

			public static function generate_id() {
				return self::generate_unique_ids(self::$user, 'user_id');
			}

			public static function check_email_exist($email) {
				return self::check_row(self::$user, ['email' => $email]);
			}

			public static function add_user($data) {
				$user_query = self::insert_query_constructor($data, self::$user);
				$result = self::$database->insert($user_query);
				return $result->query;
			}
			
			public static function get_users_by_company($company_id, $master_id) {
				$users_query = self::select_query_constructor(['user_id', 'name', 'email', 'blocked'], self::$user);
				$users_query = $users_query.str_replace(':comp-id', $company_id, self::$custom_query)." AND master_id='$master_id'";
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

			public static function delete_company_from_user($company_id, $user_id) {
				$update_query = self::update_query_constructor(
					['companies' => $company_id], 
					self::$user, 
					['user_id' => $user_id]
				);
				$str_id = strval($company_id);
				$dlt = "REPLACE(companies, '$str_id','')";				
				$update_query = str_replace("'$company_id'", $dlt, $update_query);
				self::$database->update($update_query);
			}

			public static function check_user_company($company_id, $user_id) {
				$check_query = self::select_query_constructor(['user_id'], self::$user, ['user_id' => $user_id]);
				$custom_query = str_replace('WHERE', 'AND', self::$custom_query);
				$check_query = $check_query.str_replace(':comp-id', $company_id, $custom_query);
				$result = self::$database->select($check_query);
				if (!empty($result)) {
					return true;
				}
				return false;
			}

			public static function get_companies($user_id) {
				$get_query = self::select_query_constructor(['companies'], self::$user, ['user_id' => $user_id]);
				return self::$database->select($get_query);
			}

			public static function search_user($master_id, $keyword) {
				$search_query = self::search_query_constructor(['email', 'user_id', 'name'], self::$user, ['email' => $keyword], ['master_id' => $master_id]);
				return self::$database->select($search_query);
			}

			public static function check_user($user_id, $master_id) {
				return self::check_row(self::$user, ['user_id' => $user_id, 'master_id' => $master_id]);
			}
		}
	}