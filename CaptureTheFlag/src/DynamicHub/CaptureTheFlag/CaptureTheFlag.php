<?php

/*
 * CaptureTheFlag
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

namespace DynamicHub\CaptureTheFlag;

use DynamicHub\DynamicHub;
use pocketmine\plugin\PluginBase;

class CaptureTheFlag extends PluginBase{
	/** @type CTFGame */
	private $game;

	public function onEnable(){
		$hub = DynamicHub::getInstance($this->getServer());
		$hub->loadGame($this->game = new CTFGame($this));
	}
}
