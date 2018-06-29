<?php
/**
 * @author    Amarjeet Kaur
 * @package   Webservices
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link      http://github.com/amarjeetkaurgill/webservices
 */

/**
 * @author Amarjeet Kaur
 */
class Response
{
	/* @var integer */
	protected $code;

	/* @var string */
	protected $message;

	static protected $contentType = "application/json";

	/**
	 * HTTP Codes
	 */
	const HTTP_OK                    = 200;
	const HTTP_CREATED               = 201;
	const HTTP_MOVED_PERMANENTLY     = 301;
	const HTTP_NOT_MODIFIED          = 304;
	const HTTP_TEMPORARY_REDIRECT    = 307;
	const HTTP_BAD_REQUEST           = 400;
	const HTTP_UNAUTHORIZED          = 401;
	const HTTP_FORBIDDEN             = 403;
	const HTTP_NOT_FOUND             = 404;
	const HTTP_METHOD_NOT_ALLOWED    = 405;
	const HTTP_NOT_ACCEPTABLE        = 406;
	const HTTP_TOO_MANY_REQUESTS     = 429;
	const HTTP_SESSION_NOT_FOUND     = 454;
	const HTTP_INTERNAL_SERVER_ERROR = 500;

	/**
	 * HTTP Codes and Associated Strings
	 */
	static protected $httpCodeStrings = [
		self::HTTP_OK                    => 'OK',
		self::HTTP_CREATED               => 'Created',
		self::HTTP_MOVED_PERMANENTLY     => 'Moved Permanently',
		self::HTTP_NOT_MODIFIED          => 'Not Modified',
		self::HTTP_TEMPORARY_REDIRECT    => 'Temporary Redirect',
		self::HTTP_BAD_REQUEST           => 'Bad Request',
		self::HTTP_UNAUTHORIZED          => 'Unauthorized',
		self::HTTP_FORBIDDEN             => 'Forbidden',
		self::HTTP_NOT_FOUND             => 'Not Found',
		self::HTTP_METHOD_NOT_ALLOWED    => 'Method Not Allowed',
		self::HTTP_NOT_ACCEPTABLE        => 'Not Acceptable',
		self::HTTP_TOO_MANY_REQUESTS     => 'Too Many Requests',
		self::HTTP_SESSION_NOT_FOUND     => 'Session Not Found',
		self::HTTP_INTERNAL_SERVER_ERROR => 'Internal Server Error',
	];

	/**
	 * @param int    $code
	 * @param string $message
	 */
	public function __construct($code, $message)
	{
		$this->SetCode($code)
			 ->SetMessage($message)
			 ->SetHeaders();
	}

	/**
	 * Set headers to return JSON response
	 */
	private function SetHeaders()
	{
		header("HTTP/1.1 " . $this->GetCode() . " " . $this->GetMessage());
		header("Content-Type:" . self::$contentType);
	}

	/**
	 * @author Amarjeet Kaur
	 *
	 * @param int $code
	 *
	 * @return bool
	 */
	static public function IsValidCode($code)
	{
		return array_key_exists($code, self::$httpCodeStrings);
	}

	/**
	 * @param int $code
	 *
	 * @return Response
	 * @throws Exception
	 */
	protected function SetCode($code)
	{
		if (empty($code) || !self::IsValidCode($code)) {
			throw new Exception('Invalid HTTP code provided!!');
		}

		$this->code = $code;

		return $this;
	}

	/**
	 * @return int
	 */
	public function GetCode()
	{
		return $this->code;
	}

	/**
	 * @param string $message
	 *
	 * @return Response
	 * @throws Exception
	 */
	protected function SetMessage($message)
	{
		if (empty($message)) {
			throw new Exception('Invalid message provided!!');
		}

		$this->message = $message;

		return $this;
	}

	/**
	 * @return int
	 */
	public function GetMessage()
	{
		return $this->message;
	}

	/**
	 * @param int $code
	 *
	 * @return string
	 */
	static public function GetStatusMessage($code)
	{
		return self::$httpCodeStrings[$code];
	}

	/**
	 * @param array $response
	 */
	public function EchoJSON($response = [])
	{
		if (empty($response)) {
			$response = ['message' => $this->GetMessage()];
		}

		echo json_encode($response);

		exit;
	}
}
