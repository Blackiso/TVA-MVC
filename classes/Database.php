<?php

	class Database {

		// private $db_user = "root";
		// private $db_pass = "";
		// private $db_host = "127.0.0.1";
		// private $db_name = "tva_app2";

		private $db_user = "root";
		private $db_pass = "8u1xfmUxPay2";
		private $db_host = "localhost";
		private $db_name = "tva_app2";

		// private $db_user = "b13_24305872";
		// private $db_pass = "ismailismail123";
		// private $db_host = "sql105.byethost13.com";
		// private $db_name = "b13_24305872_api";

		public static $instence;
		private $conn;

		public static function get_instence() {
			if (self::$instence == null) {
				self::$instence = new Database();
			}
			return self::$instence;
		}

		private function __construct() {
			try {
				$pdo_init = 'mysql:host='.$this->db_host;
				$pdo_init .= ';dbname='.$this->db_name;		
				$this->conn = new PDO($pdo_init, $this->db_user, $this->db_pass);
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) {
			    $this->db_error($e->getMessage());
			}
		}

		private function query($stm) {
			try {
				$query = $this->conn->prepare($stm);
				return [$query->execute(), $query];
			} catch (PDOException $e) {
				$this->db_error($e->getMessage());
			}
		}

		public function select($stm) {
			$return = $this->query($stm);
			$return = $return[1]->fetchAll(PDO::FETCH_ASSOC);
			if (sizeof($return) == 1) $return = $return[0];
			return $return;
		}

		public function update($stm) {
			$return = $this->query($stm);
			return true;
		}

		public function insert($stm) {
			$return = $this->query($stm);
			$result = (object)array();
			$result->insert_id = $this->conn->lastInsertId();
			$result->query = $return[0];
			return $result;
		}

		public function pagination($query, $index = 0, $order = null, $max = 10) {
			$page_start = $index;
			$query = $query;
			if (preg_match('/(GROUP)/', $query)) {
				$last_part = preg_match('/(?<=FROM).*(?=GROUP)/', $query, $matches);
			}else {
				$last_part = preg_match('/(?<=FROM).*$/', $query, $matches);
			}
			
			$last_part = $matches[0];
			
			if ($order !== null) {
				$query .= " ORDER BY $order";
			}
			$query .= " LIMIT $page_start, $max";

			$qr_result = $this->query($query);
			if (array_keys($qr_result) !== range(0, count($qr_result) - 1) AND !empty($qr_result)) {
				$qr_result = array($qr_result);;
			}
			$result = (object)array();
			$result->data = $qr_result;
			$result->max_index = $this->query("SELECT count(*) as num FROM $last_part")['num'];
			return $result;
		}

		private function db_error($msg) {
			echo json_encode(array('error' => $msg));
			exit();
		}
	}