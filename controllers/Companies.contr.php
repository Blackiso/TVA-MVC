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

			public static function GET_company() {
				$company_id = self::$params['company-id'];
				if (!self::check_company($company_id)) View::bad_request();
				$response = CompaniesModel::get_company($company_id);
				View::response($response);
			}

			private static function check_company($company_id) {
				$master_id = self::$user->master_id;
				return CompaniesModel::check_company($company_id, $master_id);
			}
		}
	}