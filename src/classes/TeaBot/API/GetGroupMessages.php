<?php

namespace TeaBot\API;

use DB;
use PDO;
use Exception;

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

		if (isset($_GET["order_by"]) && is_string($_GET["order_by"])) {
			$orderBy = strtolower($_GET["order_by"]);
		} else {
			$orderBy = "id";
		}

		if (isset($_GET["order_type"]) && is_string($_GET["order_type"])) {
			$orderType = strtolower($_GET["order_type"]);
		} else {
			$orderType = "asc";
		}

		$allowedField = ["id", "group_id", "user_id", "tmsg_id", "reply_to_tmsg_id", "msg_type", "text", "text_entities", "file", "is_edited", "tmsg_datetime", "created_at"];

		if (!in_array($orderBy, $allowedField)) {
			throw new Exception("Invalid field \"{$orderBy}\"");
			return;
		}

		if (($orderType !== "asc") && ($orderType !== "desc")) {
			throw new Exception("Invalid order type \"{$orderType}\"");
			return;
		}

		$queryFields = [];
		$queryDataBind = [];
		$whereClause = "";
		$i = 0;
		foreach ($allowedField as $v) {
			if (isset($_GET[$v]) && is_string($_GET[$v])) {
				$queryFields[$i] = $v;
				$whereClause = ($i ? " AND " : "")."`{$v}` = :{$v}";
				$queryDataBind[$v] = $_GET[$v];
				$i++;
			}
		}

		print "{\"success\":true,\"param\":";
		print json_encode(
			[
				"query_data_bind" => $queryDataBind,
				"limit" => $limit,
				"offset" => $offset,
				"order_by" => $orderBy,
				"order_type" => $orderType
			]
		);
		print ",\"data\":[";

		if (count($queryFields) > 0) {
			
		}

		if ($whereClause !== "") {
			$whereClause = "WHERE ".$whereClause;
		}

		$query = "SELECT * FROM `groups_messages` {$whereClause} ORDER BY {$orderBy} {$orderType} LIMIT {$limit} OFFSET {$offset};";

		$st = $this->pdo->prepare($query);
		$st->execute($queryDataBind);

		$i = 0;
		while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
			$r["id"] = (int)$r["id"];
			$r["group_id"] = (int)$r["group_id"];
			$r["user_id"] = (int)$r["user_id"];
			$r["tmsg_id"] = (int)$r["tmsg_id"];
			$r["file"] = (int)$r["file"];
			$r["is_edited"] = (bool)$r["is_edited"];
			print ($i ? "," : "").json_encode($r, JSON_UNESCAPED_SLASHES);
			flush();
			$i++;
		}
		print "]}";
		$this->pdo = null;
		DB::close();
	}
}

