<?php

namespace TeaBot;

use DB;
use PDO;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
abstract class LoggerFoundation
{
	/**
	 * @var \TeaBot\Data
	 */
	protected $data;

	/**
	 * @var \PDO
	 */

	/**
	 * @param \TeaBot\Data &$data
	 *
	 * Constructor.
	 */
	public function __construct(Data &$data)
	{
		$this->data = &$data;
		$this->pdo = DB::pdo();
	}

	/**
	 * @return void
	 */
	abstract public function run(): void;

	/**
	 * @param string $groupId
	 * @return void
	 */
	public static function groupLock(string $groupId): void
	{
		is_dir("/tmp/telegram_lock") or mkdir("/tmp/telegram_lock");
		file_put_contents("/tmp/telegram_lock/{$groupId}", time());
	}

	/**
	 * @param string $groupId
	 * @return void
	 */
	public static function groupUnlock(string $groupId): void
	{
		@unlink("/tmp/telegram_lock/{$groupId}");
	}

	/**
	 * @param string $groupId
	 * @return bool
	 */
	public static function groupIsLocked(string $groupId): bool
	{
		return file_exists($groupId);
	}

	/**
	 * @param string $telegramFileId
	 * @return ?int
	 */
	public static function fileResolve(string $telegramFileId): ?int
	{
		$pdo = DB::pdo();
		$st = $pdo->prepare("SELECT `id` FROM `files` WHERE `telegram_file_id` = :telegram_file_id LIMIT 1;");
		$st->execute([":telegram_file_id" => $telegramFileId]);
		if ($r = $st->fetch(PDO::FETCH_NUM)) {
			return (int)$r[0];
		}

		$o = json_decode(Exe::getFile(["file_id" => $telegramFileId])["out"], true);
		if (isset($o["result"]["file_path"])) {

			$o = $o["result"];

			// Create required directories.
			is_dir(STORAGE_PATH) or mkdir(STORAGE_PATH);
			is_dir(STORAGE_PATH."/telegram") or mkdir(STORAGE_PATH."/telegram");
			is_dir(STORAGE_PATH."/telegram/files") or mkdir(STORAGE_PATH."/telegram/files");
			is_dir("/tmp/telegram_download") or mkdir("/tmp/telegram_download");

			// // Create .gitignore at storage path.
			// file_exists(STORAGE_PATH."/telegram/.gitignore") or
			// file_get_contents(STORAGE_PATH."/telegram/.gitignore", "*\n!.gitignore\n");

			// Get file extension.
			$ext = explode(".", $o["file_path"]);
			if (count($ext) > 1) {
				$ext = strtolower(end($ext));
			} else {
				$ext = null;
			}

			// Prepare temporary file handler.
			$tmpFile = "/tmp/telegram_download/".time()."_".sha1($telegramFileId)."_".rand(100000, 999999).
				(isset($ext) ? ".".$ext : "");
			$handle = fopen($tmpFile, "wb+");
			$bufferSize = 4096;
			$writtenBytes = 0;

			// Download the file.
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

			// Calculate file checksum.
			$sha1_hash = sha1_file($tmpFile, true);
			$md5_hash = md5_file($tmpFile, true);

			// Check whether there is the same file in storage by matching its absolute hash.
			$st = $pdo->prepare("SELECT `id` FROM `files` WHERE `md5_sum` = :md5_sum AND `sha1_sum` = :sha1_sum LIMIT 1;");
			$st->execute(
				[
					":md5_sum" => $md5_hash,
					":sha1_sum" => $sha1_hash,
				]
			);
			if ($r = $st->fetch(PDO::FETCH_NUM)) {
				// Increase hit counter.
				$pdo->prepare("UPDATE `files` SET `telegram_file_id` = :telegram_file_id, `hit_count` = `hit_count` + 1, `updated_at` = :updated_at WHERE `id` = :id LIMIT 1;")->execute(
						[
							":telegram_file_id" => $telegramFileId,
							":id" => $r[0],
							":updated_at" => date("Y-m-d H:i:s")
						]
					);

				// Delete temporary file.
				unlink($tmpFile);

				return (int)$r[0];
			}

			// Prepare target filename.
			$targetFile = STORAGE_PATH."/telegram/files/".
				bin2hex($md5_hash)."_".bin2hex($sha1_hash).(isset($ext) ? ".".$ext : "");

			// Move downloaded file to storage directory.
			rename($tmpFile, $targetFile);

			// Insert metadata to database.
			$pdo->prepare("INSERT INTO `files` (`telegram_file_id`, `md5_sum`, `sha1_sum`, `file_type`, `extension`, `size`, `hit_count`, `created_at`) VALUES (:telegram_file_id, :md5_sum, :sha1_sum, :file_type, :extension, :size, :hit_count, :created_at);")
				->execute(
					[
						":telegram_file_id" => $telegramFileId,
						":md5_sum" => $md5_hash,
						":sha1_sum" => $sha1_hash,
						":file_type" => "photo",
						":extension" => $ext,
						":size" => filesize($targetFile),
						":hit_count" => 1,
						":created_at" => date("Y-m-d H:i:s")
					]
				);
			return $pdo->lastInsertId();
		}

		// Couldn't get the file_path (Error from Telegram API)
		return null;
	}

