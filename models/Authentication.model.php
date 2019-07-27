<?php 
	namespace Models {

		class Authentication extends Model {

			private static $master = 'master_accounts';
			private static $user = 'user_accounts';

			public static function check_email_exist($email) {
				return self::check_row(self::$master, ['email' => $email]);
			}

			public static function register_user($data) {
				$register_query = self::insert_query_constructor($data, self::$master);
				$result = self::$database->insert($register_query);
				return ['user_id' => $result->insert_id, 'registred' => $result->query];
			}

			public static function update_user($updates, $type, $id) {
				$secret_query = self::update_query_constructor($updates, self::$$type, ['user_id' => $id]);
				$result = self::$database->insert($secret_query);
				return $result;
			}

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
