<?php
/**
 * @author    Amarjeet Kaur
 * @package   Webservices
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link      http://github.com/amarjeetkaurgill/webservices
 */

require_once('database/Connection.php');
require_once('api/response.php');

/**
 * @author Amarjeet Kaur
 *
 * This will be the generic file for handling API requests.
 * All basic validations and filtration will be handled though it
 */
class REST
{
	const REQUEST_GET    = 'GET';
	const REQUEST_POST   = 'POST';
	const REQUEST_PUT    = 'PUT';
	const REQUEST_DELETE = 'DELETE';

	const FIELD_EMPTY    = 'Empty field provided';
	const FIELD_REQUIRED = 'Field required';

	/**
	 * @var MysqliDb
	 */
	public $db;

	/*
     * @var array
	 * This container will be used to handle POST request parameters
	 */
	public $resourceParameters = [];

	/*
	 * @var array
	 * This container will be used to handle GET request parameters
	 */
	public $requestParameters = [];

	/**
	 * @author Amarjeet Kaur
	 */
	public function __construct()
	{
		$this->SetRequestParameters()
			 ->SetResourceParameters();

		$this->db = Connection::Get();
	}

	/**
	 * While processing an API request, system needs to know the request type.
	 * This method will provide us the received request type.
	 *
	 * @return string
	 */
	public function GetRequestType()
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Whenever any GET request will be processed, we will populate the request parameters
	 *
	 * @return REST
	 */
	public function SetRequestParameters()
	{
		if ($this->GetRequestType() == self::REQUEST_GET) {
			$this->requestParameters = $this->CleanParameters($_GET);
		} else {
			$queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
			parse_str($queryString, $parameters);
			$this->requestParameters = $this->CleanParameters($parameters);
		}

		return $this;
	}

	/**
	 * When we need to use request parameters, this function will be called
	 *
	 * @return array
	 */
	public function GetRequestParameters()
	{
		return $this->requestParameters;
	}

	/**
	 * Whenever any POST request will be processed, we will populate the resource parameters
	 *
	 * @return REST
	 */
	public function SetResourceParameters()
	{
		if ($this->GetRequestType() == self::REQUEST_POST) {
			$this->resourceParameters = $this->CleanParameters($_POST);
		} else if ($this->GetRequestType() == self::REQUEST_PUT) {
			$this->parse_raw_http_request($this->resourceParameters);
		}

		return $this;
	}

	/**
	 * When we need to use resource parameters, this function will be called
	 *
	 * @return array
	 */
	public function GetResourceParameters()
	{
		return $this->resourceParameters;
	}

	/**
	 * This will help us in keeping data sanity checks
	 *
	 * @param array|string $data
	 *
	 * @return array|string
	 */
	private function CleanParameters($data)
	{
		$parameters = [];
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$parameters[$key] = $this->CleanParameters($value);
			}
		} else {
			$parameters = trim(strip_tags($data));
		}

		return $parameters;
	}

	/**
	 * This function will handle empty validation checks for POST/PUT requests
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function ValidateInput($fields = [])
	{
		$errors             = [];
		$resourceParameters = $this->GetResourceParameters();
		foreach ($fields as $field) {
			if (!array_key_exists($field, $resourceParameters)) {
				$errors[$field] = self::FIELD_REQUIRED;
			}
		}

		foreach ($resourceParameters as $key => $value) {
			if (empty($value)) {
				$errors[$key] = self::FIELD_EMPTY;
			}
		}

		return $errors;
	}

	/**
	 * We need to authenticate each request before performing any action.
	 * This function will authenticate requests for valid session id
	 */
	public function IsAuthenticated()
	{
		$requestParameters  = $this->GetRequestParameters();
		$resourceParameters = $this->GetResourceParameters();

		$sessionID = '';
		$user      = [];
		if (!empty($requestParameters['session_id'])) {
			$session = $this->db->where('session_key', $requestParameters['session_id'])
								->getOne('sessions');

			if (count($session) > 0) {
				$sessionID = $requestParameters['session_id'];

				$user = $this->db->where('user_id', $session['user_id'])
								 ->getOne('users');
			}
		} else if (!empty($resourceParameters['email']) && !empty($resourceParameters['password'])) {
			$user = $this->db->where('user_email', $resourceParameters['email'])
							 ->where('user_password', md5($resourceParameters['password']))
							 ->getOne('users');
			if (count($user) > 0) {
				$session = $this->db->where('user_id', $user['user_id'])
									->getOne('sessions');

				if (count($session) == 0) {
					$hash = $this->BuildHashBlock() . $this->BuildHashBlock() . $this->BuildHashBlock() . $this->BuildHashBlock();
					$this->db->insert('sessions', ['session_key' => $hash, 'user_id' => $user['user_id'], 'updated_at' => time()]);

					$sessionID = $hash;
				} else {
					$this->db->where('user_id', $user['user_id'])
							 ->update('sessions', ['updated_at' => time()]);

					$sessionID = $session['session_key'];
				}
			}
		}

		if (!empty($sessionID) && !empty($user)) {
			return ['user_id'    => $user['user_id'],
					'full_name'  => $user['user_fullname'],
					'email'      => $user['user_email'],
					'status'     => $user['user_status'],
					'session_id' => $sessionID];
		}

		return false;
	}

	/**
	 * Build a Unique Hash Block
	 *
	 * @author John Haugeland
	 * @return string The Hash Block
	 */
	protected function BuildHashBlock()
	{
		$Ch1to3 = mt_rand(0, 36 * 36 * 36) - 1; // Largest alphanum power that'll fit in the minimum guaranteed 16-bit range for mt_randmax()
		$Ch4to5 = mt_rand(0, 36 * 36) - 1;
		$Ch6to8 = hexdec(substr(uniqid(), -6)) % (36 * 36 * 36); // Only want the bottom two characters of entropy, but clip a large range to keep from much influencing probability

		return str_pad(base_convert($Ch1to3, 10, 36), 3, '0', STR_PAD_LEFT) . str_pad(base_convert($Ch4to5, 10, 36), 2, '0', STR_PAD_LEFT) . str_pad(base_convert($Ch6to8, 10, 36), 3, '0', STR_PAD_LEFT);
	}

	/**
	 * Parse raw HTTP headers of PUT request
	 *
	 * @param array $data
	 *
	 * @return string The Hash Block
	 */
	function parse_raw_http_request(array &$data)
	{
		// read incoming data
		$input = file_get_contents('php://input');

		// grab multipart boundary from content type header
		preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
		$boundary = $matches[1];

		// split content by boundary and get rid of last -- element
		$blocks = preg_split("/-+$boundary/", $input);
		array_pop($blocks);

		// loop data blocks
		foreach ($blocks as $id => $block) {
			if (empty($block)) {
				continue;
			}

			// you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char

			// parse uploaded files
			if (strpos($block, 'application/octet-stream') !== false) {
				// match "name", then everything after "stream" (optional) except for prepending newlines
				preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
			} // parse all other fields
			else {
				// match "name" and optional value in between newline sequences
				preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
			}
			$data[$matches[1]] = $matches[2];
		}
	}
}
