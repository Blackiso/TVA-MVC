<?php 
	namespace Models {

		class Payment extends Model {

			private static $plans = 'account_plans';
			private static $active_plans = 'active_plans';
			private static $payments = 'payments';
			private static $master_accounts = 'master_accounts';

			public static function get_plan($reference_id) {
				$qr = self::select_query_constructor(['reference_id', 'price', 'currency_code', 'type'], self::$plans, ['reference_id' => $reference_id]);
				$result = self::$database->select($qr);
				return empty($result) ? false : $result;
			}

			public static function create_payment($payment, $active_plan) {
				$_culumns = [];
				foreach ($active_plan as $culumn => $value) {
					$culumn = $culumn."='".$value."'";
					array_push($_culumns, $culumn);
				}

				$qr_payment = self::insert_query_constructor($payment, self::$payments);
				$qr_active = self::insert_query_constructor($active_plan, self::$active_plans);
				$qr_active .= " ON DUPLICATE KEY UPDATE ".implode(',', $_culumns);
				$qr_user = self::update_query_constructor(['account_type' => $payment['account_type']], self::$master_accounts, ['user_id' => $payment['master_id']]);
				$qr = $qr_payment.";".$qr_active.";".$qr_user.";";
				self::$database->insert($qr);
			}

			public static function get_expire_date($master_id) {
				$qr = self::select_query_constructor(['expire_date'], self::$active_plans, ['master_id' => $master_id]);
				$result = self::$database->select($qr);
				return empty($result) ? false : $result['expire_date'];
			}

			public static function get_active_plan($master_id) {
				$qr = self::select_query_constructor(['payment_id','start_date'], self::$active_plans, ['master_id' => $master_id]);
				return self::$database->select($qr);
			}
			
			public static function set_account_type($type, $master_id) {
				$qr = self::update_query_constructor(['account_type' => $type], self::$master_accounts, ['user_id' => $master_id]);
				self::$database->insert($qr);
			}

			public static function get_account_type($master_id) {
				$qr = self::select_query_constructor(['account_type'], self::$master_accounts, ['user_id' => $master_id]);
				$result = self::$database->select($qr);
				return $result['account_type'];
			}

			public static function refunded_payment($payment_id) {
				$qr = self::update_query_constructor(['refunded' => 1], self::$payments, ['payment_id' => $payment_id]);
				return self::$database->insert($qr);
			}

			public static function check_payment($token) {
				return self::check_row(self::$payments, ['payment_id' => $token]);
			}

			public static function check_refund($payment_id) {
				$qr = self::select_query_constructor(['refunded'], self::$payments, ['payment_id' => $payment_id]);
				$result = self::$database->select($qr);
				return $result['refunded'];
			}

			public static function capture_id($payment_id) {
				$qr = self::select_query_constructor(['capture_id'], self::$payments, ['payment_id' => $payment_id]);
				$result = self::$database->select($qr);
				return $result['capture_id'];
			}

			public static function get_history($master_id) {
				$qr = self::select_query_constructor(['payment_id','full_name', 'payer_email', 'account_type', 'payment_amount', 'payment_time', 'refunded'], self::$payments, ['master_id' => $master_id]);
				$qr .= " ORDER BY id DESC LIMIT 5"; 
				$result = self::$database->select($qr);
				return !empty($result) && !isset($result[0]) ? [$result] : $result;
			}
		}
	}