<?php 
	namespace Models {

		class Files extends Model {

			private static $table = 'files';
			private static $companies_user_table = 'users_companies';
			private static $companies_table = 'companies';

			public static function generate_id() {
				return self::generate_unique_ids(self::$table, 'id');
			}

			public static function create_file($data) {
				$file_qr = self::insert_query_constructor($data, self::$table);
				$result = self::$database->insert($file_qr);
				return $result->query;
			}

			public static function get_all_files($company_id, $last_item) {
				$files_query = self::pagination(
					['id', 'file_name', 'year', 'type', 'last_modified'], 
					self::$table, 
					['company_id' => $company_id, 'deleted' => 0],
					$last_item,
					'id'
				);
				return self::$database->select($files_query);
			}

			public static function search_files($company_id, $keyword) {
				$files_query = self::search_query_constructor(
					['id', 'file_name', 'year', 'type', 'last_modified'], 
					self::$table,
					['file_name' => $keyword],
					['company_id' => $company_id, 'deleted' => 0]
				);
				return self::$database->select($files_query);
			}

			public static function update_file($data, $file_id) {
				$update = self::update_query_constructor($data, self::$table, ['id' => $file_id]);
				self::$database->update($update);
			}

			public static function delete_file($files_id) {
				self::soft_delete(self::$table, ["id IN ($files_id)"]);
			}

			public static function get_file_type($file_id) {
				$query = self::select_query_constructor(['type'], self::$table, ['id' => $file_id]);
				return self::$database->select($query);
			}

			public static function get_details($file_id) {
				$query = "SELECT count(b.id) AS bills, b.month FROM files AS f INNER JOIN bills AS b ON f.id = b.file_id WHERE f.id = '$file_id' GROUP BY b.month";
				return self::$database->select($query);
			}

			public static function check_user_file($files_id, $user_id, $return = false) {
				$check_query = "SELECT f.id, f.year, f.type, dc.i_f, dc.company_name FROM ".self::$table." AS f INNER JOIN ".self::$companies_user_table." AS c ON c.id = f.company_id INNER JOIN companies AS dc ON c.id = dc.id WHERE f.id IN ($files_id) AND c.user_id = '$user_id' AND f.deleted = 0";
				$result = self::$database->select($check_query);
				if (!empty($result && !$return) && !isset($result[0])) $result = [$result];
				return  $return ? $result : self::process_result($result, $files_id);
			}

			public static function check_master_file($files_id, $master_id, $return = false) {
				$check_query = "SELECT f.id, f.year, f.type, c.i_f, c.company_name FROM ".self::$table." AS f INNER JOIN ".self::$companies_table." AS c ON c.id = f.company_id WHERE f.id IN ($files_id) AND c.master_id = '$master_id' AND f.deleted = 0";
				$result = self::$database->select($check_query);
				if (!empty($result && !$return) && !isset($result[0])) $result = [$result];
				return  $return ? $result : self::process_result($result, $files_id);
			}

			private static function process_result($result, $files_id) {
				if (!empty($result) && sizeof($result) == sizeof(explode(',', $files_id))) {
					return true;
				}else {
					return false;
				}
			}

		}

	}
