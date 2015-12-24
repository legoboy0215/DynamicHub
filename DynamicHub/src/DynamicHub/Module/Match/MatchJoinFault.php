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

class MatchJoinFault{
	const FULL = 0;
	const SEMI_FULL = 1;
	const CLOSED = 2;
	const NO_PERM = 3;
	const NOT_IN_GAME = 4;

	const SUCCESS = -1;
}
