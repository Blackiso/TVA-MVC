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

			public static function delete_file($file_id) {
				self::soft_delete(self::$table, ['id' => $file_id]);
			}

			public static function check_user_file($file_id, $user_id) {
				$check_query = "SELECT f.id FROM ".self::$table." AS f INNER JOIN ".self::$companies_user_table." AS c ON c.id = f.company_id WHERE f.id = '$file_id' AND c.user_id = '$user_id' AND f.deleted = 0";
				$result = self::$database->select($check_query);
				return !empty($result);
			}

			public static function check_master_file($file_id, $master_id) {
				$check_query = "SELECT f.id FROM ".self::$table." AS f INNER JOIN ".self::$companies_table." AS c ON c.id = f.company_id WHERE f.id = '$file_id' AND c.master_id = '$master_id' AND f.deleted = 0";
				$result = self::$database->select($check_query);
				return !empty($result);
			}

		}

	}
