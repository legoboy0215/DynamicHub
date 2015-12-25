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

chdir(__DIR__);

$info = json_decode(file_get_contents("compile/info.json"));
$NAME = $info->name;

$CLASS = "Dev";
$opts = getopt("", ["rc", "beta"]);
if(isset($opts["beta"])){
	$CLASS = "Beta";
}elseif(isset($opts["rc"])){
	$CLASS = "RC";
}
$file = "compile/" . $NAME . "_" . $CLASS . ".phar";

