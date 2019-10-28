<?php
	
	use Models\Authentication as AuthModel;
	use Models\Payment as PaymentModel;
	use Models\Account as AccountModel;

	require_once('JWT.php');
	
	class User extends JWT {
		
		private $jwt;
		private $user_id;
		private $master_id;
		private $password;
		private $name;
		private $email;
		private $account_type;
		private $secret;
		private $user_type;
		private $blocked;
		private $active;

		function __construct() {
			AuthModel::init();
		}

		public function init_data($data) {
			$this->user_id = $data->user_id;
			$this->master_id = $data->user_id;
			$this->name = $data->name;
			$this->email = $data->email;
			$this->account_type = $data->account_type ?? "user";
			$this->user_type = $data->user_type;
			$this->secret = $data->secret;
			$this->active = false;
			return true;
		}

		public function init_jwt($key) {
			$this->jwt = $key;
			$data = $this->get_user_info($this->jwt);
			if ($data !== null && $this->check_data($data)) {
				$this->user_id = $data->uid;
				$this->master_id = $data->mid;
				$this->name = $data->nam;
				$this->email = $data->eml;
				$this->account_type = $data->typ;
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
				$this->account_type = $data->account_type ?? 'user';
				$this->blocked = $data->blocked ?? null;
				$this->active = $data->active ?? null;
				return true;
			}else {
				return false;
			}
		}

		public function check_expire() {
			if ($this->user_type == 'user') {
				$account_type = PaymentModel::get_account_type($this->master_id);
				if ($account_type == 'pending') return true;
			}

			if ($this->account_type == "pending") return false;

			$expire_date = PaymentModel::get_expire_date($this->master_id);
			if (!$expire_date) {
				$this->reset_account();
				return true;
			}

			$expire_date = strtotime($expire_date);
			$now = time();
			if ($expire_date < $now) {
				$this->reset_account();
				return true;
			}

			return false;
		}

		public function reset_account() {
			AccountModel::update_account(['account_type' => 'pending', 'refund' => 0], $this->master_id);
			$this->account_type = "pending";
			$this->revoke_secret();
		}

		public function check_block() {
			if ($this->user_type == 'user') {
				if (isset($this->blocked) && $this->blocked) {
					return true;
				}else {
					return $this->get_block();
				}
			}
			return false;
		}

		public function generate_jwt($revoke = true) {
			if ($revoke) {
				$this->revoke_secret();
				$this->get_new_info();
			}
			$payload = (object) array();
			$payload->uid = $this->user_id;
			$payload->mid = $this->master_id;
			$payload->nam = $this->name;
			$payload->eml = $this->email;
			$payload->typ = $this->account_type;
			$payload->act = (bool)$this->active;
			return $this->sign_JWT($payload, $this->get_secret());
		}

		public function revoke_secret() {
			$this->secret = $this->revoke_key($this->get_secret());
			$this->update_user(['secret' => $this->secret]);
		}

		private function check_data($data) {
			$keys = ['uid', 'mid', 'nam', 'eml', 'typ', 'exp', 'rnw', 'act'];
			if (is_object($data)) {
				foreach($data as $key => $val) {
					if (!in_array($key, $keys)) {
						return false;
					}
				}
			}
			return true;
		}

		private function get_new_info() {
			$data = ['email', 'name', 'account_type', 'secret'];
			if ($this->user_type == 'master') array_push($data, 'active');
			$data = AuthModel::get_info($this->user_id, $this->user_type, $data);
			$this->name = $data['name'];
			$this->email = $data['email'];
			$this->secret = $data['secret'];
			$this->account_type = $data['account_type'];
			if (isset($data['active'])) {
				$this->active = (bool)$data['active'];
			}else {
				$this->active = null;
			}
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

		public function new_jwt($revoke = true) {
			$jwt = $this->generate_jwt($revoke);
			$this->insert_JWT($jwt);
		}

		public function get_user_from_email() {
			return AuthModel::get_user_from_email($this->email, $this->user_type);
		}

		public function update_user($updates) {
			return AuthModel::update_user($updates, $this->user_type, $this->user_id);
		}

		public function get_block() {
			return AuthModel::user_block($this->user_id);
		}

		private function get_secret() {
			if ($this->secret == null) {
				return $this->secret = AuthModel::get_info($this->user_id, $this->user_type, ['secret'])['secret'];
			}
			return $this->secret;
		}

		private function get_password() {
			if ($this->password == null) {
				return $this->password = AuthModel::get_info($this->user_id, $this->user_type, ['password'])['password'];
			}
			return $this->password;
		}

		private function get_info() {
			$info = [
				'user_id' => $this->user_id,
				'name' => $this->name,
				'email' => $this->email,
				'type' => $this->account_type,
				'active' => (bool)$this->active
			];
			return $info;
		}
	}