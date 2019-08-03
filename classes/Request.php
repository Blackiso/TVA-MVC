<?php 

	class Request {

		private $_URI;
		private $parsedURI;
		private $querys;

		function __construct() {
			$this->_URI = $_SERVER['REQUEST_URI'];
			$this->method = $_SERVER['REQUEST_METHOD'];
			$this->querys = $this->parse_querys($_SERVER['QUERY_STRING']);
			$this->parse_URI();
		}

		private function parse_URI() {
			$parsed = explode('?', $this->_URI)[0];
			$_parsed = explode('/', $parsed);
			$this->parsedURI = $parsed;
		}

		public function __get($name) {
			$method = 'get_'.$name;
			if (method_exists($this, $method)) {
				return $this->$method();
			}else if (property_exists($this, $name)) {
				return $this->$name;
			}else {
				return null;
			}
		}

		private function parse_querys($str) {
			if ($str == "" or $str == null) {
				return null;
			}
			$qr_arr = explode('&', $str);
			$return = [];
			foreach ($qr_arr as $qr) {
				$x = explode('=', $qr);
				$return[$x[0]] = $x[1] ?? null;
			}
			return $return; 
		}

		private function get_ip() {
			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
	     		$ip = $_SERVER['HTTP_CLIENT_IP'];
		    }elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		     	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		    }else {
		     	$ip = $_SERVER['REMOTE_ADDR'];
		    }
		    return $ip;
		}

		private function get_agent() {
		    return $_SERVER['HTTP_USER_AGENT'] ?? null;
		}

		private function json_strip_tags($array) {
			$str = json_encode($array);
			$str = strip_tags($str);
			$str = preg_replace("(['])", "", $str);
			return json_decode($str);
		}

		public function get_uri() {
			return $this->parsedURI;
		}

		public function get_body() {
			$entity_body = file_get_contents('php://input');
			return $this->json_strip_tags(json_decode($entity_body));
		}

		public function get_JWT() {
			return $this->cookie('JWT');
		}

		public function cookie($name) {
			return $_COOKIE[$name] ?? null;
		}

	}
