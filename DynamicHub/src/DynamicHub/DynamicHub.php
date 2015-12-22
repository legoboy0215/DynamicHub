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

use DynamicHub\Config\JoinMethod\JoinListener;
use DynamicHub\Config\JoinMethod\JoinMethod;
use DynamicHub\Gamer\Gamer;
use DynamicHub\Module\Event\EventListener;
use DynamicHub\Module\Event\RegisteredGameEventHandler;
use DynamicHub\Module\Game;
use DynamicHub\Module\HubModule;
use DynamicHub\Module\Module;
use DynamicHub\Utils\CallbackPluginTask;
use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class DynamicHub extends PluginBase{
	private static $NAME = "DynamicHub";

	/** @type Game[] */
	private $loadedGames = [];
	/** @type HubModule */
	private $hubModule;
	private $gamers = [];
	/** @type EventListener[] */
	private $listeners = [];

	/** @type bool */
	private $single;
	/** @type JoinMethod[] */
	private $joinMethods = [];

	public function onLoad(){
		self::$NAME = $this->getDescription()->getName();
	}

	public function onEnable(){
		$this->saveDefaultConfig();
		$this->single = $this->getConfig()->get("single", false);
		if($this->single){
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackPluginTask($this, function(){
				if(count($this->loadedGames) === 0){
					$this->getLogger()->critical("No games loaded");
					$this->getServer()->getPluginManager()->disablePlugin($this);
				}
			}), 1);
		}
		foreach($this->joinMethods as $method){
			$m = JoinMethod::get($this, $method);
			if($m !== null){
				$this->joinMethods[] = $m;
			}
		}
		new JoinListener($this);
		$this->gameEventListener = new GameEventListener($this);
	}

	/**
	 * Load a {@link Game} into the plugin
	 *
	 * @param Game $game
	 *
	 * @throws \RuntimeException
	 */
	public function loadGame(Game $game){
		$owner = $game->getOwner();
		if(!in_array($this->getDescription()->getName(), $owner->getDescription()->getDepend())){
			throw new \RuntimeException("A Game must be owned by a plugin that depends on DynamicHub");
		}
		if($this->single and count($this->loadedGames) > 0){
			throw new \RuntimeException("Only one Game can be loaded on this server limited in config");
		}
		$this->loadedGames[strtolower($game->getName()->get())] = $game;
		$game->onLoaded($this);
	}

	public function unloadGame(Game $game){
		// TODO quit players
		// TODO unregister listeners
	}

	public function registerListeners(Game $game, Listener $listener){
		foreach((new \ReflectionClass($listener))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method){
			if($method->isStatic()){
				continue;
			}
			$parameters = $method->getParameters();
			if(count($parameters) !== 1){
				continue;
			}
			if(!($parameters[0]->getClass() instanceof \ReflectionClass)){
				continue;
			}
			if(!$parameters[0]->getClass()->isSubclassOf(Event::class)){
				continue;
			}
			$priority = EventPriority::NORMAL;
			$ignoreCancelled = false;
			if(preg_match("/^[\t ]*\\* @priority[\t ]{1,}([a-zA-Z]{1,})/m", (string) $method->getDocComment(), $matches) > 0){
				$matches[1] = strtoupper($matches[1]);
				if(defined(EventPriority::class . "::" . $matches[1])){
					$priority = constant(EventPriority::class . "::" . $matches[1]);
				}
			}
			if(preg_match("/^[\t ]*\\* @ignoreCancelled[\t ]{1,}([a-zA-Z]{1,})/m", (string) $method->getDocComment(), $matches) > 0){
				$matches[1] = strtolower($matches[1]);
				if($matches[1] === "false"){
					$ignoreCancelled = false;
				}elseif($matches[1] === "true"){
					$ignoreCancelled = true;
				}
			}
			$event = $parameters[0]->getClass()->getName();
			$reflection = new \ReflectionClass($event);
			if(strpos((string) $reflection->getDocComment(), "@deprecated") !== false and $this->getServer()->getProperty("settings.deprecated-verbose", true)){
				$this->getLogger()->warning($this->getServer()->getLanguage()->translateString("pocketmine.plugin.deprecatedEvent", [
					$game->getOwner()->getName(),
					$event,
					get_class($listener) . "->" . $method->getName() . "()",
				]));
			}
			if(!isset($this->listeners[$identifier = EventListener::identifier($event, $priority, $ignoreCancelled)])){
				$this->listeners[$identifier] = new EventListener($this, $event, $priority, $ignoreCancelled);
			}
			$this->listeners[$identifier]->addHandler(new RegisteredGameEventHandler($game, $listener, $method->getName()));
		}
	}

	public function onPlayerAuth(Player $player){
		$this->gamers[$player->getId()] = new Gamer($this, $player);
	}

	public function isSingle() : bool{
		return $this->single;
	}

	/**
	 * @return JoinMethod[]
	 */
	public function getJoinMethods() : array{
		return $this->joinMethods;
	}

	/**
	 * @param string $name
	 *
	 * @return Module|null
	 */
	public function getModule(string $name){
		$lower = strtolower($name);
		if($lower === strtolower(HubModule::NAME)){
			return $this->hubModule;
		}
		return $this->loadedGames[$lower] ?? null;
	}

	public function getHubModule() : HubModule{
		return $this->hubModule;
	}

	/**
	 * @param Server $server
	 *
	 * @return DynamicHub|null
	 */
	public static function getInstance(Server $server){
		$me = $server->getPluginManager()->getPlugin(self::$NAME);
		if($me instanceof DynamicHub and $me->isEnabled()){
			return $me;
		}
		return null;
	}
}
