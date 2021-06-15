<?php

declare(strict_types=1);

namespace sys\jordan\meetup\game;


use Logger;
use pocketmine\plugin\PluginLogger;
use sys\jordan\meetup\utils\GameTrait;
use Throwable;

class GameLogger implements Logger {

	use GameTrait;

	protected string $prefix;
	private PluginLogger $logger;

	public function __construct(Game $game) {
		$this->setGame($game);
		$this->prefix = "[Game #{$game->getId()}] ";
		$this->logger = $game->getPlugin()->getLogger();
	}

	/**
	 * @param string $message
	 */
	public function emergency($message): void {
		$this->logger->emergency($this->prefix . $message);
	}

	/**
	 * @param string $message
	 */
	public function alert($message) {
		$this->logger->alert($this->prefix . $message);
	}

	/**
	 * @param string $message
	 */
	public function critical($message) {
		$this->logger->critical($this->prefix . $message);
	}

	/**
	 * @param string $message
	 */
	public function error($message) {
		$this->logger->error($this->prefix . $message);
	}

	/**
	 * @param string $message
	 */
	public function warning($message) {
		$this->logger->warning($this->prefix . $message);
	}

	/**
	 * @param string $message
	 */
	public function notice($message) {
		$this->logger->notice($this->prefix . $message);
	}

	/**
	 * @param string $message
	 */
	public function info($message) {
		$this->logger->info($this->prefix . $message);
	}

	/**
	 * @param string $message
	 */
	public function debug($message) {
		$this->logger->debug($this->prefix . $message);
	}

	/**
	 * @param string $level
	 * @param string $message
	 */
	public function log($level, $message) {
		$this->logger->log($level, $this->prefix . $message);
	}

	public function logException(Throwable $e, $trace = null) {
		$this->logger->logException($e, $trace);
	}
}