<?php 
	namespace Models {

		abstract class Model {

			protected static $database;

			public static function init() {
				self::$database = \Database::get_instence();
			}

			protected static function prep_query($items, $query) {
				$return;
				foreach ($items as $key => $value) {
					$return = str_replace(':'.$key, $value, $query);
				}

				return $return;
			}
		}

	}
