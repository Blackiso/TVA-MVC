<?php 
	namespace Models {

		class Companies extends Model {

			private static $table = 'companies';

			public static function generate_id() {
				return self::generate_unique_ids(self::$table, 'id');
			}

			public static function add_company($data) {
				$company_query = self::insert_query_constructor($data, self::$table);
				$result = self::$database->insert($company_query);
				return $result->query;
			}

			public static function check_company($company_id, $master_id) {
				return self::check_row(self::$table, ['id' => $company_id, 'master_id' => $master_id, 'deleted' => 0]);
			}

			public static function get_company($company_id) {
				$company_query = self::select_query_constructor(
					['id', 'company_name', 'activity', 'i_f', 'phone', 'address'], 
					self::$table, 
					['id' => $company_id, 'deleted' => 0]
				);
				return self::$database->select($company_query);
			}

			public static function get_companies($master_id, $last_item) {
				$companies_query = self::pagination(
					['id', 'company_name', 'activity', 'i_f'], 
					self::$table, 
					['master_id' => $master_id, 'deleted' => 0],
					$last_item
				);
				return self::$database->select($companies_query);
			}

			public static function delete_company($company_id) {
				self::soft_delete(self::$table, ['id' => $company_id]);
			}

			public static function update_company($data, $company_id) {
				$update = self::update_query_constructor($data, self::$table, ['id' => $company_id]);
				self::$database->insert($update);
			}
		}
	}