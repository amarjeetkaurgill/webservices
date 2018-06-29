<?php
/**
 * @author    Amarjeet Kaur
 * @package   Webservices
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link      http://github.com/amarjeetkaurgill/webservices
 */


require_once('api/REST.php');

/**
 * @author Amarjeet Kaur
 *
 * This will be the generic file for handling API requests.
 * All basic validations and filtration will be handled though it
 */
class API extends REST
{
	/**
	 * This function will process all incoming requests
	 * and corresponding function of respective file will be called
	 *
	 * @return Response
	 * @throws Exception
	 */
	public function ProcessRequest()
	{
		$entity = strtolower(trim(str_replace("/", "", $_REQUEST['request'])));
		$user   = $this->IsAuthenticated();

		if ($entity == 'login' && !empty($user)) {
			$code = Response::HTTP_OK;
			(new Response($code, Response::GetStatusMessage($code)))->EchoJSON(['session_id' => $user]);
		}

		if (empty($user) && $entity != 'register') {
			$code = Response::HTTP_SESSION_NOT_FOUND;
			(new Response($code, Response::GetStatusMessage($code)))->EchoJSON();
		}

		if ($entity == 'register') {
			$entity = 'user';
		}
		$file = 'api/' . $entity . '.php';
		if (file_exists($file) > 0) {

			include_once($file);
			$Object = new $entity();

			switch ($this->GetRequestType()) {
				case self::REQUEST_GET:
					$Object->Get($this->requestParameters['id']);

					break;

				case self::REQUEST_POST:
					$Object->Post();

					break;

				case self::REQUEST_PUT:
					$Object->Put($this->requestParameters['id']);

					break;

				case self::REQUEST_DELETE:
					$Object->Delete($this->requestParameters['id']);

					break;

				default:
					throw new Exception('Invalid request specified!!');
					break;
			}
		} else {
			// If the method not exist with in this class, response would be "Page not found".
			$code = Response::HTTP_NOT_FOUND;
			(new Response($code, Response::GetStatusMessage($code)))->EchoJSON();
		}
	}
}

$API = new API();
$API->ProcessRequest();
exit;
