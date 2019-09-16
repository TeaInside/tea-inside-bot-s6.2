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
				if ($v === "id") {
				    $_GET["id"] = (int)$_GET["id"];
				} else if ($v === "group_id") {
				    $_GET["group_id"] = (int)$_GET["group_id"];
				} else if ($v === "user_id") {
				    $_GET["user_id"] = (int)$_GET["user_id"];
				} else if ($v === "tmsg_id") {
				    $_GET["tmsg_id"] = (int)$_GET["tmsg_id"];
				} else if ($v === "file") {
				    $_GET["file"] = (int)$_GET["file"];
				} else if ($v === "is_edited") {
				    $_GET["is_edited"] = (
				    	($_GET["is_edited"] === "false" ? false : (
				    		$_GET["is_edited"] === "true" ? true :
				    			(bool)$_GET["is_edited"]
				    	))
				    );
				} else if ($v === "reply_to_tmsg_id") {
				    $_GET["reply_to_tmsg_id"] = (int)$_GET["reply_to_tmsg_id"];
				} else if ($v === "text_entities") {
				    $_GET["text_entities"] = is_null($_GET["text_entities"]) ? [] : json_decode($_GET["text_entities"], true);
				}
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
				"order_type" => $orderType,
				"data_fields" => $allowedField,
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

		isset($queryDataBind["is_edited"]) && $queryDataBind["is_edited"] = (string)((int)$queryDataBind["is_edited"]);
		$st->execute($queryDataBind);

		$i = 0;
		while ($r = $st->fetch(PDO::FETCH_NUM)) {
			$r[0] = (int)$r[0];
			$r[1] = (int)$r[1];
			$r[2] = (int)$r[2];
			$r[3] = (int)$r[3];
			$r[4] = (int)$r[4];
			$r[7] = is_null($r[7]) ? [] : json_decode($r[7], true);
			is_null($r[8]) or $r[8] = (int)$r[8];
			$r[9] = (bool)$r[9];
			echo ($i ? "," : "").json_encode($r, JSON_UNESCAPED_SLASHES);
			$i++;
		}
		print "]}";
		$this->pdo = null;
		DB::close();
	}
}

