<?php

namespace TeaBot\Responses;

use stdClass;
use TeaBot\Exe;
use TeaBot\Lang;
use TeaBot\ResponseFoundation;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class Amikom extends ResponseFoundation
{
	/**
	 * @param string  $nim
	 * @param string  $pass
	 * @param string  $hari
	 * @return bool
	 */
	public function jadwal(string $nim, string $pass, ?string $_hari = ""): bool
	{
		$debugData = json_encode($this->data->in, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

		$o = $this->curl(
			"http://mhsmobile.amikom.ac.id/login",
			[
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query(["username" => $nim, "keyword" => $pass])
			]
		);

		$o = json_decode($o->out, true);

		if (!isset($o["access_token"])) {
			Exe::sendMessage(
				[
					"chat_id" => $this->data["chat_id"],
					"reply_to_message_id" => $this->data["msg_id"],
					"text" => "Login Failed!",
					"parse_mode" => "HTML"
				]
			);
			goto ret;
		}

		$bearer = ["Authorization: {$o["access_token"]}"];

		$o = $this->curl(
			"http://mhsmobile.amikom.ac.id/api/personal/jadwal_kuliah",
			[
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query(["npm" => $nim, "semester" => 0]),
				CURLOPT_HTTPHEADER => $bearer
			]
		);

		$a = json_decode($o->out, true);

		$jadwal = [];
		foreach ($a as $v) {
			$jadwal[$v["Hari"]][] = $v;
		}
		unset($a);

		$r = "";
		$i = 0;
		$hh = ($_hari !== "");
		$_hari = trim(strtolower($_hari));
		foreach ($jadwal as $hari => $v) {
			if ($hh && (strtolower($hari) !== $_hari)) {
				continue;
			}
			$i and $r .= "\n\n";
			$r .= "<b>{$hari}:</b>\n";
			foreach ($v as $vv) {
				$vv["Keterangan"] = htmlspecialchars(trim($vv["Keterangan"]), ENT_QUOTES, "UTF-8");
				$vv["Ruang"] = htmlspecialchars(trim($vv["Ruang"]), ENT_QUOTES, "UTF-8");
				$vv["Waktu"] = htmlspecialchars(trim($vv["Waktu"]), ENT_QUOTES, "UTF-8");
				$vv["MataKuliah"] = htmlspecialchars(trim($vv["MataKuliah"]), ENT_QUOTES, "UTF-8");
				$vv["JenisKuliah"] = htmlspecialchars(trim($vv["JenisKuliah"]), ENT_QUOTES, "UTF-8");
				$vv["Kelas"] = htmlspecialchars(trim($vv["Kelas"]), ENT_QUOTES, "UTF-8");
				$vv["Jenjang"] = htmlspecialchars(trim($vv["Jenjang"]), ENT_QUOTES, "UTF-8");
				$vv["Nik"] = htmlspecialchars(trim($vv["Nik"]), ENT_QUOTES, "UTF-8");
				$vv["NamaDosen"] = htmlspecialchars(trim($vv["NamaDosen"]), ENT_QUOTES, "UTF-8");

				$r .= "[".$vv["IdKuliah"]."]\n";
				$r .= "Mata Kuliah: ".$vv["MataKuliah"]." ({$vv["JenisKuliah"]})\n";
				$r .= "Keterangan: ".($vv["Keterangan"] === "()" ? "-" : $vv["Keterangan"])."\n";
				$r .= "Ruang: ".$vv["Ruang"]."\n";
				$r .= "Waktu: ".$vv["Waktu"]."\n";
				$r .= "Kelas: ".$vv["Kelas"]." ({$vv["Jenjang"]})\n";
				$r .= "NIK: ".$vv["Nik"]."\n";
				$r .= "Nama Dosen: ".$vv["NamaDosen"]."\n";
				$r .= "--\n";
			}
			$i++;
		}

		Exe::sendMessage(
			[
				"chat_id" => $this->data["chat_id"],
				"reply_to_message_id" => $this->data["msg_id"],
				"text" => $r,
				"parse_mode" => "HTML"
			]
		);

		ret:
		return true;
	}


	private function curl($url, $opt = []): stdClass
	{
		$ch = curl_init($url);
		$optf = [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => "AmikomMobile"
		];
		foreach ($opt as $k => $v) {
			$optf[$k] = $v;
		}
		curl_setopt_array($ch, $optf);
		$o = new \stdClass;
		$o->out = curl_exec($ch);
		$o->info = curl_getinfo($ch);
		$o->error = curl_error($ch);
		$o->errno = curl_errno($ch);
		curl_close($ch);
		return $o;
	}
}
