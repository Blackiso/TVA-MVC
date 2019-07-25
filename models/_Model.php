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
					$query = $query.self::conditions($conditions);
				}
				return $query;
			}

			protected static function insert_query_constructor($culumns, $table) {
				$query = "INSERT INTO :table (:culumns) VALUES (:values)";
				$_culumns = [];
				$_values = [];
				foreach ($culumns as $culumn => $value) {
					array_push($_culumns, $culumn);
					array_push($_values, "'".$value."'");
				}

				$query = str_replace(':table', $table, $query);
				$query = str_replace(':culumns', implode(',', $_culumns), $query);
				$query = str_replace(':values', implode(',', $_values), $query);

				return $query;
			}

			protected static function update_query_constructor($culumns, $table, $conditions = null) {
				$query = "UPDATE :table SET :culumns";
				$_culumns = [];
				foreach ($culumns as $culumn => $value) {
					$culumn = $culumn."='".$value."'";
					array_push($_culumns, $culumn);
				}

				$query = str_replace(':table', $table, $query);
				$query = str_replace(':culumns', implode(',', $_culumns), $query);

				if ($conditions !== null) {
					$query = $query.self::conditions($conditions);
				}
				return $query;
			}

			private static function conditions($conditions) {
				$cond = " WHERE ";
				$arr = [];
				foreach ($conditions as $key => $value) {
					$x = $key."='".$value."'";
					array_push($arr, $x);
				}
				return $cond.implode(' AND ', $arr);
			}

			public static function check_row($table, $conditions) {
				$qr = self::select_query_constructor(['*'], $table, $conditions);
				$result = self::$database->select($qr);
				if (empty($result)) {
					return false;
				}else {
					return true;
				}
			}
		}

	}
