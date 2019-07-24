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

			protected static function select_query_constructor($culumns, $table, $conditions = null) {
				$query = "SELECT :culumns FROM :table";
				$culumns = implode(',', $culumns);
				$query = str_replace(':culumns', $culumns, $query);
				$query = str_replace(':table', $table, $query);
				if ($conditions !== null) {
					$cond = " WHERE ";
					$arr = [];
					foreach ($conditions as $key => $value) {
						$x = $key."='".$value."'";
						array_push($arr, $x);
					}
					$query = $query.$cond.implode(' AND ', $arr);

				}
				return $query;
			}
		}

	}
