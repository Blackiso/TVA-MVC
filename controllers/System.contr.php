<?php 

	namespace Controllers {

		use Views\View as View;

		class System extends Controller  {
			
			private static $logs_file_ = "logs";
			private static $logs_file = "/opt/bitnami/apache2/htdocs/backend/logs_14112019";

			public static function POST_logs() {
				$_data = self::$request->body;
				$cookies = "false";
				$browser = self::get_browser();
				$browser = $browser['name']." ".$browser['version'] . " on " .$browser['platform'];
				if (isset($_COOKIE['_CAT'])) {
					if ($_COOKIE['_CAT'] == $_data->c_id) {
						$cookies = "true";
					}
				}
				$data = "[".date("Y-m-d H:i:s", time())."] ".self::$request->ip." => ".$browser." | cookies ? ".$cookies." | ".$_data->url;
				file_put_contents(self::$logs_file, $data.PHP_EOL , FILE_APPEND | LOCK_EX);
				View::response();
			}
			
			private static function get_browser() {
				$u_agent = $_SERVER['HTTP_USER_AGENT'];
				$bname = 'Unknown';
				$platform = 'Unknown';
				$version= "";

				if (preg_match('/linux/i', $u_agent)) {
					$platform = 'linux';
				}elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
					$platform = 'mac';
				}elseif (preg_match('/windows|win32/i', $u_agent)) {
					$platform = 'windows';
				}

				if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)){
					$bname = 'Internet Explorer';
					$ub = "MSIE";
				}elseif(preg_match('/Firefox/i',$u_agent)){
					$bname = 'Mozilla Firefox';
					$ub = "Firefox";
				}elseif(preg_match('/OPR/i',$u_agent)){
					$bname = 'Opera';
					$ub = "Opera";
				}elseif(preg_match('/Chrome/i',$u_agent) && !preg_match('/Edge/i',$u_agent)){
					$bname = 'Google Chrome';
					$ub = "Chrome";
				}elseif(preg_match('/Safari/i',$u_agent) && !preg_match('/Edge/i',$u_agent)){
					$bname = 'Apple Safari';
					$ub = "Safari";
				}elseif(preg_match('/Netscape/i',$u_agent)){
					$bname = 'Netscape';
					$ub = "Netscape";
				}elseif(preg_match('/Edge/i',$u_agent)){
					$bname = 'Edge';
					$ub = "Edge";
				}elseif(preg_match('/Trident/i',$u_agent)){
					$bname = 'Internet Explorer';
					$ub = "MSIE";
				}

				$known = array('Version', $ub, 'other');
				$pattern = '#(?<browser>' . join('|', $known) .')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
				if (!preg_match_all($pattern, $u_agent, $matches)) {
				}

				$i = count($matches['browser']);
				if ($i != 1) {
					if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
						$version= $matches['version'][0];
					}else {
						$version= $matches['version'][1];
					}
				}else {
					$version= $matches['version'][0];
				}

				if ($version==null || $version=="") {$version="?";}

				return array(
					'userAgent' => $u_agent,
					'name'      => $bname,
					'version'   => $version,
					'platform'  => $platform,
					'pattern'    => $pattern
				);
			}
		}
	}
