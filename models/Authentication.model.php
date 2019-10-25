<?php 
	namespace Models {

		class Authentication extends Model {

			private static $master = 'master_accounts';
			private static $user = 'user_accounts';
			private static $activation = 'email_codes';

			public static function generate_id() {
				return self::generate_unique_ids(self::$master, 'user_id');
			}

			public static function check_email_exist($email) {
				return self::check_row(self::$master, ['email' => $email]);
			}

			public static function register_user($data) {
				$register_query = self::insert_query_constructor($data, self::$master);
				$result = self::$database->insert($register_query);
				return $result->query;
			}

			public static function update_user($updates, $type, $id) {
				$secret_query = self::update_query_constructor($updates, self::$$type, ['user_id' => $id]);
				$result = self::$database->insert($secret_query);
				return $result;
			}

			public static function get_info($id, $type, $info) {
				$secret_query = self::select_query_constructor($info, self::$$type, ['user_id' => $id]);
				$result = self::$database->select($secret_query);
				return $result ?? null;
			}

			public static function user_block($id) {
				$secret_query = self::select_query_constructor(['blocked'], self::$user, ['user_id' => $id]);
				$result = self::$database->select($secret_query);
				return $result['blocked'] ?? null;
			}

			public static function set_activation_code($user_id) {
				$generate_code = '';
				for($i = 0; $i < 5; $i++) {
					$generate_code .= mt_rand(0, 9);
				}
				$qr = self::insert_query_constructor(['code' => $generate_code, 'user_id' => $user_id], self::$activation);
				self::$database->insert($qr);
				return $generate_code;
			}

			public static function get_activation_code($user_id, $code) {
				$timestamp = time() - 60*60;
				$qr = self::select_query_constructor(['code'], self::$activation, ['user_id' => $user_id, 'code' => $code]);
				$qr .= " AND time >= $timestamp ORDER BY id DESC LIMIT 1";
				$result = self::$database->select($qr);
				return !empty($result);
			}

			public static function set_active($user_id, $code) {
				$qr_updt = self::update_query_constructor(['active' => 1], self::$master, ['user_id' => $user_id]);
				$qr_dlt = "DELETE FROM ".self::$activation." WHERE user_id='$user_id' AND code='$code';";
				$qr = $qr_updt.";".$qr_dlt;
				self::$database->insert($qr);
			}

			public static function get_user_from_email($email, $type) {
				$columns = ['user_id', 'name', 'email', 'password', 'secret'];

				if (self::$$type == 'master_accounts') {
					array_push($columns, 'account_type');
					array_push($columns, 'active');
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
