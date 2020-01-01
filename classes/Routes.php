<?php

	abstract class Routes {
		
		private $params = [];
		private $routes = [
			//Authentication Routes
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
			///////////////////////////////////////////////
			// Users Routes
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
				'uri' => '/api/users/:user-id/block',
				'controller' => 'Users',
				'action' => 'block_user',
				'auth' => true,
				'types' => ['premium'],
				'method' => 'POST'
			],
			[
				'uri' => '/api/users/:user-id/unblock',
				'controller' => 'Users',
				'action' => 'unblock_user',
				'auth' => true,
				'types' => ['premium'],
				'method' => 'POST'
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
				'uri' => '/api/users/:user-id/update',
				'controller' => 'Users',
				'action' => 'update_user',
				'auth' => true,
				'types' => ['premium'],
				'method' => 'PATCH'
			],
			////////////////////////////////////////////
			// Companies Routes
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
				'types' => ['regular', 'premium', 'user'],
				'method' => 'GET'
			],
			[
				'uri' => '/api/companies/search',
				'controller' => 'Companies',
				'action' => 'companies_search',
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
				'method' => 'GET'
			],
			[
				'uri' => '/api/companies/:company-id',
				'controller' => 'Companies',
				'action' => 'company',
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
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
			],
			[
				'uri' => '/api/companies/:company-id/stats',
				'controller' => 'Companies',
				'action' => 'stats',
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
				'method' => 'GET'
			],
			/////////////////////////////////////////////
			// Files Routes
			[
				'uri' => '/api/files/:company-id',
				'controller' => 'Files',
				'action' => 'files',
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
				'method' => 'POST'
			],
			[
				'uri' => '/api/files/:company-id',
				'controller' => 'Files',
				'action' => 'files',
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
				'method' => 'GET'
			],
			[
				'uri' => '/api/files/:file-id',
				'controller' => 'Files',
				'action' => 'files',
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
				'method' => 'PATCH'
			],
			[
				'uri' => '/api/files/:file-id/details',
				'controller' => 'Files',
				'action' => 'details',
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
				'method' => 'GET'
			],
			[
				'uri' => '/api/files/delete',
				'controller' => 'Files',
				'action' => 'files',
				'auth' => true,
				'types' => ['regular', 'premium'],
				'method' => 'DELETE'
			],
			[
				'uri' => '/api/files/:company-id/search',
				'controller' => 'Files',
				'action' => 'search_files',
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
				'method' => 'GET'
			],
			/////////////////////////////////////////////
			// Bills Routes
			[
				'uri' => '/api/bills/:file-id',
				'controller' => 'Bills',
				'action' => null,
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
				'method' => 'POST'
			],
			[
				'uri' => '/api/bills/:file-id/:month',
				'controller' => 'Bills',
				'action' => null,
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
				'method' => 'GET'
			],
			[
				'uri' => '/api/bills/suppliers',
				'controller' => 'Bills',
				'action' => 'suppliers',
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
				'method' => 'GET'
			],
			[
				'uri' => '/api/bills/dbs',
				'controller' => 'Bills',
				'action' => 'dbs',
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
				'method' => 'GET'
			],
			[
				'uri' => '/api/bills/:bill-id',
				'controller' => 'Bills',
				'action' => null,
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
				'method' => 'PATCH'
			],
			[
				'uri' => '/api/bills/delete',
				'controller' => 'Bills',
				'action' => null,
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
				'method' => 'DELETE'
			],
			[
				'uri' => '/api/bills/:file-id/:month/search',
				'controller' => 'Bills',
				'action' => 'search',
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
				'method' => 'GET'
			],
			[
				'uri' => '/api/bills/:file-id/:month/table',
				'controller' => 'Bills',
				'action' => 'table',
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
				'method' => 'GET'
			],
			/////////////////////////////////////////////
			// Download Routes
			[
				'uri' => '/api/download/:file-id/:month/xml',
				'controller' => 'Download',
				'action' => 'xml',
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
				'method' => 'GET'
			],
			[
				'uri' => '/api/download/:file-id/:month/pdf',
				'controller' => 'Download',
				'action' => 'pdf',
				'auth' => true,
				'types' => ['regular', 'premium', 'user'],
				'method' => 'GET'
			],
			/////////////////////////////////////////////
			// Payment Routes
			[
				'uri' => '/api/payment/create',
				'controller' => 'Payment',
				'action' => 'create',
				'auth' => true,
				'types' => ['premium', 'pending'],
				'method' => 'POST'
			],
			[
				'uri' => '/api/payment/capture',
				'controller' => 'Payment',
				'action' => 'capture',
				'auth' => true,
				'types' => ['premium', 'pending'],
				'method' => 'GET'
			],
			[
				'uri' => '/api/payment/history',
				'controller' => 'Payment',
				'action' => 'history',
				'auth' => true,
				'types' => ['premium'],
				'method' => 'GET'
			],
			[
				'uri' => '/api/payment/:payment-id/refund',
				'controller' => 'Payment',
				'action' => 'refund',
				'auth' => true,
				'types' => ['premium'],
				'method' => 'GET'
			],
			/////////////////////////////////////////////
			// Account Routes
			[
				'uri' => '/api/account/update',
				'controller' => 'Account',
				'action' => null,
				'auth' => true,
				'types' => ['premium'],
				'method' => 'PATCH'
			],
			[
				'uri' => '/api/account/details',
				'controller' => 'Account',
				'action' => null,
				'auth' => true,
				'types' => ['premium'],
				'method' => 'GET'
			],
			[
				'uri' => '/api/account/support',
				'controller' => 'Account',
				'action' => null,
				'auth' => false,
				'types' => [],
				'method' => 'POST'
			],
			[
				'uri' => '/api/account/email/send',
				'controller' => 'Account',
				'action' => 'send_email',
				'auth' => true,
				'types' => ['pending', 'premium', 'regular'],
				'method' => 'POST'
			],
			[
				'uri' => '/api/account/email/verify',
				'controller' => 'account',
				'action' => 'verify_email',
				'auth' => true,
				'types' => ['pending', 'premium', 'regular'],
				'method' => 'POST'
			],
			[
				'uri' => '/api/account/reset',
				'controller' => 'Account',
				'action' => 'reset_password',
				'auth' => false,
				'types' => [],
				'method' => 'POST'
			],
			[
				'uri' => '/api/account/reset',
				'controller' => 'Account',
				'action' => 'reset_password',
				'auth' => false,
				'types' => [],
				'method' => 'PATCH'
			],
			/////////////////////////////////////////////
			[
				'uri' => '/api/logger',
				'controller' => 'System',
				'action' => 'logs',
				'auth' => false,
				'types' => [],
				'method' => 'POST'
			]
			/////////////////////////////////////////////
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