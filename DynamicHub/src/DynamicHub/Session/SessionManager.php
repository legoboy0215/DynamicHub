<?php

namespace DynamicHub\Session;

use DynamicHub\DynamicHub;
use pocketmine\Player;

class SessionManager{
	/** @var DynamicHub */
	private $plugin;
	/** @var Session[] */
	private $sessions = [];
	public function __construct(DynamicHub $plugin){
		$this->plugin = $plugin;
	}
	public function startSession(Player $player){
		$this->sessions[$player->getId()] = $player;
	}
	public function endSession(Player $player){
		if(isset($this->sessions[$player->getId()])){
			$this->sessions[$player->getId()]->finalize();
			unset($this->sessions[$player->getId()]);
		}
	}
	/**
	 * @param Player $player
	 * @return Session|null
	 */
	public function getSession(Player $player){
		return isset($this->sessions[$player->getId()]) ? $this->sessions[$player->getId()]:null;
	}
}
