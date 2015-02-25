<?php

namespace DynamicHub\Module;

use DynamicHub\DynamicHub;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

abstract class Game extends Module{
	/** @var DynamicHub */
	private $hub;
	/** @var Plugin */
	protected $ctx;
	public function __construct(DynamicHub $hub, Plugin $context){
		$this->hub = $hub;
		$this->ctx = $context;
	}
	/**
	 * @return DynamicHub
	 */
	public function getHub(){
		return $this->hub;
	}
	/**
	 * @return Plugin
	 */
	public function getContext(){
		return $this->ctx;
	}
	public function isAvailableFor(
		/** @noinspection PhpUnusedParameterInspection */
		Player $player
	){
		return true;
	}
}
