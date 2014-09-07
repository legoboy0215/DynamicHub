<?php

namespace dynamichub\event;

use dynamichub\DynamicHub;
use pocketmine\event\Event;
use pocketmine\event\Listener;
use pocketmine\plugin\EventExecutor;

class MinigameEventExecutor implements EventExecutor{
	public function execute(Listener $listener, Event $event){
		if(!($listener instanceof DynamicHub)){
			throw new \UnexpectedValueException;
		}
		$listener->handleEvent($event);
	}
}
