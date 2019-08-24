<?php 
	namespace Controllers {

		use Views\View as View;
		use Models\Files as FilesModel;

		class Files extends Controller  { 
			
			public static function POST_files() {
				$company_id = self::$params['company-id'];
				$data = self::$request->body;
				if(!self::check_data($data, ['file_name', 'year', 'type'])) {
					View::bad_request();
				}
				self::check_file_data($data);
				$data->company_id = $company_id;
				$data->id = FilesModel::generate_id();
				FilesModel::create_file($data);
				View::created();
			}

			public static function GET_files() {
				$company_id = self::$params['company-id'];
				self::check_company($company_id);
				if (isset(self::$request->querys['last_item'])) {
					$last_item_page = ['id' => self::$request->querys['last_item']];
				}else {
					$last_item_page = null;
				}
				$result = FilesModel::get_all_files($company_id, $last_item_page);
				View::response($result);
			}

			public static function PATCH_files() {
				$file_id = self::$params['file-id'];
				$data = self::$request->body;
				if(!self::check_data($data, ['file_name', 'year', 'type'], false)) {
					View::bad_request();
				}
				self::check_file_data($data);
				self::check_file($file_id);
				FilesModel::update_file($data, $file_id);
				View::response();
			}

			public static function DELETE_files() {
				$file_id = self::$params['file-id'];
				self::check_file($file_id);
				FilesModel::delete_file($file_id);
				View::response();
			}

			public static function GET_search_files() {
				$company_id = self::$params['company-id'];
				$keyword = self::$request->querys['keyword'] ?? View::bad_request();
				$result = FilesModel::search_files($company_id, $keyword);
				if (!empty($result) && !isset($result[0])) $result = [$result];
				View::response($result);
			}

			private static function check_file($file_id) {
				$function = "check_".self::$user->user_type."_file";
				if (!FilesModel::$function($file_id, self::$user->user_id)) View::bad_request();
			}

			private static function check_file_data($data) {
				if (isset($data->year)) {
					if ($data->year < 1000 AND $data->year < 2100) {
						View::bad_request();
					}
				}
				if (isset($data->type)) {
					if ($data->type !== "quarterly" && $data->type !== "monthly") {
						View::bad_request();
					}
				}
			}

		}
	}
