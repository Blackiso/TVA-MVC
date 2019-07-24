<?php 
	namespace Models {

		class User extends Model {

			private static $master = 'master_accounts';
			private static $user = 'user_accounts';

			public static function get_secret($id, $type) {
				$secret_query = self::select_query_constructor(['secret'], self::$$type, ['user_id' => $id]);
				$result = self::$database->select($secret_query);
				return $result['secret'];
			}

			public static function get_user_from_email($email, $type) {
				$columns = ['user_id', 'name', 'email', 'password', 'secret'];

				if (self::$$type == 'master_accounts') {
					array_push($columns, 'account_type');
				}else {
					array_push($columns, 'master_id');
					array_push($columns, 'blocked');
				}
				$qr = self::select_query_constructor($columns, self::$$type, ['email' => $email]);
				$result = self::$database->select($qr);
				return $result;
			}
		}
	}