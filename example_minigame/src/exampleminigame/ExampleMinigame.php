<?php

namespace exampleminigame;

use dynamichub\api\Minigame;
use dynamichub\DynamicHub;
use pocketmine\Player;

class ExampleMinigame extends Minigame{
	/*
	 * We are using a constant so that errors ed by typos can be detected by the PHP engine
	 * (undefined constant ExampleMinigame::WOELD_NAME for example) instead of causing unexpected bugs
	 * such as the world "example_woeld" instead of "example_world" is trying to be loaded but failed.
	 * This is good coding practice.
	 */
	const WORLD_NAME = "example_world";
	const MAX_CAPACITY = 20;
	private $loader;
	private $hub;
	public function __construct(Loader $loader, DynamicHub $instance){
		$this->loader = $loader;
		$this->hub = $instance;
	}
	public function getServer(){
		return $this->loader->getServer();
	}
	public function getDynamicHub(){
		return $this->hub;
	}
	public function getName(){
		return "ExampleMinigame";
	}
	public function getOwnedWorldNames(){
		return [self::WORLD_NAME];
	}
	public function onEnable(){
		$this->getServer()->loadLevel(self::WORLD_NAME);
		$this->getDynamicHub()->registerListener($this);
	}
	public function onDisable(){
		$level = $this->getServer()->getLevelByName(self::WORLD_NAME);
		$this->getServer()->unloadLevel($level);
	}
	public function getPlugin(){
		return $this->hub;
	}

	public function onPlayerJoin(Player $player){
		return true;
	}
}
