<?php

	abstract class Routes {
		
		private $params = [];
		private $routes = [
			[
				'uri' => '/api/authentication/login',
				'controller' => 'Authentication',
				'action' => 'login',
				'auth' => false,
				'types' => [],
				'method' => 'POST'
			],
			[
				'uri' => '/api/authentication/register',
				'controller' => 'Authentication',
				'action' => 'register',
				'auth' => false,
				'types' => [],
				'method' => 'POST'
			],
			[
				'uri' => '/api/authentication/logout',
				'controller' => 'Authentication',
				'action' => 'logout',
				'auth' => true,
				'types' => [],
				'method' => 'POST'
			],
			[
				'uri' => '/api/authentication/authenticate',
				'controller' => 'Authentication',
				'action' => 'authenticate',
				'auth' => true,
				'types' => [],
				'method' => 'GET'
			],
			[
				'uri' => '/api/users',
				'controller' => 'Users',
				'action' => null,
				'auth' => true,
				'types' => ['premium'],
				'method' => 'POST'
			],
			[
				'uri' => '/api/users/:user-id/add',
				'controller' => 'Users',
				'action' => 'add_users',
				'auth' => true,
				'types' => ['premium'],
				'method' => 'POST'
			],
			[
				'uri' => '/api/users/:company-id/all',
				'controller' => 'Users',
				'action' => 'all_users',
				'auth' => true,
				'types' => ['premium'],
				'method' => 'GET'
			],
			[
				'uri' => '/api/users/:user-id/delete',
				'controller' => 'Users',
				'action' => 'users',
				'auth' => true,
				'types' => ['premium'],
				'method' => 'DELETE'
			],
			[
				'uri' => '/api/users/search',
				'controller' => 'Users',
				'action' => 'search_users',
				'auth' => true,
				'types' => ['premium'],
				'method' => 'GET'
			],
			[
				'uri' => '/api/companies',
				'controller' => 'Companies',
				'action' => null,
				'auth' => true,
				'types' => ['premium', 'regular'],
				'method' => 'POST'
			],
			[
				'uri' => '/api/companies/all',
				'controller' => 'Companies',
				'action' => 'all',
				'auth' => true,
				'types' => [],
				'method' => 'GET'
			],
			[
				'uri' => '/api/companies/:company-id',
				'controller' => 'Companies',
				'action' => 'company',
				'auth' => true,
				'types' => [],
				'method' => 'GET'
			],
			[
				'uri' => '/api/companies/:company-id/delete',
				'controller' => 'Companies',
				'action' => 'company_dlt',
				'auth' => true,
				'types' => ['premium', 'regular'],
				'method' => 'DELETE'
			],
			[
				'uri' => '/api/companies/:company-id/update',
				'controller' => 'Companies',
				'action' => 'company_upd',
				'auth' => true,
				'types' => ['premium', 'regular'],
				'method' => 'PATCH'
			]
		];

		protected function get_route($uri, $method) {
			foreach ($this->routes as $route) {
				$route_uri = $this->parse_new_uri($route['uri'], $uri);
				if ($route_uri == $uri && $route['method'] == $method) {
					return $route;
				}
			}
		}

		protected function get_params_() {
			return $this->params;
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
					$this->set_params(ltrim($item, ':'), $uri[$i]);
				}
			}
			array_unshift($pattern, '');
			return implode('/', $pattern);
		}

		private function set_params($key, $val) {
			$this->params[$key] = $val;
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