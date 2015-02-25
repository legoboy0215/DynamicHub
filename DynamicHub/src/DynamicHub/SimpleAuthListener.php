<?php

namespace DynamicHub;

use pocketmine\event\Listener;
use SimpleAuth\event\PlayerAuthenticateEvent;

class SimpleAuthListener implements Listener{
	/** @var DynamicHub */
	private $plugin;
	public function __construct(DynamicHub $plugin){
		$this->plugin = $plugin;
	}
	public function onPlayerAuth(PlayerAuthenticateEvent $event){
		$this->plugin->startSession($event->getPlayer());
	}
}
