<?php

/*
 * DynamicHub
 *
 * Copyright (C) 2015 LegendsOfMCPE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author LegendsOfMCPE
 */

namespace DynamicHub;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

class StandardListener implements Listener{
	private $hub;

	public function __construct(DynamicHub $hub){
		$this->hub = $hub;
		$hub->getServer()->getPluginManager()->registerEvents($this, $hub);
	}

	public function onQuit(PlayerQuitEvent $event){
		$this->hub->onPlayerQuit($event->getPlayer());
	}
}
