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

namespace DynamicHub\Module\Event;

use DynamicHub\Module\Game;
use pocketmine\event\Event;
use pocketmine\event\Listener;

class RegisteredGameEventHandler implements Listener{
	private $game;
	private $listener;
	private $method;

	public function __construct(Game $game, Listener $listener, string $method){
		$this->game = $game;
		$this->listener = $listener;
		$this->method = $method;
	}

	public function execute(Event $event){
		$this->listener->{$this->method}($event);
	}

	public function getGame(){
		return $this->game;
	}

	public function getListener(){
		return $this->listener;
	}

	public function getMethod(){
		return $this->method;
	}
}
