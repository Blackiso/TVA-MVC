<?php 
	namespace Models {

		class Users extends Model {

			private static $user = 'user_accounts';

			public static function check_email_exist($email) {
				return self::check_row(self::$user, ['email' => $email]);
			}

			public static function add_user($data) {
				$user_query = self::insert_query_constructor($data, self::$user);
				$result = self::$database->insert($user_query);
				return ['user_id' => $result->insert_id, 'added' => $result->query];
			}
			
			public static function get_users_by_company($company_id, $master_id) {
				$users_query = self::select_query_constructor(['user_id', 'name', 'email', 'blocked'], self::$user);
				$users_query = $users_query." WHERE companies REGEXP '(?<![0-9])".$company_id."(?![0-9])'";
				return self::$database->select($users_query);
			}
		}
	}