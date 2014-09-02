<?php

namespace dynamichub\api;

use dynamichub\DynamicHub;
use pocketmine\event\Listener;

abstract class Minigame implements Listener{
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
	public function onEnable(){}
	public function onDisable(){}
	public abstract function getServer();
	public function getDynamicHub(){
		return DynamicHub::getSafeInstance($this->getServer());
	}
}
