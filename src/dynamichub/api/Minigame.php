<?php

namespace dynamichub\api;

use dynamichub\DynamicHub;
use pocketmine\event\Listener;

abstract class Minigame implements Listener{
	private $enabled = true;
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
	public final function isEnabled(){
		return true; // TODO
	}
	public final function disable(){
		$this->enabled = false;
		$this->onDisable();
	}
	public function onEnable(){}
	public function onDisable(){}
	public abstract function getServer();
	public final function getDynamicHub(){
		return DynamicHub::getSafeInstance($this->getServer());
	}
}