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
	 * @return void
	 */
	abstract public function saveUserInfo(): void;

	/**
	 * @param string $telegramFileId
	 * @return ?int
	 */
	public static function fileResolve(string $telegramFileId): ?int
	{
		var_dump("resolving {$telegramFileId}");
		$o = json_decode(Exe::getFile(["file_id" => $telegramFileId])["out"], true);
		var_dump($o);
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
			$absolute_hash = $sha1_hash.$md5_hash;

			// Check whether there is the same file in storage by matching its absolute hash.
			$pdo = DB::pdo();
			$st = $pdo->prepare("SELECT `id` FROM `files` WHERE `absolute_hash` = :absolute_hash LIMIT 1;");
			$st->execute([":absolute_hash" => $absolute_hash]);
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
			$pdo->prepare("INSERT INTO `files` (`telegram_file_id`, `md5_sum`, `sha1_sum`, `absolute_hash`, `file_type`, `extension`, `size`, `hit_count`, `created_at`) VALUES (:telegram_file_id, :md5_sum, :sha1_sum, :absolute_hash, :file_type, :extension, :size, :hit_count, :created_at);")
				->execute(
					[
						":telegram_file_id" => $telegramFileId,
						":md5_sum" => $md5_hash,
						":sha1_sum" => $sha1_hash,
						":absolute_hash" => $absolute_hash,
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
}
