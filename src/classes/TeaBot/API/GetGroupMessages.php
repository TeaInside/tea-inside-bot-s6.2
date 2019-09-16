<?php

namespace TeaBot\API;

use DB;
use PDO;

class GetGroupMessages
{
	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->pdo = DB::pdo();
	}

	/**
	 * @return void
	 */
	public function dispatch(): void
	{
		header("Content-Type: application/json");

		if (isset($_GET["limit"]) && is_numeric($_GET["limit"]) && ($_GET["limit"] >= 0)) {
			$limit = (int)$_GET["limit"];
		} else {
			$limit = 30;
		}

		if (isset($_GET["offset"]) && is_numeric($_GET["offset"]) && ($_GET["offset"] >= 0)) {
			$offset = (int)$_GET["offset"];
		} else {
			$offset = 0;
		}

		print "{\"success\":true,\"param\":{\"limit\":{$limit},\"offset\":{$offset}},\"data\":[";

		$st = $this->pdo->prepare("SELECT * FROM `groups_messages` LIMIT {$limit} OFFSET {$offset};");
		$st->execute();

		$i = 0;
		while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
			print ($i ? "," : "").json_encode($r, JSON_UNESCAPED_SLASHES);
			$i++;
		}

		print "]}";
	}
}
