<?php

namespace dynamichub\event;

use dynamichub\api\Minigame;
use pocketmine\event\Event;

class MinigameHandler{
	/** @var Minigame */
	private $minigame;
	/** @var object */
	private $class;
	/** @var string */
	private $methodName;
	public function __construct(Minigame $minigame, $object, $methodName){
		$this->minigame = $minigame;
		$this->class = $object;
		$this->methodName = $methodName;
	}
	public function getMinigame(){
		return $this->minigame;
	}
	public function callEvent(Event $event){
		call_user_func(array($this->class, $this->methodName), $event);
	}
}
