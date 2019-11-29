<?php

namespace TeaBot;

use Error;
use Exception;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class TeaBot
{
	/**
	 * @var \TeaBot\Data
	 */
	private $data;

	/**
	 * @param string $json
	 * @throws \Exception
	 *
	 * Constructor.
	 */
	public function __construct(string $json)
	{
		$json = json_decode($json, true);
		if (!is_array($json)) {
			throw new Exception("Invalid JSON input");
		}
		$this->data = new Data($json);
	}

	/**
	 * @return void
	 */
	public function run(): void
	{
		if (!($resPid = pcntl_fork())) {
			cli_set_process_title("response");
			try {
				$st = new Response($this->data);
				$st->run();	
			} catch (Error $e) {

				$err = $e->__toString();
				$errHash = sha1($err);

				Exe::sendMessage(
					[
						"chat_id" => -1001128970273,
						"text" => "[Error res {$errHash}]\n{$err}",
						"parse_mode" => "HTML"
					]
				);

				Exe::sendMessage(
					[
						"chat_id" => -1001128970273,
						"text" => "[Rb {$errHash}]\n".json_encode($this->data->in, JSON_UNESCAPED_SLASHES),
						"parse_mode" => "HTML"
					]
				);

				throw $e;
			}
			exit(0);
		}

		if (!($logPid = pcntl_fork())) {
			cli_set_process_title("logger");
			try {
				$st = new Logger($this->data);
				$st->run();
			} catch (Error $e) {

				$err = $e->__toString();
				$errHash = sha1($err);

				Exe::sendMessage(
					[
						"chat_id" => -1001128970273,
						"text" => "[Error log {$errHash}]\n{$err}",
						"parse_mode" => "HTML"
					]
				);

				Exe::sendMessage(
					[
						"chat_id" => -1001128970273,
						"text" => "[Rb {$errHash}]\n".json_encode($this->data->in, JSON_UNESCAPED_SLASHES),
						"parse_mode" => "HTML"
					]
				);

				throw $e;
			}
			exit(0);
		}

		$status = null;
		pcntl_waitpid($resPid, $status, WUNTRACED);
		pcntl_waitpid($logPid, $status, WUNTRACED);
	}
}
