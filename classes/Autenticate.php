<?php

	class Authenticate {

		private $request;
		private $is_auth = false;
		private $user;

		function __construct($request) {
			$this->request = $request;
			$this->jwt = $this->request->__get('JWT');
			$this->authenticate();
		}

		private function authenticate() {		
			$this->user = new User();
			if ($this->user->init_jwt($this->jwt)) {
				$secret = $this->user->__get('secret');
				if ($this->user->verify_JWT($this->jwt, $secret)) {
					$this->is_auth = true;
				}else {
					$this->is_auth = false;
				}
			}else {
				$this->is_auth = false;
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