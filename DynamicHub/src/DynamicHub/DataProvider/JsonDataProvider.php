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

namespace DynamicHub\DataProvider;

use DynamicHub\DynamicHub;
use DynamicHub\Gamer\GamerData;

class JsonDataProvider implements DataProvider{
	private $hub;

	public function __construct(DynamicHub $hub){
		$this->hub = $hub;
	}

	public function getData(string $name) : GamerData{
		$dir = $this->hub->getDataFolder() . "players/";
		$name = strtolower($name);
		$file = $dir . $name . ".json";
		if(!is_file($file)){
			return GamerData::defaultInstance($this->hub, $name);
		}else{
			$input = json_decode(file_get_contents($file), true);
			$data = new GamerData($name);
			foreach($input as $k => $v){
				$data->{$k} = $v;
			}
			return $data;
		}
	}

	public function saveData(GamerData $data){
		$dir = $this->hub->getDataFolder() . "players/";
		if(!is_dir($dir)){
			mkdir($dir, 0777, true);
		}
		$file = $dir . $data->username . ".json";
		file_put_contents($file, json_encode($data,
			JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_SLASHES));
	}
}
