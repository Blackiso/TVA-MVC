<?php
	
	use Models\User as User_model;

	require_once('JWT.php');
	
	class User extends JWT {
		
		private $jwt;
		private $user_id;
		private $matser_id;
		private $password;
		private $name;
		private $email;
		private $type;
		private $secret;
		private $user_type;
		private $blocked;

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

		public function init_email($email) {
			$this->email = $email;
			$this->user_type = preg_match('/(\.edg)$/', $this->email) ? 'user' : 'master';
			$data = $this->get_user_from_email();
			if (!empty($data)) {
				$data = (object)$data;
				$this->user_id = $data->user_id;
				$this->master_id = $this->user_type == 'user' ? $data->master_id : $data->user_id;
				$this->name = $data->name;
				$this->email = $data->email;
				$this->password = $data->password;
				$this->type = $data->account_type ?? null;
				$this->blocked = $data->blocked ?? null;
				return true;
			}else {
				return false;
			}
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
			}else if (property_exists($this, $name)) {
				return $this->$name;
			}else {
				return null;
			}
		}

		public function __set($name, $value) {
			if (property_exists($this, $name)) {
				$this->$name = $value;
			}
		}

		public function get_user_from_email() {
			return User_model::get_user_from_email($this->email, $this->user_type);
		}

		private function get_secret() {
			if ($this->secret == null) {
				return $this->secret = User_model::get_secret($this->user_id, $this->user_type);
			}
			return $this->secret;
		}
	}