<?php

namespace DynamicHub\Module;

use DynamicHub\DynamicHub;
use DynamicHub\Session\Session;

class Hub extends Module{
	/** @var DynamicHub */
	private $hub;
	public function __construct(DynamicHub $hub){
		$this->hub = $hub;
	}
	/**
	 * @return string
	 */
	public function getUniqueName(){
		return "Hub";
	}
	/**
	 * @return \pocketmine\plugin\Plugin
	 */
	public function getContext(){
		return $this->hub;
	}
	public function onDisable(){

	}
	public function onJoin(Session $session){
		// TODO: initialize inventory GUI detection
		// TODO: teleportation
	}
	public function onQuit(Session $session){
		// TODO: finalize inventory GUI detection
	}
}