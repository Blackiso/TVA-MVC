<?php 
	namespace Models {

		class User extends Model {

			private static $master = 'master_accounts';
			private static $user = 'user_accounts';

			private static $secret_query = "SELECT secret FROM :table WHERE user_id = :id";

			public static function get_secret($id, $type) {
				$secret_query = str_replace(':table', self::$$type, self::$secret_query);
				$secret_query = self::prep_query(['id' => $id], $secret_query);
				$result = self::$database->select($secret_query);
				return $result['secret'];
			}

			public static function check_user($password, $email, $type) {
				return self::$database->check_row(self::$$type, ['email' => $email, 'password' => $password]);
			}
		}

	}
