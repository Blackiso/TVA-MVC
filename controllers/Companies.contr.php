<?php 

	namespace Controllers {

		use Views\View as View;
		use Models\Companies as CompaniesModel;
		use Models\Files as FilesModel;
		use Models\Bills as BillsModel;

		class Companies extends Controller  {

			public static function POST_index() {
				$company_details = self::$request->body;
				if (!self::check_data($company_details, ['company_name', 'activity', 'i_f', 'phone', 'address', 'email'])) {
					View::bad_request();
				}
				if (strlen($company_details->i_f) > 8) View::bad_request();
				$company_details->master_id = self::$user->master_id;
				$company_details->id = CompaniesModel::generate_id();

				if (CompaniesModel::add_company($company_details)) {
					unset($company_details->master_id);
					unset($company_details->address);
					unset($company_details->phone);
					View::created($company_details);
				}
			}

			public static function GET_all() {
				if (isset(self::$request->querys['last_item'])) {
					$last_item_page = ['id' => self::$request->querys['last_item']];
				}else {
					$last_item_page = null;
				}
				$id = self::$user->user_id;
				$function = "get_".self::$user->user_type."_companies";
				$result = self::$function($id, $last_item_page);
				if (!empty($result) && !isset($result[0])) $result = [$result]; 
				View::response($result);
			}

			public static function GET_company() {
				$company_id = self::$params['company-id'];
				self::check_company($company_id);
				$response = CompaniesModel::get_company($company_id);
				View::response($response);
			}

			public static function PATCH_company_upd() {
				$company_id = self::$params['company-id'];
				$data = self::$request->body;
				if (!self::check_master_company($company_id)) View::bad_request();
				if (!self::check_data($data, ['company_name', 'address', 'phone', 'email'], false)) View::bad_request();
				CompaniesModel::update_company($data, $company_id);
				View::response();
			}

			public static function DELETE_company_dlt() {
				$company_id = self::$params['company-id'];
				self::check_company($company_id);
				CompaniesModel::delete_company($company_id);
				View::response();
			}

			public static function GET_companies_search() {
				$keyword = self::$request->querys['keyword'] ?? View::bad_request();
				$user_id = self::$user->user_id;
				if (self::$user->user_type == 'user') {
					$result = CompaniesModel::search_user_company($keyword, $user_id);
				}else {
					$result = CompaniesModel::search_company($keyword, $user_id);
				}
				
				if (!empty($result) && !isset($result[0])) $result = [$result];
				View::response($result);
			}

			public static function GET_stats() {
				$company_id = self::$params['company-id'];
				self::check_company($company_id);
				$file = FilesModel::get_last_file($company_id);
				$stats = FilesModel::get_stats($file['id']);

				if ($file['type'] == "monthly") {
					$stats = self::calculate_monlty($stats);
				}else {
					$stats = self::calculate_quarterly($stats);
				}
				View::response($stats);
			}

			private static function calculate_monlty($stats) {
				$current_month = date('m');
				$return = [];
				for ($i = $current_month - 3; $i <=  $current_month; $i++) {
					$found = false;
					foreach ($stats as $item => $value) {
						if ($value['month'] == $i) {
							array_push($return, $value);
							$found = true;
						}
					}
					if (!$found) {
						array_push($return, ['total_tva' => 0, 'month' => $i]);
					}
				}
				return $return;
			}

			private static function calculate_quarterly($stats) {
				$return = [];
				for ($i = 3; $i <=  12; $i = $i+3) {
					$found = false;
					foreach ($stats as $item => $value) {
						if ($value['month'] == $i) {
							array_push($return, $value);
							$found = true;
						}
					}
					if (!$found) {
						array_push($return, ['total_tva' => 0, 'month' => $i]);
					}
				}
				return $return;
			}

			private static function get_master_companies($master_id, $last_item) {
				return CompaniesModel::get_master_companies($master_id, $last_item);
			}

			private static function get_user_companies($user_id, $last_item) {
				return CompaniesModel::get_user_companies($user_id, $last_item);
			}
		}
	}