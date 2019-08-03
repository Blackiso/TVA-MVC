<?php 

	namespace Controllers {

		use Views\View as View;
		use Models\Companies as CompaniesModel;

		class Companies extends Controller  {

			public static function POST_index() {
				$company_details = self::$request->body;
				if (!self::check_data($company_details, ['company_name', 'activity', 'i_f', 'phone', 'address'])) {
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
				$result = CompaniesModel::get_companies(self::$user->master_id, $last_item_page);
				View::response($result);
			}

			public static function GET_company() {
				$company_id = self::$params['company-id'];
				if (!self::check_company($company_id)) View::bad_request();
				$response = CompaniesModel::get_company($company_id);
				View::response($response);
			}

			public static function PATCH_company_upd() {
				$company_id = self::$params['company-id'];
				$data = self::$request->body;
				if (!self::check_company($company_id)) View::bad_request();
				if (!self::check_data($data, ['company_name', 'address', 'phone'], false)) View::bad_request();
				CompaniesModel::update_company($data, $company_id);
				View::response();
			}

			public static function DELETE_company_dlt() {
				$company_id = self::$params['company-id'];
				if (!self::check_company($company_id)) View::bad_request();
				CompaniesModel::delete_company($company_id);
				View::response();
			}

			private static function check_company($company_id) {
				$master_id = self::$user->master_id;
				return CompaniesModel::check_company($company_id, $master_id);
			}
		}
	}