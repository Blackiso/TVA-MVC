<?php
	namespace Views {
		class View {

			private static $errors = [
				'password' => 'Password Requires a Mix of Numbers and Letters Min Characters 5 and Max Characters 20',
				'email_used' => 'Email already used!',
				'account_blocked' => 'Your account is blocked!',
				'authentication' => 'Authentication Error!',
				'company_set' => 'Company already set!',
				'payment_already_captured' => 'Error payment already captured!',
				'payment_error' => 'Payment Error!'
			];

			public static function json($data) {
				return json_encode($data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
			}

			public static function throw_error($err, $code = null) {
				if (gettype($err) == "string") {
					$err = [
						"error" => self::$errors[$err],
						"exit" => false,
						"code" => $code
					];
				}
				if ($code !== null) http_response_code($code);
				exit(self::json($err));
			}

			public static function not_found() {
				$err = [
					"error" => "Resource Not Found!",
					"exit"  => false,
					"code" => 404
				];
				self::throw_error($err, 404);
			}

			public static function not_authenticated() {
				$err = [
					"error" => "Authentication Required!",
					"exit"  => true,
					"code" => 401
				];
				self::throw_error($err, 401);
			}

			public static function unauthorized() {
				$err = [
					"error" => "Unauthorized Request!",
					"exit"  => false,
					"code" => 401
				];
				self::throw_error($err, 401);
			}

			public static function bad_request() {
				$err = [
					"error" => "Bad Request!",
					"exit"  => false,
					"code" => 400
				];
				self::throw_error($err, 400);
			}

			public static function created($data = null) {
				http_response_code(201);
				if ($data !== null) {
					echo self::json($data);
				}
			}

			public static function response($data = null) {
				if ($data !== null) {
					http_response_code(200);
					echo self::json($data);
				}else {
					http_response_code(204);
				}
			}

			public static function plain_response($data) {
				echo $data;
			}
		}
	}