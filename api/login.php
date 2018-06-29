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
class Login extends REST
{
	/**
	 * @var array $fields
	 *
	 * List of required fields
	 */
	protected $fields = ['email', 'password'];

	/**
	 * Handles Post request
	 *
	 * @throws Exception
	 */
	public function Post()
	{
		print_r($_COOKIE);
	}
}
