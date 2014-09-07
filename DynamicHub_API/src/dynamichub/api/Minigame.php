<?php

namespace dynamichub\api;

use dynamichub\DynamicHub;
use pocketmine\event\Listener;
use pocketmine\Player;

abstract class Minigame implements Listener{
	const QUIT_DISCONNECTION = 0;
	const QUIT_COMMAND = 1;
	const QUIT_TELEPORT = 2;
	const QUIT_UNKNOWN = -1;

	private $enabled = false;
	private $players = [];

	public abstract function getName();
	public abstract function getOwnedWorldNames();
	public function ownsWorld($world){
		if($world === true){
			return true;
		}
		foreach($this->getOwnedWorldNames() as $w){
			if(strtolower($world) === strtolower($w)){
				return true;
			}
		}
		return false;
	}
	public function getDefaultConfig(){
		return [];
	}
	public function getConfig(){
		return $this->getDynamicHub()->getMinigameConfig($this);
	}

	public final function isEnabled(){
		return $this->enabled;
	}
	public final function enable(){
		if($this->enabled === true){
			return;
		}
		$this->enabled = true;
		$this->onEnable();
	}
	public final function disable(){
		if($this->enabled === false){
			return;
		}
		$this->enabled = false;
		$this->onDisable();
	}
	protected function onEnable(){}
	protected function onDisable(){}
	public final function __destruct(){
		$this->disable();
	}

	public abstract function getServer();
	public function getDynamicHub(){
		return DynamicHub::getSafeInstance($this->getServer());
	}

	/**
	 * If this method returns an instance of Plugin, the minigame will be automatically
	 * disabled when the owning plugin is disabled.
	 * onDisable() will only be called once by the API.
	 *
	 * @return \pocketmine\plugin\Plugin|null
	 */
	public function getOwningPlugin(){
		return null;
	}
	public final function join(Player $player){
		if($this->onPlayerJoin($player)){
			$this->players[$player->getID()] = $player;
			return true;
		}
		return false;
	}
	public final function quit(Player $player, $method){
		$r = (bool) $this->onPlayerQuit($player, $method);
		if($method === self::QUIT_DISCONNECTION and !$r){
			$this->getDynamicHub()->getLogger()->warning("Cannot disallow minigame quit on player disconnection at " . get_class($this) . "::onPlayerQuit()");
			$r = true;
		}
		if($r){
			unset($this->players[$player->getID()]);
		}
		return $r;
	}
	/**
	 * @param Player $player
	 * @return bool
	 */
	public function onPlayerJoin(
		/** @noinspection PhpUnusedParameterInspection */
		Player $player){
		return true;
	}

	/**
	 * @param Player $player
	 * @param int $method refer to the constants Minigame::QUIT_***
	 * @return bool whether to allow quit. If $method === Minigame::QUIT_DISCONNECTION, returning false will result in a warning message and the return value will be ignored.
	 */
	public function onPlayerQuit(
		/** @noinspection PhpUnusedParameterInspection */
		Player $player, $method){
		return true;
	}
	public function countPlayers(){
		return count($this->players);
	}
	public function getPlayers(){
		return $this->players;
	}

	public function isPlayerInWorld(Player $player){
		return isset($this->players[$player->getID()]);
	}
}
