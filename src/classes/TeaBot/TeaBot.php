<?php

namespace TeaBot;

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
			$st = new Response($this->data);
			$st->run();
			exit(0);
		}

		if (!($logPid = pcntl_fork())) {
			cli_set_process_title("logger");
			$st = new Logger($this->data);
			$st->run();
			exit(0);
		}

		$status = null;
		pcntl_waitpid($resPid, $status, WUNTRACED);
		pcntl_waitpid($logPid, $status, WUNTRACED);
	}
}
