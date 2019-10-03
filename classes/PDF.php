<?php 
	// FPDF Class
	require_once('fpdf.php');
	/**
	 * This Class Generates a PDF based on data that wase passed to it.
	 * @return PDF object
	 */
	class PDF extends fpdf {

		protected $page_type = 'L';
		protected $data;

		private $pages_total = 0;
		private $mht_val = 0;
		private $tva_val = 0;
		private $ttc_val = 0;

		private $payment_mode = [
			1 => "ESPÈCE",
			2 => "CHEQUE",
			3 => "PRÉLÈVEMENT",
			4 => "VIREMENT",
			5 => "EFFET",
			6 => "COMPENSATION",
			7 => "AUTRES"
		];

		function __construct($data) {
			$this->data = (object) $data;
			if (strlen($this->data->if) < 8) {
				$mul = 8 - strlen($this->data->if);
				$str = str_repeat("0", $mul);
				$this->data->if = $str.$this->data->if;
			}
			parent::__construct($this->page_type);
			$this->AddPage();
		}

		public function init($bills, $order) {

			$arrays = [];
			$current_index = 0;
			$full_bills = [];

			foreach ($bills as $key => $value) {
				if ($key == 0) {
					$arrays[$current_index] = [];
				}else if ($bills[$key - 1]['tau'] !== $bills[$key]['tau']) {
					$current_index++;
					$arrays[$current_index] = [];
				}
				array_push($arrays[$current_index], $value);
			}

			foreach ($arrays as $key => $value) {
				$pages = ceil(sizeof($value) / 17);
				$loop = (17 * $pages) - sizeof($value);
				for ($i = 0; $i < $loop; $i++) { 
					array_push($arrays[$key], " ");
				}
				$full_bills = array_merge($full_bills, $arrays[$key]);
			}

			for ($i = 0; $i < sizeof($full_bills); $i++) { 
				if ($full_bills[$i] !== " ") {
					$bill = (object) $full_bills[$i];
					$this->setX(8);
					$this->SetFont('Arial', '', 8);
					$this->Cell(16.2, 5.3, $order+1, 1, 0);
					$this->Cell(15.1, 5.3, $this->resize($bill->nfa, 7), 1, 0);
					$this->Cell(19.6, 5.3, $bill->ddf, 1, 0, 'C');
					$nome = urldecode($this->em($bill->ndf));
					$this->Cell(35.2, 5.3, $this->resize($nome, 23), 1, 0);
					$this->Cell(32.57, 5.3, $bill->iff, 1, 0);
					$this->Cell(27.54, 5.3, $bill->ice, 1, 0);
					$dbs = urldecode($this->em($bill->dbs));
					$this->Cell(27.25, 5.3, $this->resize($dbs, 13), 1, 0);
					$this->Cell(19.6, 5.3, number_format($bill->mht, 2, '.', ' '), 1, 0, 'R');
					$this->Cell(12.43, 5.3, number_format($bill->tau, 2, '.', ' '), 1, 0, 'R');
					$this->Cell(18.8, 5.3, number_format($bill->tva, 2, '.', ' '), 1, 0, 'R');
					$this->Cell(18.53, 5.3, number_format($bill->ttc, 2, '.', ' '), 1, 0, 'R');
					$mode = urldecode($this->em($this->payment_mode[$bill->mdp]));
					$this->Cell(18.8, 5.3, $this->resize($mode, 8), 1, 0);
					$this->Cell(18.5, 5.3, $bill->ddp, 1, 1, 'C');

					$this->mht_val = $this->mht_val + (float) $bill->mht;
					$this->tva_val = $this->tva_val + (float) $bill->tva;
					$this->ttc_val = $this->ttc_val + (float) $bill->ttc;
					$this->pages_total = $this->pages_total + (float) $bill->tva;
					$order++;
				}else {
					$this->empty_row();
				}
			}

			$this->Output();
		}

		private function resize($str, $max) {
			if (strlen($str) > $max) {
				$sub = strlen($str) - $max;
				return substr($str, 0, -1 * $sub);
			}
			return $str;
		}

		private function empty_row() {
				$this->setX(8);
				$this->SetFont('Arial', '', 8);
				$this->Cell(16.2, 5.3, '', 1, 0);
				$this->Cell(15.1, 5.3, '', 1, 0);
				$this->Cell(19.6, 5.3, '', 1, 0, 'C');
				$this->Cell(35.2, 5.3, '', 1, 0, 'C');
				$this->Cell(32.57, 5.3, '', 1, 0, 'C');
				$this->Cell(27.54, 5.3, '', 1, 0, 'C');
				$this->Cell(27.25, 5.3, '', 1, 0, 'C');
				$this->Cell(19.6, 5.3, '', 1, 0, 'C');
				$this->Cell(12.43, 5.3, '', 1, 0, 'C');
				$this->Cell(18.8, 5.3, '', 1, 0, 'C');
				$this->Cell(18.53, 5.3, '', 1, 0, 'C');
				$this->Cell(18.8, 5.3, '', 1, 0, 'C');
				$this->Cell(18.5, 5.3, '', 1, 1, 'C');
		}

		public function Header() {
			// Generate header images
			$this->image(dirname(__DIR__, 1).'/images/image-1.png', 20, 10, 45, 25);
			$this->image(dirname(__DIR__, 1).'/images/image-2.png', $this->postion(23, 'C'), 10, 23, 23);
			$this->image(dirname(__DIR__, 1).'/images/image-3.png', $this->postion(45, 'R'), 10, 45, 25);
			$this->cell(0, 30, '', 0, 1);

			// Border Width
			$this->SetLineWidth(0.4);

			// Generate header text box
			// Top Border -- Start
			$this->position_cell('C', 'R', 80);
			$this->Cell(80, 5, '', 'T', 0);
			$this->Cell(80, 3, '', 'L', 1);
			// Top Border -- End

			// Text -- Start
			$this->position_cell('C', 'R', 80);
			$this->SetFont('Arial', 'B', 13);
			$text = urldecode($this->em("Relevé de déduction"));
			$this->Cell(80, 5, $text, 0, 0, 'C');
	
			$this->SetFont('Arial', 'B', 9);
			$text = urldecode($this->em("Modèle n° ADC082F-17I"));
			$this->Cell(90, 5, $text, 'L', 1, 'R');

			$this->SetFont('Arial', '', 10);
			$this->position_cell('C', 'R', 80);
			$text = urldecode($this->em("(Article 112 du Code Général des Impôts)"));
			$this->Cell(80, 5, $text, 0, 0, 'C');
			$this->Cell(80, 3, '', 'L', 1);
			// Text -- End

			// Bottom Border -- Start
			$this->position_cell('C', 'R', 80);
			$this->Cell(80, 5, '', 'B', 0);
			$this->Cell(80, 5, '', 'L', 1);
			// Bottom Border -- End

			$this->SetFont('Arial', '', 10);
			$text = urldecode($this->em("Mois /__/__/ Trimestre /__/ Année /__/__/__/__/"));
			$this->Cell(0, 11, $text, 0, 1, 'C');

			$this->SetFont('Arial', '', 10);
			$text = urldecode($this->em("Nom et prénom ou raison sociale : .......................................................................................................................... N° d’identification fiscale : /__/__/__/__/__/__/__/__/"));
			$this->Cell(0, 7, $text, 0, 1, 'C');

			$this->SetLineWidth(0.2);
			$this->Cell(0, 5, '', 0, 1);
			$this->setX(8);
			$this->Cell(16.2, 18.3, '', 1, 0);
			$this->Cell(15.1, 18.3, '', 1, 0);
			$this->Cell(19.6, 18.3, '', 1, 0);
			$this->Cell(35.2, 18.3, '', 1, 0);
			$this->Cell(32.57, 18.3, '', 1, 0);
			$this->Cell(27.54, 18.3, '', 1, 0);
			$this->Cell(27.25, 18.3, '', 1, 0);
			$this->Cell(19.6, 18.3, '', 1, 0);
			$this->Cell(12.43, 18.3, '', 1, 0);
			$this->Cell(18.8, 18.3, '', 1, 0);
			$this->Cell(18.53, 18.3, '', 1, 0);
			$this->Cell(18.8, 18.3, '', 1, 0);
			$this->Cell(18.5, 18.3, '', 1, 1);

			$this->fill_header();
			$this->setY(97.3);
			$this->setX(0);
		}

		public function Footer() {
			$this->setX(8);
			$this->SetFillColor(128, 128, 128);
			$this->SetFont('Arial', 'B', 11);
			$txt = urldecode($this->em("Total"));
			$this->Cell(173.45, 5.3, $txt, 1, 0);
			$this->SetFont('Arial', '', 8);
			$this->Cell(19.6, 5.3, number_format($this->mht_val, 2, '.', ' '), 1, 0, 'R');
			$this->Cell(12.43, 5.3, '', 1, 0, '', 1);
			$this->Cell(18.8, 5.3, number_format($this->tva_val, 2, '.', ' '), 1, 0, 'R');
			$this->Cell(18.53, 5.3, number_format($this->ttc_val, 2, '.', ' '), 1, 0, 'R');
			$this->Cell(37.3, 5.3, '', 1, 1, '', 1);
			$this->setX(8);
			$this->SetFont('Arial', 'B', 11);
			$txt = urldecode($this->em("Total de la TVA déductible après application du prorata visé à l’article 104 du CGI"));
			$this->Cell(205.5, 5.3, $txt, 1, 0);
			$this->SetFont('Arial', '', 8);
			$this->Cell(18.8, 5.3, number_format($this->pages_total, 2, '.', ' '), 1, 0, 'R');
			$this->Cell(55.8, 5.3, '', 1, 1, '', 1);
			$this->mht_val = 0;
			$this->tva_val = 0;
			$this->ttc_val = 0;
		}

		protected function fill_header() {
			// Date Info
			$month_val = $this->prep_num($this->data->month);
			$this->setY(59);
			$this->setX(120);
			$this->Cell(10, 5, $month_val, 0, 1, 'C');

			$tr_val = $this->data->tr;
			$this->setY(59);
			$this->setX(145);
			$this->Cell(10, 5, $tr_val, 0, 1, 'C');

			$year_val = $this->prep_num($this->data->year);
			$this->setY(59);
			$this->setX(171);
			$this->Cell(10, 5, $year_val, 0, 1, 'C');

			// User Info
			$name_val = urldecode($this->em(strtoupper($this->data->name)));
			$this->setY(67);
			$this->setX(75);
			$this->Cell(100, 5, $name_val, 0, 1);

			$if_val = $this->prep_num($this->data->if);
			$this->setY(68);
			$this->setX(252);
			$this->Cell(10, 5, $if_val, 0, 1, 'C');

			// Table info
			$this->SetFont('Arial', '', 10);
			$txt = urldecode($this->em("N° d’ordre"));
			$this->setY(84.25);
			$this->setX(8);
			$this->MultiCell(16, 4, $txt, 0, 'C');

			$txt = urldecode($this->em("N° de facture"));
			$this->setY(84.25);
			$this->setX(23.8);
			$this->MultiCell(16, 4, $txt, 0, 'C');

			$txt = urldecode($this->em("Date de la facture"));
			$this->setY(84.25);
			$this->setX(40);
			$this->MultiCell(18, 4, $txt, 0, 'C');

			$txt = urldecode($this->em("Nom et prénom ou raison sociale du fournisseur"));
			$this->setY(82.25);
			$this->setX(59);
			$this->MultiCell(35, 4, $txt, 0, 'C');

			$txt = urldecode($this->em("N° d’identification fiscale du fournisseur"));
			$this->setY(80.25);
			$this->setX(97);
			$this->MultiCell(27, 4, $txt, 0, 'C');

			$txt = urldecode($this->em("Identifiant commun de l'entreprise « ICE »"));
			$this->setY(80.25);
			$this->setX(129.5);
			$this->MultiCell(22, 4, $txt, 0, 'C');

			$txt = urldecode($this->em("Désignation des biens, travaux ou services"));
			$this->setY(80.25);
			$this->setX(157);
			$this->MultiCell(22, 4, $txt, 0, 'C');

			$txt = urldecode($this->em("Montant HT"));
			$this->setY(84.25);
			$this->setX(183.5);
			$this->MultiCell(16, 4, $txt, 0, 'C');

			$txt = urldecode($this->em("Taux"));
			$this->setY(86.25);
			$this->setX(199);
			$this->MultiCell(16, 4, $txt, 0, 'C');

			$txt = urldecode($this->em("Montant TVA"));
			$this->setY(84.25);
			$this->setX(215);
			$this->MultiCell(16, 4, $txt, 0, 'C');

			$txt = urldecode($this->em("Montant TTC"));
			$this->setY(84.25);
			$this->setX(233.5);
			$this->MultiCell(16, 4, $txt, 0, 'C');

			$txt = urldecode($this->em("Mode de paiement"));
			$this->setY(84.25);
			$this->setX(252);
			$this->MultiCell(17, 4, $txt, 0, 'C');

			$txt = urldecode($this->em("Date de paiement"));
			$this->setY(84.25);
			$this->setX(270);
			$this->MultiCell(17, 4, $txt, 0, 'C');
		}

		protected function position_cell($p, $b, $w) {
			$page_w = $this->w;
			$val;
			switch ($p) {
				case 'R':
					$val = ($page_w - $w) - $this->rMargin;
					break;
				case 'C':
					$val = (($page_w /2) - $this->rMargin) - ($w / 2);
					break;
				default:
					$val = $this->rMargin;
					break;
			}
			$this->Cell($val , 5, '', $b, 0);
		}

		protected function postion($w, $p = 'L') {
			$page_w = $this->w;
			$val;
			switch ($p) {
				case 'R':
					$val = ($page_w - $w) - 20;
					break;
				case 'C':
					$val = ($page_w - $w)/2;
					break;
				default:
					$val = 20;
					break;
			}
			return $val;
		}

		protected function em($word) {
		    $word = str_replace("»","%BB",$word);
		    $word = str_replace("«","%AB",$word);
		    $word = str_replace("’","%27",$word);
		    $word = str_replace("°","%B0",$word);
		    $word = str_replace("à","%E0",$word);
		    $word = str_replace("á","%E1",$word);
		    $word = str_replace("â","%E2",$word);
		    $word = str_replace("ç","%E7",$word);
		    $word = str_replace("è","%E8",$word);
		    $word = str_replace("é","%E9",$word);
		    $word = str_replace("ê","%EA",$word);
		    $word = str_replace("ë","%EB",$word);
		    $word = str_replace("î","%EE",$word);
		    $word = str_replace("ô","%F4",$word);
		    $word = str_replace("ù","%F9",$word);
		    $word = str_replace("ú","%FA",$word);
		    $word = str_replace("û","%FB",$word);
		    $word = str_replace("ü","%FC",$word);
		    $word = str_replace("È","%C8",$word);
		    $word = str_replace("É","%C9",$word);
		    $word = str_replace("Ê","%CA",$word);
		    $word = str_replace("À","%C0",$word);
		    $word = str_replace("Á","%C1",$word);
		    $word = str_replace("Â","%C2",$word);
		    return $word;
		}

		protected function prep_num($int) {
			$num = $this->pad($int);
			$num = str_split($num);
			$num = implode('   ', $num);
			return $num;
		}

		protected function pad($int) {
			if ($int >= 10) {
				return $int;
			}else {
				return sprintf("%02d", $int);
			}
		}

	}