	/**
	 * @param mixed $parData Must be accessible as array.
	 * @param int	$logType
	 * @return void
	 *
	 *
	 * $logType description
	 * 0 = No message log.
	 * 1 = Group log.
	 * 2 = Private log.
	 */
	public function userLogger($parData, $logType = 0): void
	{
		$createUserHistory = false;
		$data = [
			":user_id" => $parData["user_id"],
			":username" => $parData["username"],
			":first_name" => $parData["first_name"],
			":last_name" => $parData["last_name"],
			":photo" => null,
			":created_at" => date("Y-m-d H:i:s")
		];

		/**
		 * Retrieve user data from database.
		 */
		$st = $this->pdo->prepare("SELECT `id`, `username`, `first_name`, `last_name`, `photo`, `is_bot`, `group_msg_count`, `private_msg_count` FROM `users` WHERE `user_id` = :user_id LIMIT 1;");
		$st->execute([":user_id" => $parData["user_id"]]);

		if ($r = $st->fetch(PDO::FETCH_ASSOC)) {

			/**
			 * User has been recorded in database.
			 */
			$exeData = [":id" => (int)$r["id"]];

			$noMsgLog = false;
			if ($logType == 1) {
				$cc = $r["group_msg_count"] = ((int)$r["group_msg_count"] + 1);
				$exeData[":group_msg_count"] = $r["group_msg_count"];
				$additionalQuery = ", `group_msg_count` = :group_msg_count";
			} else if ($logType == 2) {
				$cc = $r["private_msg_count"] = ((int)$r["private_msg_count"] + 1);
				$exeData[":private_msg_count"] = $r["private_msg_count"];
				$additionalQuery = ", `private_msg_count` = :private_msg_count";
			} else {
				$noMsgLog = true;
				$additionalQuery = "";
			}

			if ((!$noMsgLog) && (($cc % 5) == 0)) {
				$exeData[":photo"] = null;
				$additionalQuery .= ", `photo` = :photo";
				$o = Exe::getUserProfilePhotos(
					[
						"user_id" => $parData["user_id"],
						"offset" => 0,
						"limit" => 1
					]
				);
				$json = json_decode($o["out"], true);
				if (isset($json["result"]["photos"][0])) {
					$c = count($json["result"]["photos"][0]);
					if ($c) {
						$p = $json["result"]["photos"][0][$c - 1];
						if (isset($p["file_id"])) {
							$exeData[":photo"] = self::fileResolve($p["file_id"]);
						}
					}
				}
			}

			// Check whether there is a change on user info.
			if (($parData["username"] !== $r["username"]) ||
				($parData["first_name"] !== $r["first_name"]) ||
				($parData["last_name"] !== $r["last_name"])) {

				// Create user history if there is a change on user info.
				$createUserHistory = true;

				$exeData[":username"] = $parData["username"];
				$exeData[":first_name"] = $parData["first_name"];
				$exeData[":last_name"] = $parData["last_name"];

				$this->pdo->prepare("UPDATE `users` SET `username` = :username, `first_name` = :first_name, `last_name` = :last_name {$additionalQuery} WHERE `id` = :id LIMIT 1;")
				->execute($exeData);

			} else {
				$additionalQuery[0] = " ";
				$this->pdo->prepare("UPDATE `users` SET {$additionalQuery} WHERE `id` = :id LIMIT 1;")->execute($exeData);
			}

		} else {

			$o = Exe::getUserProfilePhotos(
				[
					"user_id" => $parData["user_id"],
					"offset" => 0,
					"limit" => 1
				]
			);
			$json = json_decode($o["out"], true);
			if (isset($json["result"]["photos"][0])) {
				$c = count($json["result"]["photos"][0]);
				if ($c) {
					$p = $json["result"]["photos"][0][$c - 1];
					if (isset($p["file_id"])) {
						$data[":photo"] = self::fileResolve($p["file_id"]);
					}
				}
			}

			/**
			 * User has not been stored in database.
			 */
			$data[":is_bot"] = ($parData["is_bot"] ? '1' : '0');

			if ($logType == 1) {
				$u = 1;
				$v = 0;
			} else if ($logType == 2) {
				$u = 0;
				$v = 1;
			} else {
				$u = $v = 0;
			}

			$this->pdo->prepare("INSERT INTO `users` (`user_id`, `username`, `first_name`, `last_name`, `photo`, `is_bot`, `group_msg_count`, `private_msg_count`, `created_at`) VALUES (:user_id, :username, :first_name, :last_name, :photo, :is_bot, {$u}, {$v}, :created_at);")->execute($data);
			unset($data[":is_bot"]);
			$createUserHistory = true;
		}

		if ($createUserHistory) {
			$this->pdo->prepare("INSERT INTO `users_history` (`user_id`, `username`, `first_name`, `last_name`, `photo`, `created_at`) VALUES (:user_id, :username, :first_name, :last_name, :photo, :created_at);")->execute($data);
		}
	}
}
