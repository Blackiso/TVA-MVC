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
					"periode" => $month,
					"regime" => $type,
					"releveDeductions" => [
						"rd" => []
					]
				];

				foreach ($bills as $i => $bill) {
					$arr = [];
					$arr['ord'] = $i+1;
					$arr['num'] = $bill['nfa'];
					$arr['des'] = $bill['dbs'];
					$arr['mht'] = $bill['mht'];
					$arr['tva'] = $bill['tva'];
					$arr['ttc'] = $bill['ttc'];
					$arr['refF'] = [
						"if" => $bill['iff'],
						"nom" => $bill['ndf'],
						"ice" => $bill['ice']
					];
					$arr['tx'] = $bill['tau'];
					$arr['mp'] = [
						"id" => $bill['mdp']
					];
					$arr['dpai'] = $bill['ddp'];
					$arr['dfac'] = $bill['ddf'];
					array_push($main['releveDeductions']['rd'], $arr);
				}

				$xml = new \LaLit\Array2XML();
				$xx = $xml::createXML("DeclarationReleveDeduction", $main);
				$xx = str_replace('<?xml version="1.0" encoding="utf-8" standalone="no"?>', '', $xx->saveXML());
				header('Content-Type: application/xml');
				// header('Content-Disposition: attachment; filename="'.$file_name_full.'"');
				View::plain_response($xx);
			}

			public static function GET_pdf() {
				$file_id = self::$params['file-id'];
				$month = self::$params['month'];
				$file_name_full = "$file_id-$month.pdf";

				$file = self::check_file_and_month($file_id, $month);
				$bills = BillsModel::get_all_bills($file_id, $month);

				$data = [
					"year" => $file['year'],
					"month" => $file['type'] == "monthly" ? $month : 0,
					"tr" => $file['type'] == "quarterly" ? $month/3 : 0,
					"if" => $file['i_f'],
					"name" => $file['company_name']
				];

				$pdf = new \PDF($data);
				$pdf->init($bills);
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
		}
	}
