<?php

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package {No Package}
 * @version 6.2.0
 */
final class DB
{
	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * @var self
	 */
	private static $self;

	/**
	 * Constructor.
	 */
	private function __construct()
	{
		$this->pdo = new \PDO(...PDO_PARAM);
		$this->pdo->exec("SET NAMES utf8mb4;");
	}

	/**
	 * @return void
	 */
	public static function close()
	{
		self::getInstance()->pdo = null;
		self::$self = null;
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		$this->pdo = null;
		unset($this->pdo);
	}

	/**
	 * @return \PDO
	 */
	public static function pdo(): \PDO
	{
		return self::getInstance()->pdo;
	}

	/**
	 * @return self
	 */
	public static function getInstance(): DB
	{
		if (!self::$self) {
			self::$self = new self;
		}
		return self::$self;
	}
}
