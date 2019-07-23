<?php

	abstract class Routes {
		
		private $routes = [
			[
				'uri' => '/api/authentication/login',
				'controller' => 'Authentication',
				'action' => 'login',
				'auth' => false,
				'types' => []
			],
			[
				'uri' => '/api/authentication/register',
				'controller' => 'Authentication',
				'action' => 'register',
				'auth' => false,
				'types' => []
			],
			[
				'uri' => '/api/authentication/logout',
				'controller' => 'Authentication',
				'action' => 'logout',
				'auth' => true,
				'types' => []
			],
			[
				'uri' => '/api/authentication/authenticate',
				'controller' => 'Authentication',
				'action' => 'authenticate',
				'auth' => true,
				'types' => []
			],
			[
				'uri' => '/api/users',
				'controller' => 'Users',
				'action' => null,
				'auth' => true,
				'types' => ['master']
			],
			[
				'uri' => '/api/users/:user',
				'controller' => 'Users',
				'action' => 'user',
				'auth' => true,
				'types' => ['master']
			]
		];

		protected function get_route($uri) {
			foreach ($this->routes as $route) {
				$route_uri = $this->parse_new_uri($route['uri'], $uri);
				if ($route_uri == $uri) {
					return $route;
				}
			}
		}

		private function uri_to_arr($uri) {
			return $this->clear_empty(explode('/', $uri));
		}

		private function parse_new_uri($pattern, $uri) {
			$uri = $this->uri_to_arr($uri);
			$pattern = $this->uri_to_arr($pattern);

			if (sizeof($uri) !== sizeof($pattern)) return null;

			foreach ($pattern as $i => $item) {
				if (preg_match('/^(:)/', $item)) {
					$pattern[$i] = $uri[$i];
				}
			}
			array_unshift($pattern, '');
			return implode('/', $pattern);
		}

		private function clear_empty($arr) {
			$return = [];
			foreach ($arr as $x) {
				if ($x !== "") {
					array_push($return, $x);
				}
			}
			return $return;
		}

	}