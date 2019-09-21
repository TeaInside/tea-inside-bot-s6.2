<?php

namespace TeaBot\Responses\Amikom;

use stdClass;
use TeaBot\Exe;
use TeaBot\Data;
use TeaBot\Lang;
use TeaBot\ResponseFoundation;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class Mahasiswa extends ResponseFoundation
{
	/**
	 * @var string
	 */
	private $storagePath;

	/**
	 * @param \TeaBot\Data &$data
	 *
	 * Constructor.
	 */
	public function __construct(Data &$data)
	{
		parent::__construct($data);
		$this->storagePath = STORAGE_PATH."/telegram/amikom/mahasiswa/{$this->data["user_id"]}";
		is_dir(STORAGE_PATH) or mkdir(STORAGE_PATH);
		is_dir(STORAGE_PATH."/telegram") or mkdir(STORAGE_PATH."/telegram");
		is_dir(STORAGE_PATH."/telegram/amikom") or mkdir(STORAGE_PATH."/telegram/amikom");
		is_dir(STORAGE_PATH."/telegram/amikom/mahasiswa") or mkdir(STORAGE_PATH."/telegram/amikom/mahasiswa");
		is_dir($this->storagePath) or mkdir($this->storagePath);
	}

	/**
	 * @param $nim  string
	 * @param $pass string
	 * @return string
	 */
	private function loginPrivate(string $nim, string $pass): ?string
	{
		$o = $this->curl(
			"http://mhsmobile.amikom.ac.id/login",
			[
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query(["username" => $nim, "keyword" => $pass])
			]
		);
		$o = json_decode($o->out, true);
		if (isset($o["access_token"], $o["expires_in"])) {
			file_put_contents(
				$this->storagePath."/auth.json",
				json_encode(
					[
						"nim" => $nim,
						"pass" => $pass
					],
					JSON_UNESCAPED_SLASHES
				)
			);
			file_put_contents(
				$this->storagePath."/token.json",
				json_encode(
					[
						"access_token" => $o["access_token"],
						"nim" => $nim,
						"expired" => (time() + $o["expires_in"] - 300)
					],
					JSON_UNESCAPED_SLASHES
				)
			);
			return $o["access_token"];
		}
		return null;
	}


	/**
	 * @return ?string
	 */
	private function getToken(): ?string
	{
		if (file_exists($this->storagePath."/token.json")) {
			$json = json_decode(file_get_contents($this->storagePath."/token.json"), true);
			if ($json["expired"] > time()) {
				return $json["access_token"];
			}
		}

		if (file_exists($this->storagePath."/auth.json")) {
			$json = json_decode(file_get_contents($this->storagePath."/auth.json"), true);
			if ($token = $this->loginPrivate($json["nim"], $json["pass"])) {
				return $token;
			}
		}

		Exe::sendMessage(
			[
				"chat_id" => $this->data["chat_id"],
				"reply_to_message_id" => $this->data["msg_id"],
				"text" => "Internal Server Error (1)",
				"parse_mode" => "HTML"
			]
		);

		return null;
	}

	/**
	 * @param string $code
	 * @return bool
	 */
	public function presensi(string $code): bool
	{
		if (file_exists($this->storagePath."/auth.json")) {
			$json = json_decode(file_get_contents($this->storagePath."/auth.json"), true);
		} else {
			Exe::sendMessage(
				[
					"chat_id" => $this->data["chat_id"],
					"reply_to_message_id" => $this->data["msg_id"],
					"text" => "You have not logged in yet!",
					"parse_mode" => "HTML"
				]
			);
			goto ret;
		}

		$data = "{$code};{$json["nim"]}";

		$o = $this->curl(
			"http://202.91.9.14:6000/api/presensi_mobile/validate_ticket",
			[
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => json_encode(["data" => $data]),
				CURLOPT_HTTPHEADER => [
					"Content-Type: application/json",
					"Connection" => "Keep-Alive",
					"Accept-Encoding" => "gzip"
				],
				CURLOPT_USERAGENT => "okhttp/3.10.0",
				CURLOPT_CONNECTTIMEOUT => 15,
				CURLOPT_TIMEOUT => 30
			]
		);
		$out = json_decode($o->out, true);

		$r = "Request Body:\n<pre>".htmlspecialchars($data, ENT_QUOTES, "UTF-8").
			"</pre>\n\nResponse Body:\n<pre>".htmlspecialchars($o->out, ENT_QUOTES, "UTF-8").
			"</pre>";

		Exe::sendMessage(
			[
				"chat_id" => $this->data["chat_id"],
				"reply_to_message_id" => $this->data["msg_id"],
				"text" => $r,
				"parse_mode" => "HTML"
			]
		);

		if (isset($out["message"]) && ($out["message"] === "Created")) {
			$r = "Presensi Sukses!";
		} else {
			$r = "Presensi Gagal!";
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

	/**
	 * @param bool   $isLogin
	 * @param string $nim
	 * @param string $pass
	 * @return bool
	 */
	public function profile(bool $isLogin = false, string $nim = "", string $pass = ""): bool
	{
		if ($isLogin) {
			$token = $this->loginPrivate($nim, $pass);
		} else {
			$token = $this->getToken();
		}

		if (is_null($token)) {
			$reply = $isLogin ? "Login Failed!" : "Invalid credentials!\n\nPlease login again!";
			goto ret;
		}

		$oo = $this->curl(
			"http://mhsmobile.amikom.ac.id/api/personal/init_data_mhs",
			[
				CURLOPT_HTTPHEADER => ["Authorization: {$token}"]
			]
		);
		$oo = json_decode($oo->out, true);
		if (isset($oo["Mhs"], $oo["PeriodeAkademik"])) {
			$isLogin and Exe::sendMessage(
				[
					"chat_id" => $this->data["chat_id"],
					"reply_to_message_id" => $this->data["msg_id"],
					"text" => "Login Success!",
				]
			);
			Exe::sendPhoto(
				[
					"chat_id" => $this->data["chat_id"],
					"reply_to_message_id" => $this->data["msg_id"],
					"photo" => $oo["Mhs"]["NpmImg"]
				]
			);

			foreach ($oo["Mhs"] as &$v) {
				$v = empty($v) ? "-" : htmlspecialchars($v, ENT_QUOTES, "UTF-8");
			}
			foreach ($oo["PeriodeAkademik"] as &$v) {
				$v = empty($v) ? "-" : htmlspecialchars($v, ENT_QUOTES, "UTF-8");
			}
			unset($v);

			$reply = "<b>[Informasi Akun]</b>\n";
			$reply .= "<b>NIM:</b> {$oo["Mhs"]["Npm"]}\n";
			$reply .= "<b>Nama:</b> {$oo["Mhs"]["Nama"]}\n";
			$reply .= "<b>Angkatan:</b> {$oo["Mhs"]["Angkatan"]}\n";
			$reply .= "<b>Email AMIKOM:</b> {$oo["Mhs"]["EmailAmikom"]}\n\n";
			$reply .= "<b>Periode Akademik:</b> {$oo["PeriodeAkademik"]["TahunAkademik"]}\n";
			$reply .= "<b>Semester:</b> {$oo["PeriodeAkademik"]["Semester"]}";

			file_put_contents(
				$this->storagePath."/info.json",
				json_encode($oo, JSON_UNESCAPED_SLASHES)
			);
		} else {
			$reply = "Invalid credentials!\n\nPlease login again!";
		}

		ret:
		Exe::sendMessage(
			[
				"chat_id" => $this->data["chat_id"],
				"reply_to_message_id" => $this->data["msg_id"],
				"text" => $reply,
				"parse_mode" => "HTML"
			]
		);
		return true;
	}

	/**
	 * @param string $nim
	 * @param string $pass
	 * @return bool
	 */
	public function login(string $nim, string $pass): bool
	{
		if ($this->profile(true, $nim, $pass)) {
			return true;	
		}
		$reply = "Login Failed";		
		Exe::sendMessage(
			[
				"chat_id" => $this->data["chat_id"],
				"reply_to_message_id" => $this->data["msg_id"],
				"text" => $reply,
				"parse_mode" => "HTML"
			]
		);
		return true;
	}

	/**
	 * @param string  $nim
	 * @param string  $pass
	 * @param string  $hari
	 * @return bool
	 */
	public function jadwal(string $_hari = ""): bool
	{
		$token = $this->getToken();

		if (is_null($token)) {
			Exe::sendMessage(
				[
					"chat_id" => $this->data["chat_id"],
					"reply_to_message_id" => $this->data["msg_id"],
					"text" => "Internal Server Error (2)",
					"parse_mode" => "HTML"
				]
			);
			goto ret;
		}

		$o = $this->curl(
			"http://mhsmobile.amikom.ac.id/api/personal/jadwal_kuliah",
			[
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query(["npm" => null, "semester" => 0]),
				CURLOPT_HTTPHEADER => ["Authorization: {$token}"]
			]
		);

		$o = json_decode($o->out, true);

		$jadwal = [];
		foreach ($o as $v) {
			$jadwal[$v["Hari"]][] = $v;
		}
		unset($o);

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
				$r .= "<b>Mata Kuliah</b>: ".$vv["MataKuliah"]." ({$vv["JenisKuliah"]})\n";
				$r .= "<b>Keterangan</b>: ".($vv["Keterangan"] === "()" ? "-" : $vv["Keterangan"])."\n";
				$r .= "<b>Ruang</b>: <code>".$vv["Ruang"]."</code>\n";
				$r .= "<b>Waktu</b>: <code>".$vv["Waktu"]."</code>\n";
				$r .= "<b>Kelas</b>: ".$vv["Kelas"]." ({$vv["Jenjang"]})\n";
				$r .= "<b>NIK</b>: <code>".$vv["Nik"]."</code>\n";
				$r .= "<b>Nama Dosen</b>: ".$vv["NamaDosen"]."\n";
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
