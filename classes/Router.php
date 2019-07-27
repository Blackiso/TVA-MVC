<?php

	class Router extends Routes {

		private $uri;
		private $route;
		
		function __construct($uri) {
			$this->uri = rtrim($uri);
			$this->route = $this->get_route($uri);
		}

		public function __get($name) {
			$method = 'get_'.$name;
			if (method_exists($this, $method)) {
				return $this->$method();
			}else {
				return null;
			}
		}

		private function get_controller() {
			return $this->route['controller'];
		}

		private function get_auth() {
			if ($this->route['auth']) {
				return $this->route['types'];
			}

			return false;
		}

		private function get_action() {
			return $this->route['action'];
		}

		private function get_types() {
			return $this->route['types'];
		}

		private function get_params() {
			return $this->get_params_();
		}
	}