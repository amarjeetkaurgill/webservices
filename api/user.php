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
 * This file will handle user table related CURD operations
 */
class User extends REST
{
	/**
	 * @var array $fields
	 *
	 * List of required fields
	 */
	protected $fields = ['fullname', 'email', 'password'];

	/**
	 * @param int $id
	 *
	 * @throws Exception
	 */
	public function Get($id)
	{
		if (empty($id)) {
			throw new Exception('Invalid data provided!!');
		}

		$user = $this->db->where('user_id', $id)
						 ->getOne('users');

		$response = ['user_id'   => $user['user_id'],
					 'full_name' => $user['user_fullname'],
					 'email'     => $user['user_email'],
					 'status'    => $user['user_status']];

		$statusCode = Response::HTTP_OK;
		(new Response($statusCode, Response::GetStatusMessage($statusCode)))->EchoJSON($response);
	}

	/**
	 * Handles Post request
	 *
	 * @throws Exception
	 */
	public function Post()
	{
		$resourceParameters = $this->GetResourceParameters();
		if (empty($resourceParameters)) {
			throw new Exception('Invalid data provided!!');
		}

		$errors = $this->ValidateInput($this->fields);
		if (!empty($errors)) {
			$statusCode = Response::HTTP_BAD_REQUEST;
			(new Response($statusCode, Response::GetStatusMessage($statusCode)))->EchoJSON($errors);
		}

		if (!filter_var($resourceParameters['email'], FILTER_VALIDATE_EMAIL)) {
			$statusCode = Response::HTTP_BAD_REQUEST;
			(new Response($statusCode, Response::GetStatusMessage($statusCode)))->EchoJSON(['message' => 'Email address is not valid']);
		}

		$users = $this->db->where('user_email', $resourceParameters['email'])
						  ->getOne('users');

		if (count($users) > 0) {
			$statusCode = Response::HTTP_FORBIDDEN;
			(new Response($statusCode, Response::GetStatusMessage($statusCode)))->EchoJSON(['message' => 'Email address already exists!!']);
		}

		$this->db->insert('users', ['user_fullname' => $resourceParameters['fullname'],
									'user_email'    => $resourceParameters['email'],
									'user_password' => md5($resourceParameters['password']),
									'user_status'   => 1]);

		$user = $this->db->where('user_id', $this->db->getInsertId())
						 ->getOne('users');

		$response = ['user_id'   => $user['user_id'],
					 'full_name' => $user['user_fullname'],
					 'email'     => $user['user_email'],
					 'status'    => $user['user_status']];

		$statusCode = Response::HTTP_CREATED;
		(new Response($statusCode, Response::GetStatusMessage($statusCode)))->EchoJSON($response);
	}

	/**
	 * @param int $id
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function Put($id)
	{
		if (empty($id)) {
			throw new Exception('Invalid data provided!!');
		}

		$resourceParameters = $this->GetResourceParameters();
		if (empty($resourceParameters)) {
			throw new Exception('Invalid data provided!!');
		}

		$errors = $this->ValidateInput();
		if (!empty($errors)) {
			$statusCode = Response::HTTP_BAD_REQUEST;
			(new Response($statusCode, Response::GetStatusMessage($statusCode)))->EchoJSON($errors);
		}

		$user = $this->db->where('user_id', $id)
						 ->getOne('users');

		if (count($user) == 0) {
			$statusCode = Response::HTTP_NOT_FOUND;
			(new Response($statusCode, Response::GetStatusMessage($statusCode)))->EchoJSON();
		}

		$updateRecord = [];
		if (!empty($resourceParameters['fullname'])) {
			$updateRecord['user_fullname'] = $resourceParameters['fullname'];
		}

		if (!empty($resourceParameters['password'])) {
			$updateRecord['user_password'] = md5($resourceParameters['fullname']);
		}

		if (!empty($resourceParameters['status'])) {
			$updateRecord['user_status'] = $resourceParameters['status'];
		}

		$this->db->where('user_id', $id)
				 ->update('users', $updateRecord);

		$user = $this->db->where('user_id', $id)
						 ->getOne('users');

		$response = ['user_id'   => $user['user_id'],
					 'full_name' => $user['user_fullname'],
					 'email'     => $user['user_email'],
					 'status'    => $user['user_status']];

		$statusCode = Response::HTTP_OK;
		(new Response($statusCode, Response::GetStatusMessage($statusCode)))->EchoJSON($response);
	}

	/**
	 * @param int $id
	 *
	 * @throws Exception
	 */
	public function Delete($id)
	{
		if (empty($id)) {
			throw new Exception('Invalid data provided!!');
		}
		$this->db->where('user_id', $id)
				 ->delete('users');

		$this->db->where('user_id', $id)
				 ->delete('sessions');

		$statusCode = Response::HTTP_OK;
		(new Response($statusCode, Response::GetStatusMessage($statusCode)))->EchoJSON();
	}
}
