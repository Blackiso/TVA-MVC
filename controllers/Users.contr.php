<?php 

	namespace Controllers {

		class Users extends Controller  {
			
			private static $need_auth = true;

			public static function run() {
				return "self::test()";
			}

			public static function need_auth() {
				return self::$need_auth;
			}
		}
	}
