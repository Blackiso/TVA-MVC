<?php 
	namespace Models {

		class Companies extends Model {

			private static $table = 'companies';
			private static $user_table = 'users_companies';
			private static $custom_user_query = "SELECT uc.id, c.company_name, c.activity, c.i_f FROM users_companies AS uc INNER JOIN companies AS c WHERE c.id = uc.id AND uc.user_id = :user_id AND c.deleted = 0";

			public static function generate_id() {
				return self::generate_unique_ids(self::$table, 'id');
			}

			public static function add_company($data) {
				$company_query = self::insert_query_constructor($data, self::$table);
				$result = self::$database->insert($company_query);
				return $result->query;
			}

			public static function add_user_company($company_id, $user_id) {
				$company_query = self::insert_query_constructor(["id" => $company_id, "user_id" => $user_id], self::$user_table);
				$result = self::$database->insert($company_query);
				return $result->query;
			}

			public static function check_company($company_id, $master_id) {
				return self::check_row(self::$table, ['id' => $company_id, 'master_id' => $master_id, 'deleted' => 0]);
			}

			public static function check_user_company($company_id, $user_id) {
				return self::check_row(self::$user_table, ['id' => $company_id, 'user_id' => $user_id]);
			}

			public static function get_company($company_id) {
				$company_query = self::select_query_constructor(
					['id', 'company_name', 'activity', 'i_f', 'phone', 'address'], 
					self::$table, 
					['id' => $company_id, 'deleted' => 0]
				);
				return self::$database->select($company_query);
			}

			public static function get_master_companies($master_id, $last_item) {
				$companies_query = self::pagination(
					['id', 'company_name', 'activity', 'i_f'], 
					self::$table, 
					['master_id' => $master_id, 'deleted' => 0],
					$last_item,
					'id'
				);
				return self::$database->select($companies_query);
			}

			public static function search_company($keyword, $user_id) {
				$search_query = self::search_query_constructor(['id', 'company_name', 'activity', 'i_f'], self::$table, ['company_name' => $keyword], ['master_id' => $user_id]);
				return self::$database->select($search_query);
			}

			public static function search_user_company($keyword, $user_id) {
				$keyword = explode(" ", urldecode($keyword));
				$keyword = "%".implode("%", $keyword)."%";
				$custom_query = str_replace(':user_id', $user_id, self::$custom_user_query);
				$custom_query = $custom_query." AND c.company_name LIKE '$keyword'";
				return self::$database->select($custom_query);
			}

			public static function get_user_companies($user_id, $last_item) {
				$custom_query = str_replace(':user_id', $user_id, self::$custom_user_query);
				if (isset($last_item)) {
					$last_item = $last_item['id'];
					$custom_query .= " AND c.id > '$last_item'";
				}
				$custom_query .= " GROUP BY id ASC LIMIT 20";
				return self::$database->select($custom_query);
			}

			public static function delete_company($company_id) {
				self::soft_delete(self::$table, ['id' => $company_id]);
			}

			public static function delete_company_from_user($company_id, $user_id) {
				$delete_query = "DELETE FROM ".self::$user_table." WHERE user_id = '$user_id' AND id = '$company_id'";		
				return self::$database->update($delete_query);
			}

			public static function update_company($data, $company_id) {
				$update = self::update_query_constructor($data, self::$table, ['id' => $company_id]);
				self::$database->update($update);
			}
		}
	}