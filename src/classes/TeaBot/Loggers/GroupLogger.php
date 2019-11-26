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
		/**
		 * @see TeaBot\LoggerFoundation
		 *
		 * 1 means group logger
		 */
		$this->userLogger($this->data, 1);
		$this->groupLogger();
	}

	/**
	 * @return void
	 */
	private function groupLogger(): void
	{
		$this->groupHash = sha1($this->data["group_id"]);
		$t = 0;
		while (static::f_is_locked("group", $this->groupHash)) {
			if ($t >= 30) {
				self::funlock("group", $this->groupHash);
				break;
			}
			sleep(1);
			$t++;
		}
		static::flock("group", $this->groupHash);

		$e = null;
		try {
			$this->unsafeGroupLogger();
		} catch (Exception $e) {
		} catch (Error $e) {
		}

		self::funlock("group", $this->groupHash);
		if ($e) throw $e;
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
			$st = $this->pdo->prepare("SELECT `telegram_file_id` FROM `files` WHERE `id` = :id LIMIT 1;");
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
	private function unsafeGroupLogger(): void
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

			$r["photo"] = (int)$r["photo"];

			if (($r["msg_count"] % 5) === 0) {
				$resolvedPhoto = $this->groupPhotoResolve($r["photo"]);
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
		static::funlock("group", $this->groupHash);
	}
}
