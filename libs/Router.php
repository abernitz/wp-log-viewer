<?php 
	
namespace Allbitsnbytes\WPLogViewer;

if (!defined('WPLOGVIEWER_BASE')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}


/**
 * Dependencies
 */
use Allbitsnbytes\WPLogViewer\Auth;
use Allbitsnbytes\WPLogViewer\Characteristic\IsSingleton;
use Allbitsnbytes\WPLogViewer\Helper;
use Allbitsnbytes\WPLogViewer\Http\Request;
use Allbitsnbytes\WPLogViewer\Http\Response;


/**
 * Request router
 *
 * @since 0.1.0
 */
class Router {
	
	use IsSingleton;
	
	/**
	 * @var array Registered handlers
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	private $handlers = [];
	
	/**
	 * Get all request headers
	 *
	 * @since 0.1.0
	 *
	 * @return array The request headers
	 */
	private function get_request_headers() {
		if (function_exists('getallheaders')) {
			return getallheaders();
		}
		
		$headers = [];
		
		foreach ($_SERVER as $key => $value) {
			if ((substr($key, 0, 5) == 'HTTP_')) {
				$headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($key, 5))))] = $value;
			}
		}
		
		return $headers;
	}
	
	
	/**
	 * Get request method
	 *
	 * @since 0.1.0
	 *
	 * @return string The request method
	 */
	private function get_request_method() {
		return $_SERVER['REQUEST_METHOD'];
	}
	
	
	/**
	 * Get requested action
	 *
	 * @since 0.1.0
	 *
	 * @return string The requested action
	 */
	private function get_action() {
		return isset($_REQUEST['do']) ? $_REQUEST['do'] : '';
	}
	
	
	/**
	 * Get parameters sent in the request
	 *
	 * @since 0.1.0
	 *
	 * @return array Array of parameters
	 */
	private function get_params() {
		$params = $_REQUEST;
		
		unset($params['do']);
		
		return $params;
	}

	
	/**
	 * Register an action and handler
	 *
	 * @since 0.1.0
	 *
	 * @param string $action The action to handle
	 * @param array $handler An array with the following keys: method, call, auth.  Method must be either POST or GET, call must be a callable function, and auth a boolean.
	 * @return void
	 */
	public function handle($action, $handler) {
		if (!isset($this->handlers[$action]) || !is_array($this->handlers[$action])) {
			$this->handlers[$action] = [];
		}
		
		if (is_array($handler) && isset($handler['method']) && isset($handler['call']) && isset($handler['auth']) && is_callable($handler['call'])) {
			$this->handlers[$action][] = $handler;
		}
	}
	
	
	/**
	 * Register a GET handler
	 *
	 * @since 0.1.0
	 *
	 * @param string $action The action to handle
	 * @param callable $fn The callable function to call if action is matched
	 * @param int $auth_code The authentication code, Check Allbitsnbytes\WPLogViewer\Auth for more details
	 * @return void
	 */
	public function get($action, $fn, $auth_code=Auth::AUTHENTICATED) {
		$this->handle($action, [
			'method'	=> 'GET',
			'call'		=> $fn,
			'auth'		=> $auth_code,
		]);
	}
	
	
	/**
	 * Register a POST handler
	 *
	 * @since 0.1.0
	 *
	 * @param string $action The action to handle
	 * @param callable $fn The callable function to call if action is matched
	 * @param int $auth_code The authentication code, Check Allbitsnbytes\WPLogViewer\Auth for more details
	 * @return void
	 */
	public function post($action, $fn, $auth_code=Auth::AUTHENTICATED) {
		$this->handle($action, [
			'method'	=> 'POST',
			'call'		=> $fn,
			'auth'		=> $auth_code,
		]);
	}


	/**
	 * Process incoming request
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function run() {
		$headers = $this->get_request_headers();
		$method = $this->get_request_method();
		$action = $this->get_action();
		$params = $this->get_params();
		$request = new Request($action, $method, $headers, $params);
		$response = new Response();
		$handled = 0;

		if (isset($this->handlers[$action])) {
			$handlers = $this->handlers[$action];

			foreach ($handlers as $handler) {
				if ($method === strtoupper($handler['method'])) {
					// Default to authenticated.  If skip is not specified, authenticate
					if ($handler['auth'] !== Auth::SKIP) {
						$auth = Auth::get_instance();

						if ((!isset($headers['wplv-cookie']) || !isset($headers['wplv-session'])) && ($handler['auth'] === Auth::AUTHENTICATED && !$auth->is_authenticated_api_session())) {
								$response->set_code(401);
								$response->send();
						}
					}

					if (is_callable($handler['call'])) {
						// Execute handler
						$response = call_user_func_array($handler['call'], [$request, $response]);

						// If response instance was not returned, let's invalite this request
						if (!isset($response) || !is_object($response)) {
							$response = new Response(400);
							$response->send();
						}

						$handled++;
					}
				}
			}
		}

		if ($handled === 0) {
			$response->set_code(404);
		} 

		$response->send();
	}
}