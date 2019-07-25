<?php 
	namespace Models {

		class Authentication extends Model {

			private static $users_table = 'master_accounts';

			public static function check_email_exist($email) {
				return self::check_row(self::$users_table, ['email' => $email]);
			}

			public static function register_user($data) {
				$register_query = self::insert_query_constructor($data, self::$users_table);
				$result = self::$database->insert($register_query);
				return ['user_id' => $result->insert_id, 'registred' => $result->query];
			}
		}

	}
