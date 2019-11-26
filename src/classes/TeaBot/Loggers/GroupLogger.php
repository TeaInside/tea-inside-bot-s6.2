<?php

namespace TeaBot\Loggers;

use DB;
use PDO;
use TeaBot\Exe;
use TeaBot\LoggerFoundation;
use TeaBot\Contracts\LoggerInterface;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot\Loggers
 * @version 6.2.0
 */
final class GroupLogger extends LoggerFoundation implements LoggerInterface
{
	/**
	 * @var string
	 */
	public $groupHash;

	/**
	 * @return void
	 */
	public function run(): void
	{
		$this->groupHash = sha1($this->data["group_id"]);
		while (static::groupIsLocked($this->groupHash)) {
			sleep(2);
		}
		static::groupLock($this->groupHash);

		$this->userLogger();
	}

	/**
	 * @return void
	 */
	public function userLogger(): void
	{
		$createUserHistory = false;
		$data = [
			":user_id" => $this->data["user_id"],
			":username" => $this->data["username"],
			":first_name" => $this->data["first_name"],
			":last_name" => $this->data["last_name"],
			":photo" => null,
			":created_at" => date("Y-m-d H:i:s")
		];

		/**
		 * Retrieve user data from database.
		 */
		$st = $this->pdo->prepare("SELECT `id`, `username`, `first_name`, `last_name`, `photo`, `is_bot`, `group_msg_count` FROM `users` WHERE `user_id` = :user_id LIMIT 1;");
		$st->execute([":user_id" => $this->data["user_id"]]);

		if ($r = $st->fetch(PDO::FETCH_ASSOC)) {

			/**
			 * User has been recorded in database.
			 */
			$r["id"] = (int)$r["id"];
			$r["group_msg_count"] = ((int)$r["group_msg_count"] + 1);

			// Check whether there is a change on user info.
			if (($this->data["username"] !== $r["username"]) ||
				($this->data["first_name"] !== $r["first_name"]) ||
				($this->data["last_name"] !== $r["last_name"])) {

				// Create user history if there is a change on user info.
				$createUserHistory = true;

				$this->pdo->prepare("UPDATE `users` SET `username` = :username, `first_name` = :first_name, `last_name` = :last_name, `group_msg_count` = :group_msg_count WHERE `id` = :id LIMIT 1;")
				->execute(
					[
						":id" => $r["id"],
						":username" => $this->data["username"],
						":first_name" => $this->data["first_name"],
						":last_name" => $this->data["last_name"],
						":group_msg_count" => $r["group_msg_count"]
					]
				);
			} else {
				$this->pdo->prepare("UPDATE `users` SET `group_msg_count` = :group_msg_count WHERE `id` = :id LIMIT 1;")->execute([":id" => $r["id"], ":group_msg_count" => $r["group_msg_count"]]);
			}

		} else {

			/**
			 * User has not been stored in database.
			 */
			$data[":is_bot"] = ($this->data["is_bot"] ? '1' : '0');
			$this->pdo->prepare("INSERT INTO `users` (`user_id`, `username`, `first_name`, `last_name`, `photo`, `is_bot`, `group_msg_count`, `private_msg_count`, `created_at`) VALUES (:user_id, :username, :first_name, :last_name, :photo, :is_bot, 1, 0, :created_at);")->execute($data);
			unset($data[":is_bot"]);
			$createHistory = true;
		}


		if ($createHistory) {
			$this->pdo->prepare("INSERT INTO `users_history` (`user_id`, `username`, `first_name`, `last_name`, `photo`, `created_at`) VALUES (:user_id, :username, :first_name, :last_name, :photo, :created_at);")->execute($data);
		}
	}

	/**
	 * @return void
	 */
	public function logText(): void
	{
	}

	/**
	 * @return void
	 */
	public function logPhoto(): void
	{
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		static::groupUnlock($this->groupHash);
	}
}
