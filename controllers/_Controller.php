<?php 
	namespace Controllers {

		use Views\view as View;

		abstract class Controller {

			private static $default = 'index';
			private static $pass_regex = '/^(?=.*[A-Za-z])(?=.*[0-9])[A-Za-z\d@$!%*#?é^&â ]{5,20}$/';
			private static $encryption = 'sha256';
			protected static $user;
			protected static $request;
			protected static $params;
				
			public static function init($request, $user, $action, $params) {
				\Models\Model::init();
				self::$request = $request;
				self::$user = $user;
				self::$params = $params;
				$action = $action ?? self::$default;
				$action = self::construct_action($action);

				if (method_exists(static::class, $action)) {
					static::$action();
				}else {
					View::not_found();
				}
			}

			private static function construct_action($action) {
				return self::$request->method.'_'.$action;
			}

			protected static function filter_password($pass) {
				return preg_match(self::$pass_regex, $pass);
			}

			public static function check_data($arr, $keys, $force = true) {
				if ($arr == null || gettype($arr) !== 'object' || self::has_empty($arr)) {
					return false;
				}
				if ($force) {
					foreach ($keys as $key) {
						if(!array_key_exists($key, $arr)) return false;
					}
				}
				foreach ($arr as $key => $value) {
					if (!in_array($key, $keys)) return false;
				}
				return true;
			}

			protected static function has_empty($array) {
				$array = (array)$array;
    			return count($array) != count(array_diff(array_map('serialize', $array), array_map('serialize', [' ', '', null, []])));
			}

			protected static function clear_str($str) {
				return addslashes(strip_tags($str));
			}

			protected static function encrypt_password($pass, $secret) {
				$secret = explode('-', $secret)[0];
				return hash_hmac(self::$encryption, $pass, $secret);
			}

			protected static function generate_secret($salt) {
				$rand_num = time();
				$secret = hash_hmac('sha256', $salt, $rand_num);
				$add_secret = uniqid("KY");
				return $secret."-".$add_secret;
			}

			protected static function check_email($email) {
				return filter_var($email, FILTER_VALIDATE_EMAIL);
			}

			protected static function check_master_company($company_id) {
				$master_id = self::$user->master_id;
				return \Models\Companies::check_company($company_id, $master_id);
			}

			protected static function check_user_company($company_id, $user_id = null) {
				$user_id = $user_id ?? self::$user->user_id;
				return \Models\Users::check_user_company($company_id, $user_id);
			}
		}

	}
