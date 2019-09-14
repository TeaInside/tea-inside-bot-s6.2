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
		$this->saveUserInfo();
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
				$this->pdo->prepare("UPDATE `users` SET `username` = :username, `first_name` = :first_name, `last_name` = :last_name, `private_msg_count` = `private_msg_count` + 1, `updated_at` = :updated_at WHERE `user_id` = :user_id LIMIT 1;")->execute(
					[
						":username" => $this->data["username"],
						":first_name" => $this->data["first_name"],
						":last_name" => $this->data["last_name"],
						":updated_at" => $data[":created_at"],
						":user_id" => $data[":user_id"]
					]
				);
			} else {
				$this->pdo->prepare("UPDATE `users` SET `private_msg_count` = `private_msg_count` + 1, `updated_at` = :updated_at WHERE `user_id` = :user_id LIMIT 1;")->execute(
					[
						":updated_at" => $data[":created_at"],
						":user_id" => $data[":user_id"]
					]
				);
			}
		} else {
			$data[":is_bot"] = ($this->data["is_bot"] ? '1' : '0');
			$this->pdo->prepare("INSERT INTO `users` (`user_id`, `username`, `first_name`, `last_name`, `photo`, `is_bot`, `group_msg_count`, `private_msg_count`, `created_at`, `updated_at`) VALUES (:user_id, :username, :first_name, :last_name, :photo, :is_bot, 0, 1, :created_at, NULL);")->execute($data);
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

		$o = json_decode(Exe::getFile(["file_id" => $photo["file_id"]])["out"], true);
		if (isset($o["result"]["file_path"])) {

			$o = $o["result"];

			is_dir(STORAGE_PATH) or mkdir(STORAGE_PATH);
			is_dir(STORAGE_PATH."/telegram") or mkdir(STORAGE_PATH."/telegram");
			is_dir(STORAGE_PATH."/telegram/files") or mkdir(STORAGE_PATH."/telegram/files");
			is_dir("/tmp/telegram_download") or mkdir("/tmp/telegram_download");

			$ext = explode(".", $o["file_path"]);
			if (count($ext) > 1) {
				$ext = strtolower(end($ext));
			} else {
				$ext = null;
			}

			$tmpFile = "/tmp/telegram_download/".time()."_".sha1($photo["file_id"])."_".rand(100000, 999999).
				(isset($ext) ? ".".$ext : "");
			$handle = fopen($tmpFile, "wb+");
			$bufferSize = 4096;
			$writtenBytes = 0;

			$ch = curl_init("https://api.telegram.org/file/bot".BOT_TOKEN."/".$o["file_path"]);
			curl_setopt_array($ch,
				[
					CURLOPT_VERBOSE => 0,
					CURLOPT_RETURNTRANSFER => false,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_WRITEFUNCTION => function ($ch, $str) use (&$handle, &$writtenBytes, $bufferSize) {
						$bytes = fwrite($handle, $str);
						$writtenBytes += $bytes;
						if ($writtenBytes >= $bufferSize) {
							fflush($handle);
						}
						return $bytes;
					}
				]
			);
			curl_exec($ch);
			curl_close($ch);
			fclose($handle);

			$sha1_hash = sha1_file($tmpFile, true);
			$md5_hash = md5_file($tmpFile, true);
			$absolute_hash = $sha1_hash.$md5_hash;

			$st = $this->pdo->prepare("SELECT `id` FROM `files` WHERE `absolute_hash` = :absolute_hash LIMIT 1;");
			$st->execute([":absolute_hash" => $absolute_hash]);
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

			$targetFile = STORAGE_PATH."/telegram/files/".
				bin2hex($md5_hash)."_".bin2hex($sha1_hash).(isset($ext) ? ".".$ext : "");

			rename($tmpFile, $targetFile);

			$this->pdo
				->prepare("INSERT INTO `files` (`telegram_file_id`, `md5_sum`, `sha1_sum`, `absolute_hash`, `file_type`, `extension`, `size`, `hit_count`, `created_at`) VALUES (:telegram_file_id, :md5_sum, :sha1_sum, :absolute_hash, :file_type, :extension, :size, :hit_count, :created_at);")
				->execute(
					[
						":telegram_file_id" => $photo["file_id"],
						":md5_sum" => $md5_hash,
						":sha1_sum" => $sha1_hash,
						":absolute_hash" => $absolute_hash,
						":file_type" => "photo",
						":extension" => $ext,
						":size" => (isset($photo["file_size"]) ? $photo["file_size"] : null),
						":hit_count" => 1,
						":created_at" => date("Y-m-d H:i:s")
					]
				);
			$fileId = $this->pdo->lastInsertId();
		}


		save_message:
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
