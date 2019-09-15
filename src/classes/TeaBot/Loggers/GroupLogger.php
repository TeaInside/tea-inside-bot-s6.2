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
	 * @return void
	 */
	public function run(): void
	{
		$this->saveGroupInfo();
		$this->saveUserInfo();
	}

	/**
	 * @param ?int $photoId
	 * @return ?int
	 */
	private function groupPhotoResolve(?int $photoId): ?int
	{
		$o = json_decode(Exe::getChat(["chat_id" => $this->data["chat_id"]])["out"], true);
		$currentFileId = $o["result"]["photo"]["big_file_id"] ?? null;

		if (!is_null($photoId)) {
			$st = $this->pdo->prepare("SELECT `telegram_file_id`, `absolute_hash` FROM `files` WHERE `id` = :id LIMIT 1;");
			$st->execute([":id" => $photoId]);
			if (($r = $st->fetch(PDO::FETCH_ASSOC)) && ($r["telegram_file_id"] === $currentFileId)) {
				return $photoId;
			}
		}

		return static::fileResolve($currentFileId);
	}

	/**
	 * @return void
	 */
	public function saveGroupInfo(): void
	{
		$createHistory = false;
		$st = $this->pdo->prepare("SELECT `name`, `username`, `link`, `photo`, `msg_count` FROM `groups` WHERE `group_id` = :group_id LIMIT 1;");
		$st->execute([":group_id" => $this->data["chat_id"]]);
		$data = [
			":group_id" => $this->data["chat_id"],
			":name" => $this->data["group_name"],
			":username" => $this->data["group_username"],
			":link" => null,
			":photo" => null,
			":created_at" => date("Y-m-d H:i:s")
		];

		if ($r = $st->fetch(PDO::FETCH_ASSOC)) {

			if (($r["msg_count"] % 4) === 0) {
				$resolvedPhoto = $this->groupPhotoResolve($r['photo']);
			}

			$r["msg_count"]++;

			if (
				($this->data["group_name"] !== $r["name"]) ||
				($this->data["group_username"] !== $r["username"]) ||
				(isset($resolvedPhoto) && ($resolvedPhoto !== $r["photo"]))
			) {
				$createHistory = true;
				$this->pdo->prepare("UPDATE `groups` SET `username` = :username, `name` = :name, `photo` = :photo, `msg_count` = :msg_count, `updated_at` = :updated_at WHERE `group_id` = :group_id LIMIT 1;")->execute(
					[
						":name" => $this->data["group_name"],
						":username" => $this->data["group_username"],
						":photo" => $resolvedPhoto,
						":msg_count" => $r["msg_count"],
						":updated_at" => $data[":created_at"],
						":group_id" => $this->data["chat_id"],
					]
				);
			} else {
				$this->pdo->prepare("UPDATE `groups` SET `msg_count` = :msg_count, `updated_at` = :updated_at WHERE `group_id` = :group_id LIMIT 1;")->execute(
					[
						":msg_count" => $r["msg_count"],
						":updated_at" => $data[":created_at"],
						":group_id" => $this->data["chat_id"]
					]
				);
			}
		} else {
			$data["photo"] = $this->groupPhotoResolve(null);
			$this->pdo->prepare("INSERT INTO `groups` (`group_id`, `name`, `username`, `link`, `photo`, `msg_count`, `created_at`) VALUES (:group_id, :name, :username, :link, :photo, 1, :created_at);")->execute($data);
			$createHistory = true;
		}

		if ($createHistory) {
			$this->pdo->prepare("INSERT INTO `groups_history` (`group_id`, `name`, `username`, `link`, `photo`, `created_at`) VALUES (:group_id, :name, :username, :link, :photo, :created_at);")->execute($data);
		}
	}

	/**
	 * @return void
	 */
	public function saveUserInfo(): void
	{
		$createHistory = false;
		$st = $this->pdo->prepare("SELECT `username`, `first_name`, `last_name`, `photo` FROM `users` WHERE `user_id` = :user_id LIMIT 1;");
		$st->execute([":user_id" => $this->data["user_id"]]);
		$data = [
			":user_id" => $this->data["user_id"],
			":username" => $this->data["username"],
			":first_name" => $this->data["first_name"],
			":last_name" => $this->data["last_name"],
			":photo" => null,
			":created_at" => date("Y-m-d H:i:s")
		];

		if ($r = $st->fetch(PDO::FETCH_ASSOC)) {
			if (
				($this->data["username"] !== $r["username"]) ||
				($this->data["first_name"] !== $r["first_name"]) ||
				($this->data["last_name"] !== $r["last_name"])
			) {
				$createHistory = true;
				$this->pdo->prepare("UPDATE `users` SET `username` = :username, `first_name` = :first_name, `last_name` = :last_name, `group_msg_count` = `group_msg_count` + 1, `updated_at` = :updated_at WHERE `user_id` = :user_id LIMIT 1;")->execute(
					[
						":username" => $this->data["username"],
						":first_name" => $this->data["first_name"],
						":last_name" => $this->data["last_name"],
						":updated_at" => $data[":created_at"],
						":user_id" => $data[":user_id"]
					]
				);
			} else {
				$this->pdo->prepare("UPDATE `users` SET `group_msg_count` = `group_msg_count` + 1, `updated_at` = :updated_at WHERE `user_id` = :user_id LIMIT 1;")->execute(
					[
						":updated_at" => $data[":created_at"],
						":user_id" => $data[":user_id"]
					]
				);
			}
		} else {
			$data[":is_bot"] = ($this->data["is_bot"] ? '1' : '0');
			$this->pdo->prepare("INSERT INTO `users` (`user_id`, `username`, `first_name`, `last_name`, `photo`, `is_bot`, `group_msg_count`, `private_msg_count`, `created_at`, `updated_at`) VALUES (:user_id, :username, :first_name, :last_name, :photo, :is_bot, 1, 0, :created_at, NULL);")->execute($data);
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
		$this->pdo
			->prepare("INSERT INTO `groups_messages` (`group_id`, `user_id`, `tmsg_id`, `reply_to_tmsg_id`, `msg_type`, `text`, `text_entities`, `file`, `is_edited`, `tmsg_datetime`, `created_at`) VALUES (:group_id, :user_id, :tmsg_id, :reply_to_tmsg_id, :msg_type, :text, :text_entities, NULL, :is_edited, :tmsg_datetime, :created_at);")
			->execute(
				[
					":group_id" => $this->data["chat_id"],
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

		// First, check whether file_id exists in database or not.
		$st = $this->pdo->prepare("SELECT `id` FROM `files` WHERE `telegram_file_id` = :file_id LIMIT 1;");
		$st->execute([":file_id" => $photo["file_id"]]);
		if ($r = $st->fetch(PDO::FETCH_NUM)) {
			// Increase hit counter.
			$this->pdo->prepare("UPDATE `files` SET `hit_count` = `hit_count` + 1, `updated_at` = :updated_at WHERE `id` = :id LIMIT 1;")->execute(
					[
						":id" => $r[0],
						":updated_at" => date("Y-m-d H:i:s")
					]
				);

			$fileId = $r[0];
			goto save_message;
		}

		$fileId = static::fileResolve($photo["file_id"]);

		save_message:
		$this->pdo
			->prepare("INSERT INTO `groups_messages` (`group_id`, `user_id`, `tmsg_id`, `reply_to_tmsg_id`, `msg_type`, `text`, `text_entities`, `file`, `is_edited`, `tmsg_datetime`, `created_at`) VALUES (:group_id, :user_id, :tmsg_id, :reply_to_tmsg_id, :msg_type, :text, :text_entities, :file, :is_edited, :tmsg_datetime, :created_at);")
			->execute(
				[
					":group_id" => $this->data["chat_id"],
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
