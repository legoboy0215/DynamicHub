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

use DynamicHub\Gamer\Gamer;
use DynamicHub\Module\Match\int;
use DynamicHub\Module\Match\Match;
use DynamicHub\Module\Match\MatchBasedGame;
use DynamicHub\Utils\StaticTranslatable;

class CTFGame extends MatchBasedGame{
	public function __construct(CaptureTheFlag $owner){
		parent::__construct($owner, new StaticTranslatable("CTF"));
	}

	public function onJoin(Gamer $gamer){
		// TODO: Implement onJoin() method.
	}

	public function onQuit(Gamer $gamer){
		// TODO: Implement onQuit() method.
	}

	public function getMinOpenGames() : int{
		// TODO: Implement getMinOpenGames() method.
	}

	public function newMatch(int $matchId) : Match{
		return new CTFMatch($this, $matchId);
	}
}
