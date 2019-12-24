<?php

namespace TeaBot\API\Chart;

use DB;
use PDO;

class Chart
{
	/**
	 * @param string $startDate
	 * @param string $endDate
	 */
	public static function messages(string $startDate, string $endDate)
	{
		$pdo = DB::pdo();
		$st = $pdo->prepare("
			SELECT 1 as `k`, COUNT(1) as `messages`, DATE(`tmsg_datetime`) as `date` FROM `groups_messages`
			WHERE `group_id` = -1001162202776 AND
			`tmsg_datetime` >= :start_date AND
			`tmsg_datetime` <= :end_date
			GROUP BY DATE(`tmsg_datetime`)

			UNION

			SELECT 2 as `k`, COUNT(1) as `messages`, DATE(`tmsg_datetime`) as `date` FROM `groups_messages`
			WHERE `group_id` = -1001120283944 AND
			`tmsg_datetime` >= :start_date AND
			`tmsg_datetime` <= :end_date
			GROUP BY DATE(`tmsg_datetime`)"
		);
		$st->execute(
			[
				":start_date" => date("Y-m-d 00:00:00", strtotime($startDate)),
				":end_date" => date("Y-m-d 23:59:59", strtotime($endDate))
			]
		);
		$res = [
			"labels" => [],
			"datasets" => [
				[
					"label" => "Koding Teh",
					"data" => [],
					"backgroundColor" => "red",
					"borderColor" => "red",
					"borderWidth" => 3,
					"fill" => false
				],
				[
					"label" => "Tea Inside Indonesia",
					"data" => [],
					"backgroundColor" => "green",
					"borderColor" => "green",
					"borderWidth" => 3,
					"fill" => false
				]
			]
		];
		$r = $st->fetchAll(PDO::FETCH_ASSOC);
		foreach ($r as $k => $v) {
			if ($v["k"] == 1) {
				$res["labels"][] = date("d F Y", strtotime($v["date"]));
				$res["datasets"][0]["data"] = $v["messages"];
			} else {
				$res["datasets"][1]["data"] = $v["messages"];
			}
		}
		echo json_encode($res);
	}
}
