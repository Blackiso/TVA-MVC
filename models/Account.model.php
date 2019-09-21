<?php 
	namespace Models {

		class Account extends Model {

			private static $table = 'master_accounts';
			private static $active_plans = 'active_plans';

			public static function update_account($culums, $user_id) {
				$qr = self::update_query_constructor($culums, self::$table, ['user_id' => $user_id]);
				self::$database->insert($qr);
			}

			public static function plan_details($master_id) {
				$qr = self::select_query_constructor(['duration', 'start_date', 'expire_date'], self::$active_plans, ['master_id' => $master_id]);
				return self::$database->select($qr);
			}
		}
	}