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
final class PrivateLogger extends LoggerFoundation implements LoggerInterface
{
	/**
	 * @return void
	 */
	public function run(): void
	{
		/**
		 * @see TeaBot\LoggerFoundation
		 *
		 * 1 means private logger
		 */
		$this->userLogger($this->data, 2);
	}

	/**
	 * @return void
	 */
	public function logText(): void
	{
		$this->pdo
			->prepare("INSERT INTO `private_messages` (`user_id`, `tmsg_id`, `reply_to_tmsg_id`, `msg_type`, `text`, `text_entities`, `file`, `is_edited`, `tmsg_datetime`, `created_at`) VALUES (:user_id, :tmsg_id, :reply_to_tmsg_id, :msg_type, :text, :text_entities, NULL, :is_edited, :tmsg_datetime, :created_at);")
			->execute(
				[
					":user_id" => $this->data["user_id"],
					":tmsg_id" => $this->data["msg_id"],
					":reply_to_tmsg_id" => (
						isset($this->data["reply"]) ? $this->data["reply"]["message_id"] : null
					),
					":msg_type" => "text",
					":text" => $this->data["text"],
					":text_entities" => (
						isset($this->data["entities"]) ? json_encode($this->data["entities"], JSON_UNESCAPED_SLASHES) : null
					),
					":is_edited" => '0',
					":tmsg_datetime" => date("Y-m-d H:i:s", $this->data["date"]),
					":created_at" => date("Y-m-d H:i:s")
				]
			);
	}

	/**
	 * @return void
	 */
	public function logPhoto(): void
	{
		$photo = end($this->data["photo"]);
		$fileId = static::fileResolve($photo["file_id"], true);
		$this->pdo
			->prepare("INSERT INTO `private_messages` (`user_id`, `tmsg_id`, `reply_to_tmsg_id`, `msg_type`, `text`, `text_entities`, `file`, `is_edited`, `tmsg_datetime`, `created_at`) VALUES (:user_id, :tmsg_id, :reply_to_tmsg_id, :msg_type, :text, :text_entities, :file, :is_edited, :tmsg_datetime, :created_at);")
			->execute(
				[
					":user_id" => $this->data["user_id"],
					":tmsg_id" => $this->data["msg_id"],
					":reply_to_tmsg_id" => (
						isset($this->data["reply"]) ? $this->data["reply"]["message_id"] : null
					),
					":msg_type" => "photo",
					":text" => $this->data["text"],
					":text_entities" => (
						isset($this->data["entities"]) ? json_encode($this->data["entities"], JSON_UNESCAPED_SLASHES) : null
					),
					":file" => $fileId,
					":is_edited" => '0',
					":tmsg_datetime" => date("Y-m-d H:i:s", $this->data["date"]),
					":created_at" => date("Y-m-d H:i:s")
				]
			);
	}
}
