<?php

namespace DynamicHub\Session;

use DynamicHub\DynamicHub;
use pocketmine\Player;

class Session{
	/** @var DynamicHub */
	private $plugin;
	/** @var Player */
	private $player;
	/** @var \DynamicHub\Module\Module */
	private $session;
	public function __construct(DynamicHub $plugin, Player $player){
		$this->plugin = $plugin;
		$this->player = $player;
		$this->session = $this->plugin->getHub();
		$this->session->onJoin($this);
	}
	public function finalize(){
		$this->session->onQuit($this);
	}
	/**
	 * @return Player
	 */
	public function getPlayer(){
		return $this->player;
	}
}
