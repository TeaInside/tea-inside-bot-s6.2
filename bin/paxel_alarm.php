<?php

use TeaBot\Plugins\Paxel\Paxel as BasePaxel;

require __DIR__."/../bootstrap/autoload.php";

date_default_timezone_set("Asia/Jakarta");

loadConfig("telegram_bot");

is_dir(STORAGE_PATH."/paxel") or mkdir(STORAGE_PATH."/paxel");
define("PAXEL_TG", STORAGE_PATH."/paxel/tg");
define("PAXEL_DIR", STORAGE_PATH."/paxel/base_paxel");
is_dir(PAXEL_TG) or mkdir(PAXEL_TG);
is_dir(PAXEL_DIR) or mkdir(PAXEL_DIR);

$groupId = -1001491638996;

$u = json_decode(file_get_contents(PAXEL_TG."/976357499"), true);

while (true) {
	$px = new BasePaxel($u["username"], $u["password"]);

	$newPackage = $px->package(true);
	if (file_exists(PAXEL_DIR."/package.json")) {
		$oldPackage = file_get_contents(PAXEL_DIR."/package.json");
	} else {
		$oldPackage = "";
	}

	if ($newPackage && ($newPackage !== $oldPackage)) {
		$r = "[".date("d F Y H:i:s")."]\n\nSome changes on package list were made!\n\n";
		$package = json_decode($newPackage, true);
		$oPackage = json_decode($oldPackage, true);

		foreach ($package["data"] as $k => $v) {
			foreach ($oPackage as $kk => $vv) {
				if (sha1(json_encode($v)) === sha1(json_encode($vv))) {
					unset($package["data"][$k]);
				}
			}
		}

		$package["data"] = array_values($package["data"]);

		foreach ($package["data"] as $k => $v) {
	        foreach ($v as $kk => $vv) {
	            $r .= "<b>".htmlspecialchars(ucfirst($kk), ENT_QUOTES).
	                ":</b> ".htmlspecialchars($vv, ENT_QUOTES)."\n";
	        }
	        $r .= "\n";
	    }

		file_put_contents(PAXEL_DIR."/package.json", $newPackage);
		TeaBot\Exe::sendMessage(
			[
				"chat_id" => $groupId,
				"text" => $r,
				"parse_mode" => "HTML"
			]
		);
		echo "There are some changes!\n";
	} else {
		echo "No changes!\n";
	}

	sleep(15);
}
