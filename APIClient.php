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
class APIClient
{
	/** @var string */
	protected $url;

	/** @var string */
	protected $method;

	const METHOD_GET    = 'GET';
	const METHOD_POST   = 'POST';
	const METHOD_PUT    = 'PUT';
	const METHOD_DELETE = 'DELETE';

	/**
	 * @author Amarjeet Kaur
	 *
	 * @param string $url
	 * @param string $method
	 */
	public function __construct($url, $method)
	{
		$this->SetURL($url)
			 ->SetMethod($method);
	}

	/**
	 * @author Amarjeet Kaur
	 *
	 * @param int $method
	 *
	 * @return bool
	 */
	static public function IsValidMethod($method)
	{
		return in_array($method, [self::METHOD_GET, self::METHOD_POST, self::METHOD_PUT, self::METHOD_DELETE]);
	}

	/**
	 * @author Amarjeet Kaur
	 *
	 * @param string $url
	 *
	 * @return APIClient
	 */
	protected function SetURL($url)
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * @author Amarjeet Kaur
	 *
	 * @param string $method
	 *
	 * @return APIClient
	 * @throws Exception
	 */
	protected function SetMethod($method)
	{
		if (!self::IsValidMethod($method)) {
			throw new Exception('Invalid method provided');
		}

		$this->method = $method;

		return $this;
	}

	/**
	 * @author Amarjeet Kaur
	 * @return string
	 */
	public function GetURL()
	{
		return $this->url;
	}

	/**
	 * @author Amarjeet Kaur
	 * @return string
	 */
	public function GetMethod()
	{
		return $this->method;
	}

	/**
	 * @author Amarjeet Kaur
	 *
	 * @param array $data (OPTIONAL)
	 *
	 * @return string
	 * @throws Exception
	 */
	public function Send($data = [])
	{
		$method = $this->GetMethod();
		if (in_array($method, [self::METHOD_POST, self::METHOD_PUT]) && empty($data)) {
			throw new Exception('Invalid data provided');
		}

		$curlResource = curl_init();
		curl_setopt($curlResource, CURLOPT_URL, $this->GetURL());
		curl_setopt($curlResource, CURLOPT_TIMEOUT, 120);
		curl_setopt($curlResource, CURLOPT_CONNECTTIMEOUT, 120);
		curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlResource, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($curlResource, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curlResource, CURLOPT_SSL_VERIFYPEER, 0);

		switch ($method) {
			case self::METHOD_GET:
			case self::METHOD_DELETE:
				break;

			case self::METHOD_PUT:
			case self::METHOD_POST:
				if (!empty($data)) {
					curl_setopt($curlResource, CURLOPT_POST, 1);
					curl_setopt($curlResource, CURLOPT_POSTFIELDS, $data);
				}
				break;

			default:
				break;
		}

		$jsonResponse = curl_exec($curlResource);

		curl_close($curlResource);

		$response = json_decode($jsonResponse, true);

		return $response;
	}
}

/*
$Client   = new APIClient('http://abcd.com/api/user.php', 'POST');
$response = $Client->Send(['first_name' => 'Amarjeet', 'last_name' => 'Kaur']);

$Client   = new APIClient('http://abcd.com/api/login.php', 'POST');
$response = $Client->Send(['username' => 'amarjeet', 'password' => md5('Kaur')]);

$Client   = new APIClient('http://abcd.com/api/user.php?id=1', 'GET');
$response = $Client->Send();
*/
