<?php

	class Authenticate {

		private $request;
		private $is_auth = false;
		private $user;

		function __construct($request) {
			$this->request = $request;
			$this->jwt = $this->request->__get('JWT');
			$this->user = new User();
			$this->authenticate();
		}

		private function authenticate() {
			try {
				if(!$this->user->init_jwt($this->jwt)) throw new Exception();
				$secret = $this->user->__get('secret');
				if(!$this->user->verify_JWT($this->jwt, $secret)) throw new Exception();
				if($this->user->exp_check_JWT($this->jwt)) throw new Exception();
				if($this->user->check_renew($this->jwt)) {
					$this->user->new_jwt();
				}
				$this->is_auth = true;
			} catch (Exception $e) {
				$this->is_auth = false;
			}
		}

		public function check_user_type($types) {
			if (empty($types)) return true;
			if (!in_array($this->user->account_type, $types)) {
				return false;
			}else {
				return true;
			}
		}

		public function __get($name) {
			$method = 'get_'.$name;
			if (method_exists($this, $method)) {
				return $this->$method();
			}else {
				return null;
			}
		}

		private function get_user() {
			return $this->user;
		}

		private function get_is_auth() {
			return $this->is_auth;
		}
	}