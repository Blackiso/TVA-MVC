<?php 
	namespace Models {

		abstract class Model {

			protected static $database;
			protected static $page_limit = 20;
			protected static $search_limit = 10;

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

			protected static function search_query_constructor($culumns, $table, $search, $conditions = null) {
				$query = "SELECT :culumns FROM :table";
				$culumns = implode(',', $culumns);
				$query = str_replace(':culumns', $culumns, $query);
				$query = str_replace(':table', $table, $query);
				if ($conditions !== null) {
					$query .= self::conditions($conditions);
					$query .= " AND";
				}else {
					$query .= " WHERE";
				}
				$search_arr = [];
				foreach ($search as $key => $value) {
					$s = explode(" ", urldecode($value));
					$s = "%".implode("%", $s)."%";
					$s = $key." LIKE '".$s."'";
					array_push($search_arr, $s);
				}
				$query .= " ".implode(' AND ', $search_arr);
				$query .= " LIMIT ".self::$search_limit;
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

			protected static function pagination($columns, $table, $conditions, $last_item, $sort = false) {
				$select_query = self::select_query_constructor($columns, $table, $conditions);
				if ($last_item !== null) {
					$sign = $sort !== false ? '<' : '>';
					$select_query.= " AND ".key($last_item)." $sign ".array_values($last_item)[0];
				}
				if ($sort !== false) $select_query.= " ORDER BY ".$sort." DESC";
				$select_query.= " LIMIT ".self::$page_limit;
				return $select_query;
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
				return empty($result) ? false : true;
			}

			public static function soft_delete($table, $conditions) {
				$time = date('Y-m-d h:i:s');
				$qr = self::update_query_constructor(['deleted' => 1, 'deletion_time' => $time], $table, $conditions);
				$result = self::$database->insert($qr);
			}

			public static function generate_unique_ids($table, $id_name, $num = 1) {
				$return = array();
				for ($i = 0; $i < $num; $i++) { 
					$id  = explode('.', microtime(true))[0];
					array_push($return, $id);
					usleep(3 * 100);
				}
				$ids = implode(", ", $return);
				$check = "SELECT $id_name FROM $table WHERE $id_name IN ($ids)";
				$result = self::$database->select($check);
				if (!empty($result)) {
					self::generate_unique_ids($table, $id_name, $num);
				}else {
					return sizeof($return) > 1 ? $return : $return[0];
				}
			}
		}

	}
