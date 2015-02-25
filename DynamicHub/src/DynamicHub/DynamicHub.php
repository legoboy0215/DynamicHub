<?php

namespace DynamicHub;

use DynamicHub\Module\Game;
use DynamicHub\Module\Hub;
use DynamicHub\Session\SessionManager;
use DynamicHub\Utils\Configuration;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\plugin\PluginDisableEvent;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;

class DynamicHub extends PluginBase implements Listener{
	/** @var string[] */
	private static $register;
	/** @var Game[] */
	private $games = [];
	/** @var Configuration */
	private $cfg;
	/** @var SessionManager */
	private $sessionMgr;
	/** @var Hub */
	private $hub;
	public function onLoad(){
		self::$register = [];
		$this->getLogger()->info("DynamicHub is now accepting game registration.");
	}
	public function onEnable(){
		$this->saveDefaultConfig();
		foreach(self::$register as $class => $contextName){
			$context = $this->getServer()->getPluginManager()->getPlugin($contextName);
			if(!($context instanceof Plugin)){
				continue;
			}
			$class = new \ReflectionClass($class);
			/** @var Game $game */
			$game = $class->newInstance($this, $context);
			$this->games[$game->getUniqueName()] = $game;
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->cfg = new Configuration($this);
		if($this->cfg->sessionInit === "auth"){
			if(!($this->getServer()->getPluginManager()->getPlugin("SimpleAuth") instanceof Plugin)){
				$this->getLogger()->warning("SimpleAuth not laoded! sessionInit config property will be changed to \"join\".");
				$this->cfg->sessionInit = "join";
			}
			else{
				$this->getServer()->getPluginManager()->registerEvents(new SimpleAuthListener($this), $this);
			}
		}
		elseif($this->cfg->sessionInit !== "join"){
			throw new \RuntimeException("Unknown sessionInit value: \"{$this->cfg->sessionInit}\"");
		}
		$this->sessionMgr = new SessionManager($this);
		$this->hub = new Hub($this);
		foreach($this->getServer()->getOnlinePlayers() as $player){
			$this->startSession($player);
		}
	}
	public function onDisable(){
		foreach($this->games as $game){
			$game->onDisable();
		}
		foreach($this->getServer()->getOnlinePlayers() as $player){
			$this->closeSession($player);
		}
	}

	public function startSession(Player $player){
		$this->sessionMgr->startSession($player);
	}
	public function closeSession(Player $player){
		$this->sessionMgr->endSession($player);
	}
	/**
	 * Returns the corresponding {@link \DynamicHub\Session\Session Session} object for $player
	 * @param Player $player
	 * @return Session\Session|null
	 */
	public function getSession(Player $player){
		return $this->sessionMgr->getSession($player);
	}

	public function getGame($name){
		return isset($this->games[$name]) ? $this->games[$name]:null;
	}
	public function getGames(){
		return $this->games;
	}
	public function getHub(){
		return $this->hub;
	}

	public function onJoin(PlayerJoinEvent $event){
		if($this->cfg->sessionInit === "join"){
			$this->startSession($event->getPlayer());
		}
	}
	public function onQuit(PlayerQuitEvent $event){
		$this->closeSession($event->getPlayer());
	}
	public function onPluginDisabled(PluginDisableEvent $event){
		if($event->getPlugin() === $this){
			return;
		}
		foreach($this->games as $k => $game){
			if($game->getContext() === $event->getPlugin()){
				$game->onDisable();
				unset($this->games[$k]);
			}
		}
	}

	/**
	 * @param string $className
	 * @param Plugin $context
	 * @param bool   $overwrite
	 * @return bool whether the game is already registered
	 */
	public static function registerGame($className, Plugin $context, $overwrite = false){
		if(isset(self::$register[$className]) and !$overwrite){
			return false;
		}
		try{
			class_exists($className, true); // attempt to load it with the autoloader
			$class = new \ReflectionClass($className);
			if(!$class->isSubclassOf(Game::class)){
				throw new \Exception("Class '$className' passed into DynamicHub::registerGame() must extend DynamicHub\\Game");
			}
			/** @var \ReflectionMethod $constructor */
			$constructor = $class->getConstructor();
			$cnt = $constructor->getNumberOfRequiredParameters();
			if($cnt > 2){
				throw new \Exception("Attempt to register game '$className' with too many required parameters");
			}
			$params = $constructor->getParameters();
			if(isset($params[0])){
				$class = $params[0]->getClass();
				if($class instanceof \ReflectionClass and !is_subclass_of(DynamicHub::class, $class->getName())){
					throw new \Exception("Incorrect argument 1 for $className::__construct()");
				}
				if(isset($params[1])){
					$class = $params[1]->getClass();
					if($class instanceof \ReflectionClass and !is_subclass_of(Plugin::class, $class->getName())){
						throw new \Exception("Incorrect argument 2 for $className::__construct()");
					}
				}
			}
		}
		catch(\Exception $e){
			if($e instanceof \RuntimeException){
				throw new \RuntimeException("Class '$className' passed into DynamicHub::registerGame() doesn't exist");
			}
			throw new \RuntimeException($e->getMessage());
		}
		self::$register[$className] = $context->getName();
		return true;
	}
}
