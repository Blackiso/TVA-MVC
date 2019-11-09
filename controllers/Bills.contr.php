<?php 
	namespace Controllers {

		use Views\View as View;
		use Models\Files as FilesModel;
		use Models\Bills as BillsModel;

		class Bills extends Controller  {

			public static function POST_index() {
				$file_id = self::$params['file-id'];
				$data = self::$request->body;

				if (!self::check_data($data, ['month', 'bills'])) {
					View::bad_request();
				}
				if (sizeof($data->bills) > 5 || sizeof($data->bills) == 0) {
					View::bad_request();
				}
				foreach ($data->bills as $key => $value) {
					if (!self::check_data($value, ['nfa', 'ddf', 'ndf', 'iff', 'ice', 'dbs', 'mht', 'tau', 'tva', 'ttc', 'mdp', 'ddp', 'pro', 'code'])) {
						View::bad_request();
					}
					if (strlen($value->ice) > 15 || strlen($value->iff) > 8) {
						View::bad_request();
					}
				}

				self::check_file_and_month($file_id, $data->month);
				FilesModel::update_last_modified($file_id);

				$ids = BillsModel::generate_ids(sizeof($data->bills));
				if (!empty($ids) && !isset($ids[0])) $ids = [$ids];
				foreach ($data->bills as $key => $value) {
					$value->id = $ids[$key];
					$value->month = $data->month;
					$value->file_id = $file_id;
				}
				if (BillsModel::add_bills($data->bills)) {
					foreach ($data->bills as $bill) {
						unset($bill->file_id);
						unset($bill->month);
						$bill->iff = (string) $bill->iff;
						$bill->ice = (string) $bill->ice;
					}
					View::created($data->bills);
				}
			}

			public static function GET_index() {
				$file_id = self::$params['file-id'];
				$month = self::$params['month'];
				if (isset(self::$request->querys['last_item'])) {
					$last_item_page = ['id' => self::$request->querys['last_item']];
				}else {
					$last_item_page = null;
				}
				self::check_file_and_month($file_id, $month);
				$result = BillsModel::get_bills($file_id, $month, $last_item_page);
				if (!empty($result) && !isset($result[0])) $result = [$result];
				foreach ($result as $i => $bill) {
					$result[$i]['iff'] = "#-#_prefix_".$bill['iff'];
					$result[$i]['ice'] = "#-#_prefix_".$bill['ice'];
				}
				View::response($result);
			}

			public static function GET_suppliers() {
				$keyword = self::$request->querys['keyword'] ?? View::bad_request();
				$result = BillsModel::get_suppliers($keyword);
				if (!empty($result) && !isset($result[0])) $result = [$result];
				View::response($result);
			}

			public static function GET_dbs() {
				$keyword = self::$request->querys['keyword'] ?? View::bad_request();
				$result = BillsModel::get_dbs($keyword);
				if (!empty($result) && !isset($result[0])) $result = [$result];
				View::response($result);
			}

			public static function PATCH_index() {
				$bill_id = self::$params['bill-id'];
				$data = self::$request->body;
				if (!self::check_data($data, ['nfa', 'ddf', 'ndf', 'iff', 'ice', 'dbs', 'mht', 'tau', 'tva', 'ttc', 'mdp', 'ddp'], false)) {
					View::bad_request();
				}
				if (isset($data->ice)) {
					if (strlen($data->ice > 15)) {
						View::bad_request();
					}
				}
				if (isset($data->iff)) {
					if (strlen($data->iff > 15)) {
						View::bad_request();
					}
				}
				if (!BillsModel::check_bill([$bill_id], self::$user->user_id, self::$user->user_type)) View::bad_request();
				BillsModel::update_bill($data, $bill_id);
				View::response();
			}

			public static function GET_search() {
				$file_id = self::$params['file-id'];
				$month = self::$params['month'];
				$keyword = self::$request->querys['keyword'] ?? View::bad_request();
				self::check_file_and_month($file_id, $month);
				$result = BillsModel::search_bills($file_id, $month, $keyword);
				if (!empty($result) && !isset($result[0])) $result = [$result];
				View::response($result);
			}

			public static function DELETE_index() {
				$ids = self::$request->body->ids ?? View::bad_request();
				if (!BillsModel::check_bill($ids, self::$user->user_id, self::$user->user_type)) View::bad_request();
				BillsModel::delete_bills($ids);
				View::response();
			}

			public static function GET_table() {
				$file_id = self::$params['file-id'];
				$month = self::$params['month']; 
				self::check_file_and_month($file_id, $month);
				$bills = BillsModel::get_all_bills_by_type($file_id, $month);
				View::response($bills);
			}

			private static function check_file_and_month($file_id, $month) {
				self::check_file([$file_id]);
				$file_type = FilesModel::get_file_type($file_id)['type'];
				$x = $file_type == "monthly" ? 1 : 3;
				if ($month == 0 || $month > 12 || $month % $x !== 0) View::bad_request();
			}

			private static function check_file($files_id) {
				$function = "check_".self::$user->user_type."_file";
				$files_id = implode(',', $files_id);
				if (!FilesModel::$function($files_id, self::$user->user_id)) View::bad_request();
			}

		}
	}
