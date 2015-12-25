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

namespace DynamicHub\Module\Match;

use DynamicHub\DataProvider\NextIdFetchedCallback;
use DynamicHub\DynamicHub;
use DynamicHub\Module\Game;
use DynamicHub\Utils\CallbackPluginTask;

abstract class MatchBasedGame extends Game implements NextIdFetchedCallback{
	/** @type Match[] */
	private $matches = [];

	public function onLoaded(DynamicHub $hub){
		parent::onLoaded($hub);
		$this->getOwner()->getServer()->getScheduler()->scheduleRepeatingTask(
			new CallbackPluginTask($this->getOwner(), [$this, "halfSecondTick"]), 10);
	}

	/**
	 * @internal
	 */
	public function halfSecondTick(){
		$count = 0;
		foreach($this->matches as $match){
			if($match->getState() === MatchState::OPEN){
				$count++;
			}
		}
		if($count < $this->getMinOpenGames()){
			$this->getHub()->getDataProvider()->fetchNextId($this);
		}

		foreach($this->matches as $match){
			$match->halfSecondTick();
		}
	}

	public function canStartNewMatch(){
		$running = 0;
		foreach($this->matches as $match){
			if($match->getState() !== MatchState::OPEN and $match->getState() !== MatchState::GARBAGE){
				$running++;
			}
		}
		return $running < $this->getMaxRunningGames();
	}

	public abstract function getMinOpenGames() : int;
	public abstract function getMaxRunningGames() : int;

	public function onNextIdFetched(int $nextId){
		$this->matches[$nextId] = $this->newMatch($nextId);
	}

	public abstract function newMatch(int $matchId) : Match;
}
