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

namespace DynamicHub\Config\JoinMethod;

use DynamicHub\DynamicHub;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;

class JoinGameCommand extends Command implements PluginIdentifiableCommand{
	private $hub;

	public function __construct(DynamicHub $hub, string $name, $gameName, array $aliases){
		parent::__construct($name, "Join $gameName", "/$name", $aliases);
		$this->hub = $hub;
		$hub->getServer()->getCommandMap()->register("join", $this);
	}

	public function execute(CommandSender $sender, $commandLabel, array $args){
		// TODO
	}

	public function getPlugin(){
		return $this->hub;
	}
}
