<?php
	
	use Models\User as User_model;

	require_once('JWT.php');
	
	class User extends JWT {
		
		private $jwt;
		private $user_id;
		private $matser_id;
		private $name;
		private $email;
		private $type;
		private $secret;
		private $user_type;

		function __construct() {
			User_model::init();
		}

		public function init_jwt($key) {
			$this->jwt = $key;
			$data = $this->get_user_info($this->jwt);
			if ($data !== null && $this->check_data($data)) {
				$this->user_id = $data->uid;
				$this->master_id = $data->mid;
				$this->name = $data->nam;
				$this->email = $data->eml;
				$this->type = $data->typ;
				$this->user_type = $this->master_id == $this->user_id ? 'master' : 'user';
				return true;
			}
			return false;
		}

		private function check_data($data) {
			$keys = ['uid', 'mid', 'nam', 'eml', 'typ', 'exp', 'rnw'];
			if (is_object($data)) {
				foreach($data as $key => $val) {
					if (!in_array($key, $keys)) {
						return false;
					}
				}
			}
			return true;
		}

		public function __get($name) {
			$method = 'get_'.$name;
			if (method_exists($this, $method)) {
				return $this->$method();
			}else {
				return null;
			}
		}

		public function __set($name, $value) {
			if (property_exists($this, $name)) {
				$this->$name = $value;
			}
		}

		public function check_user($data) {
			if (!is_object($data)) $data = (object)$data;
			return User_model::check_user($data->password, $data->email, $this->user_type);
		}

		private function get_secret() {
			return $this->secret = User_model::get_secret($this->user_id, $this->user_type);
		}
	}