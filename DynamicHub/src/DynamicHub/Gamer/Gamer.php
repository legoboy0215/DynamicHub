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

namespace DynamicHub\Gamer;

use DynamicHub\DynamicHub;
use DynamicHub\Module\Module;
use pocketmine\Player;

class Gamer{
	private $hub;
	private $player;
	/** @type Module|null */
	private $module = null;
	/** @type GamerData */
	private $data = null;

	/** @type GamerStatus */
	private $status = null;

	public function __construct(DynamicHub $hub, Player $player){
		$this->hub = $hub;
		$this->player = $player;
		$player->blocked = true;
		// TODO load data
		$player->sendMessage("Loading account data for you. Please wait..."); // TODO translate
	}

	public function onDataLoaded(GamerData $data){
		$this->data = $data;
		$this->player->sendMessage("Your account has been loaded."); // TODO translate
		$this->player->blocked = false;
		$lastModule = $this->hub->getModule($data->lastModule);
		if($lastModule === null){
			$lastModule = $this->hub->getHubModule();
		}
		$this->module = $lastModule;
		$lastModule->onJoin($this);
	}

	public function setModule(Module $module) : bool{
		if($this->module === null){
			return false;
		}
		$this->module->quit($this);
		$this->module = $module;
		$module->join($this);
		return true;
	}

	public function onQuit(){
		if($this->module !== null){
			$this->module->quit($this);
			$this->saveData();
		}
	}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function getModule(){
		return $this->module;
	}

	public function getData() : GamerData{
		return $this->data;
	}

	public function saveData(){
	}

	public function getId() : int{
		return $this->player->getId();
	}

	public function halfSecondTick(){
		$this->getPlayer()->sendTip($this->status->toString());
	}
}
