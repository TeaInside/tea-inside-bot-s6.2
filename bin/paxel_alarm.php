<?php

use TeaBot\Plugins\Paxel\Paxel as BasePaxel;

require __DIR__."/../bootstrap/autoload.php";
require __DIR__."/paxel_extra.php";

date_default_timezone_set("Asia/Jakarta");

loadConfig("telegram_bot");

const SKIP_ADD_ONS = [
    "Box L",
    "Box M",
    "Box S",
    "Berkahnya Sedekah",
    "Kita Bahagia",
    "Semua Bisa",
    "Bagi Berkah",
    " Choayo1212",
];

is_dir(STORAGE_PATH."/paxel") or mkdir(STORAGE_PATH."/paxel");
define("PAXEL_TG", STORAGE_PATH."/paxel/tg");
define("PAXEL_DIR", STORAGE_PATH."/paxel/base_paxel");
is_dir(PAXEL_TG) or mkdir(PAXEL_TG);
is_dir(PAXEL_DIR) or mkdir(PAXEL_DIR);

$groupId = -1001491638996;

$i = 0;
#$u = json_decode(file_get_contents(PAXEL_TG."/243692601"), true);
$u["username"] = "dickytobing";
$u["password"] = "Dicky123";

while (true) {
    $px = new BasePaxel($u["username"], $u["password"]);

    $newPackage = $px->package(true);
    $date = date("d F Y H:i:s");
    if (file_exists(PAXEL_DIR."/package.json")) {
        $oldPackage = file_get_contents(PAXEL_DIR."/package.json");
    } else {
        $oldPackage = "";
    }

    if ($newPackage && ($newPackage !== $oldPackage)) {
        file_put_contents(PAXEL_DIR."/package.json", $newPackage);
        $r = "<b>[".$date."]</b>\n\nSome changes on package list were made!\n\n";
        $package = json_decode($newPackage, true);
        $oPackage = json_decode($oldPackage, true);

        if (!is_array($oPackage)) {
            $oPackage = [];
        }

        foreach ($package["data"] as $k => $v) {
            foreach ($oPackage["data"] as $kk => $vv) {
                if (sha1(json_encode($v)) === sha1(json_encode($vv))) {
                    unset($package["data"][$k]);
                }
            }

            if (in_array($v["code"], SKIP_ADD_ONS)) {
                unset($package["data"][$k]);
            }
        }

        if (count($package["data"])) {
            $package["data"] = array_values($package["data"]);
            foreach ($package["data"] as $k => $v) {
                foreach ($v as $kk => $vv) {
                    $r .= "<b>".htmlspecialchars($kk, ENT_QUOTES).
                        ":</b> ".htmlspecialchars($vv, ENT_QUOTES)."\n";
                }
                $r .= "\n";

                foreach (PAXEL_EXTRA as $kk => $vv) {
                    if (preg_match("/^".$kk."$/Usi", $v["code"])) {
                        foreach ($vv as $kkk => $vvv) {
                            $r .= "<b>".htmlspecialchars($kkk, ENT_QUOTES).
                                ":</b> ".htmlspecialchars($vvv, ENT_QUOTES)."\n";
                        }
                    }
                }

                $r .= "\n\n==============================\n";
            }

            TeaBot\Exe::sendMessage(
                [
                    "chat_id" => $groupId,
                    "text" => $r,
                    "parse_mode" => "HTML"
                ]
            );
            echo "[{$date}][{$i}] There are some changes!\n";
        }
    } else {
        echo "[{$date}][{$i}] No changes!\n";
    }

    sleep(5);
    $i++;
}
