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
				$res["labels"][] = date("d M Y", strtotime($v["date"]));
				$res["datasets"][0]["data"][] = $v["messages"];
			} else {
				$res["datasets"][1]["data"][] = $v["messages"];
			}
		}
		echo json_encode($res);
		DB::close();
	}

	/**
	 * @param string $startDate
	 * @param string $endDate
	 */
	public static function userStats(string $startDate, string $endDate)
	{
		$pdo = DB::pdo();
		$st = $pdo->prepare("
			SELECT * FROM (SELECT
				1 as `k`,
				`a`.`user_id`,
				CONCAT(`b`.`first_name`,
				  CASE WHEN `b`.`last_name` IS NULL THEN ''
				  ELSE CONCAT(' ', `b`.`last_name`) END
				) AS `name`,
				`b`.`username`,
				LOWER(CONCAT(HEX(`c`.`md5_sum`), '_',
				   HEX(`c`.`sha1_sum`), '.', `c`.`extension`)
				) AS `photo`,
				COUNT(1) as `messages`
			FROM `groups_messages` AS `a`
			INNER JOIN `users` AS `b`
			ON `b`.`user_id` = `a`.`user_id`
			LEFT JOIN `files` AS `c`
			ON `c`.`id` = `b`.`photo`
			WHERE `group_id` = -1001162202776
			AND `tmsg_datetime` >= :start_date
			AND `tmsg_datetime` <= :end_date
			GROUP BY `a`.`user_id`
			ORDER BY `messages` DESC) x

			UNION

			SELECT * FROM (SELECT
				2 as `k`,
				`a`.`user_id`,
				CONCAT(`b`.`first_name`,
				  CASE WHEN `b`.`last_name` IS NULL THEN ''
				  ELSE CONCAT(' ', `b`.`last_name`) END
				) AS `name`,
				`b`.`username`,
				LOWER(CONCAT(HEX(`c`.`md5_sum`), '_',
				   HEX(`c`.`sha1_sum`), '.', `c`.`extension`)
				) AS `photo`,
				COUNT(1) as `messages`
			FROM `groups_messages` AS `a`
			INNER JOIN `users` AS `b`
			ON `b`.`user_id` = `a`.`user_id`
			LEFT JOIN `files` AS `c`
			ON `c`.`id` = `b`.`photo`
			WHERE `group_id` = -1001120283944
			AND `tmsg_datetime` >= :start_date
			AND `tmsg_datetime` <= :end_date
			GROUP BY `a`.`user_id`
			ORDER BY `messages` DESC) y;
		");
		$st->execute(
			[
				":start_date" => date("Y-m-d 00:00:00", strtotime($startDate)),
				":end_date" => date("Y-m-d 23:59:59", strtotime($endDate))
			]
		);
		echo json_encode($st->fetchAll(PDO::FETCH_NUM));
		DB::close();
	}
}
