<?php 

	namespace Controllers {

		use Models\Files as FilesModel;
		use Models\Bills as BillsModel;
		use Views\View as View;

		class Download extends Controller  {
			
			public static function GET_xml() {
				$file_id = self::$params['file-id'];
				$month = self::$params['month'];
				$file_name_full = "$file_id-$month.xml";

				$file = self::check_file_and_month($file_id, $month);
				$bills = BillsModel::get_all_bills($file_id, $month);

				$type = $file['type'] == "monthly" ? 1 : 2;
				$main = [
					"identifiantFiscal" => $file['i_f'],
					"annee" => $file['year'],
					"periode" => $file['type'] == "quarterly" ? $month/3 : $month,
					"regime" => $type,
					"releveDeductions" => [
						"rd" => []
					]
				];
				$order = (int) BillsModel::get_order_start($month, $file_id);

				foreach ($bills as $i => $bill) {
					$arr = [];
					$arr['ord'] = $order+1;
					$arr['num'] = $bill['nfa'];
					$arr['des'] = $bill['dbs'];
					$arr['mht'] = number_format($bill['mht'], 2, '.', '');
					$arr['tva'] = number_format($bill['tva'], 2, '.', '');
					$arr['ttc'] = number_format($bill['ttc'], 2, '.', '');
					$arr['refF'] = [
						"if" => $bill['iff'],
						"nom" => $bill['ndf'],
						"ice" => $bill['ice']
					];
					$arr['tx'] = number_format($bill['tau'], 2, '.', '');
					$arr['mp'] = [
						"id" => $bill['mdp']
					];
					$arr['dpai'] = self::reverseDate($bill['ddp']);
					$arr['dfac'] = self::reverseDate($bill['ddf']);
					if ($bill['pro'] !== '100') $arr['prorata'] = $bill['pro'];
					
					array_push($main['releveDeductions']['rd'], $arr);
					$order++;
				}

				$xml = new \LaLit\Array2XML();
				$xx = $xml::createXML("DeclarationReleveDeduction", $main);
				$xx = str_replace('standalone="no"', '', $xx->saveXML());
				header('Content-Type: application/xml');
				header('Content-Disposition: attachment; filename="'.$file_name_full.'"');
				View::plain_response($xx);
			}

			public static function GET_pdf() {
				$file_id = self::$params['file-id'];
				$month = self::$params['month'];
				$file_name_full = "$file_id-$month.pdf";

				$file = self::check_file_and_month($file_id, $month);
				$bills = BillsModel::get_all_bills($file_id, $month);
				$order = (int) BillsModel::get_order_start($month, $file_id);

				$data = [
					"year" => $file['year'],
					"month" => $file['type'] == "monthly" ? $month : 0,
					"tr" => $file['type'] == "quarterly" ? $month/3 : 0,
					"if" => $file['i_f'],
					"name" => $file['company_name']
				];

				$pdf = new \PDF($data);
				$pdf->init($bills, $order);
			}

			private static function check_file_and_month($file_id, $month) {
				$result = self::check_file([$file_id]);
				if (empty($result)) View::bad_request();
				$file_type = FilesModel::get_file_type($file_id)['type'];
				$x = $file_type == "monthly" ? 1 : 3;
				if ($month == 0 || $month > 12 || $month % $x !== 0) View::bad_request();
				return $result;
			}

			private static function check_file($files_id) {
				$function = "check_".self::$user->user_type."_file";
				$files_id = implode(',', $files_id);
				return FilesModel::$function($files_id, self::$user->user_id, true);
			}

			private static function reverseDate($date) {
				$arr = explode('-', $date);
				$arr = array_reverse($arr);
				return implode('-', $arr);
			}
		}
	}
