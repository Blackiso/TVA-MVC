<?php 
	namespace Models {

		class Bills extends Model {

			private static $table = 'bills';
			private static $files_table = 'files';
			private static $companies_table = 'companies';
			private static $users_companies_table = 'users_companies';

			public static function generate_ids($num) {
				return self::generate_unique_ids(self::$table, 'id', $num);
			}

			public static function add_bills($data) {
				$culumns = array_keys((array)$data[0]);
				$query = self::multiple_insert_query_constructor($culumns, $data, self::$table);
				return self::$database->insert($query);
			}

			public static function get_bills($file_id, $month, $last_item) {
				$bills_query = self::pagination(
					['id', 'nfa', 'ddf', 'ndf', 'iff', 'ice', 'dbs', 'mht', 'tau', 'tva', 'ttc', 'mdp', 'ddp', 'code', 'pro'], 
					self::$table, 
					['file_id' => $file_id, 'month' => $month],
					$last_item,
					'id'
				);
				return self::$database->select($bills_query);
			}

			public static function search_bills($file_id, $month, $keyword) {
				$bills_query = self::search_query_constructor(
					['id', 'nfa', 'ddf', 'ndf', 'iff', 'ice', 'dbs', 'mht', 'tau', 'tva', 'ttc', 'mdp', 'ddp'], 
					self::$table,
					['nfa' => $keyword],
					['file_id' => $file_id, 'month' => $month]
				);
				return self::$database->select($bills_query);
			}

			public static function get_all_bills($file_id, $month) {
				$bills_query = self::select_query_constructor(
					['nfa', 'ddf', 'ndf', 'iff', 'ice', 'dbs', 'mht', 'tau', 'tva', 'ttc', 'mdp', 'ddp', 'pro'], 
					self::$table, 
					['file_id' => $file_id, 'month' => $month]
				);
				$bills_query .= " ORDER BY tau ASC";
				$result = self::$database->select($bills_query);
				if (!empty($result) && !isset($result[0])) $result = [$result];
				return $result;
			}

			public static function get_all_bills_by_type($file_id, $month) {
				$bills_query = "SELECT DISTINCT code, tau, TRUNCATE(SUM(tva), 2) as tva FROM ".self::$table." WHERE file_id = ".$file_id." AND month = ".$month." GROUP BY code, tau";
				$result = self::$database->select($bills_query);
				if (!empty($result) && !isset($result[0])) $result = [$result];
				return $result;
			}

			public static function check_bill($bill_id, $user_id, $type) {
				$bill_id = implode(',', $bill_id);
				if ($type == 'master') {
					$check_query = "SELECT b.id FROM ".self::$table." AS b INNER JOIN ".self::$files_table." AS f ON b.file_id = f.id INNER JOIN ".self::$companies_table." AS c ON f.company_id = c.id WHERE c.master_id = '$user_id' AND b.id IN ($bill_id) AND f.deleted = 0";
				}else {
					$check_query = "SELECT b.id FROM ".self::$table." AS b INNER JOIN ".self::$files_table." AS f ON b.file_id = f.id INNER JOIN ".self::$users_companies_table." AS c ON f.company_id = c.id WHERE c.user_id = '$user_id' AND b.id IN ($bill_id) AND f.deleted = 0";
				}
				return !empty(self::$database->select($check_query));
			}

			public static function get_order_start($month, $file_id) {
				$qr = "SELECT COUNT(id) AS order_start FROM bills WHERE month < $month AND file_id = '$file_id'";
				return self::$database->select($qr)['order_start'];
			}

			public static function update_bill($data, $bill_id) {
				$update = self::update_query_constructor($data, self::$table, ['id' => $bill_id]);
				self::$database->update($update);
			}

			public static function delete_bills($ids) {
				$ids = implode(',', $ids);
				$qr = "DELETE FROM ".self::$table." WHERE id IN ($ids)";
				self::$database->insert($qr);
 			}

			public static function get_suppliers($keyword) {
				$query = self::search_query_constructor(
					['ndf', 'iff', 'ice'], 
					self::$table,
					['ndf' => $keyword]
				);
				$query = substr($query, 0, -8);
				$query .= "GROUP BY iff LIMIT 15";
				return self::$database->select($query);
			}

			public static function get_dbs($keyword) {
				$query = self::search_query_constructor(
					['dbs', 'tau', 'code'], 
					self::$table,
					['dbs' => $keyword]
				);
				$query = substr($query, 0, -8);
				$query .= "GROUP BY dbs LIMIT 15";
				return self::$database->select($query);
			}

		}

	}
