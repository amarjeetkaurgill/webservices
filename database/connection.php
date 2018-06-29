<?php
/**
 * @author    Amarjeet Kaur
 * @package   Webservices
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link      http://github.com/amarjeetkaurgill/webservices
 */


require_once('MysqliDb.php');

/**
 * @author Amarjeet Kaur
 *
 * This file will be used for mysql connection creation.
 * We will be creating a single connection at a time and
 * then will use the same connection as per requirement
 */
class Connection
{
	const MYSQLI_HOST     = 'localhost';
	const MYSQLI_DATABASE = 'web';
	const MYSQLI_USERNAME = 'root';
	const MYSQLI_PASSWORD = '';

	/**
	 * @author Amarjeet Kaur
	 */
	static public function Get()
	{
		$db = MysqliDb::getInstance();
		if (!$db instanceof MysqliDb) {
			$db = new MysqliDb (self::MYSQLI_HOST, self::MYSQLI_USERNAME, self::MYSQLI_PASSWORD, self::MYSQLI_DATABASE);
		}

		return $db;
	}
}
