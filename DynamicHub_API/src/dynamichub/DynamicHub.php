<?php

namespace dynamichub;

use dynamichub\api\CallbackCommand;
use dynamichub\api\Minigame;
use dynamichub\api\MinigameCommand;
use dynamichub\event\memory\Arr;
use dynamichub\event\MinigameEventExecutor;
use dynamichub\event\MinigameHandler;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\entity\EntityEvent;
use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\inventory\InventoryEvent;
use pocketmine\event\level\LevelEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerEvent;
use pocketmine\event\plugin\PluginDisableEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\inventory\Inventory;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\tile\Tile;
use pocketmine\utils\Config;

class DynamicHub extends PluginBase implements Listener{
	/** @var Config */
	private $cfg;
	/** @var Minigame[] */
	private $games = [];
	/** @var MinigameHandler[][] */
	private $registeredHandles = [];
	/** @var MinigameCommand[][] */
	private $registeredCmds = [];
	private $redirectorCmds = [];
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML, [
		]);
	}
	public function onDisable(){
		foreach($this->games as $i => $game){
			$this->disableGame($i);
		}
	}
	public function register(Minigame $minigame){
		$this->games[$minigame->getName()] = $minigame;
		$minigame->enable();
		$this->getMinigameConfig($minigame);
	}
	public function registerCommand(MinigameCommand $cmd){
		$game = $cmd->getMinigame()->getName();
		$name = trim(strtolower($cmd->getName()));
		if(!isset($this->registeredCmds[$name])){
			$this->registeredCmds[$name] = [];
			$this->redirectorCmds[$name] = new CallbackCommand($this,
				function(CallbackCommand $cmd, array $args, CommandSender $sender){
					if(!($sender instanceof Player)){
						return "Please run this command in-game.";
					}
					$minigame = $this->getMinigameByLevel($sender->getLevel());
					if(!($minigame instanceof Minigame)){
						return "The command is unavailable in your world.";
					}
					$game = $minigame->getName();
					if(!isset($this->registeredCmds[$cmd->getName()]) or
							!isset($this->registeredCmds[$cmd->getName()][$game])){
						return "The command is unavailable in your world.";
					}
					/** @var MinigameCommand $mc */
					$mc = $this->registeredCmds[$cmd->getName()][$game];
					if(!$mc->hasPermission($sender)){
						return "You don't have permission to run this command.";
					}
					return $mc->onRun($sender, $args);
				}, $name, function(CommandSender $sender) use($name){
					if(!($sender instanceof Player)){
						return false;
					}
					$game = $this->getMinigameByLevel($sender->getLevel());
					if(!($game instanceof Minigame)){
						return false;
					}
					$game = $game->getName();
					if(!isset($this->registeredCmds[$name]) or
						!isset($this->registeredCmds[$name][$game])){
						return false;
					}
					/** @var MinigameCommand $cmd */
					$cmd = $this->registeredCmds[$name][$game];
					return $cmd->hasPermission($sender) ? CallbackCommand::RESULT_TRUE:CallbackCommand::RESULT_FALSE;
				});
		}
		$this->registeredCmds[$name][$game] = $cmd;
	}
	public function registerListener(Minigame $minigame, Listener $listener = null){
		if($listener === null){
			$listener = $minigame;
		}
		$class = new \ReflectionClass($listener);
		$methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
		foreach($methods as $method){
			if($method->isStatic()){
				continue;
			}
			$priority = EventPriority::NORMAL;
			$ignoreCancelled = false;
			$docComment = $method->getDocComment();
			if(is_string($docComment)){
				if(preg_match('/^[\t ]*\\* @priority[\t ]+([a-zA-Z]+)$/m', $docComment, $matches)){
					$matches[1] = strtoupper($matches[1]);
					if(defined($path = "pocketmine\\event\\EventPriority::" . $matches[1])){
						$priority = constant("pocketmine\\event\\EventPriority::" . $matches[1]);
					}
				}
				if(preg_match('/^[\t ](\\* )?@ignoreCancelled[\t ]+([a-zA-Z]+)$/m', $docComment, $matches)){
					$matches[1] = strtolower($matches[1]);
					if($matches[1] === "false"){
						$ignoreCancelled = false;
					}elseif($matches[1] === "true"){
						$ignoreCancelled = true;
					}
				}
			}
			$params = $method->getParameters();
			if(count($params) === 1){
				$param = $params[0];
				$paramClass = @$param->getClass();
				if($paramClass instanceof \ReflectionClass){
					if($paramClass->isSubclassOf("pocketmine\\event\\Event") and !$paramClass->isAbstract()){
						$this->registeredHandles[$paramClass->getName()][$minigame->getName()] = new MinigameHandler($minigame, $listener, $method->getName());
						$this->getServer()->getPluginManager()->registerEvent($paramClass->getName(), $listener, $priority, new MinigameEventExecutor(), $this, $ignoreCancelled);
					}
				}
			}
		}
	}
	public function handleEvent(Event $event){
		if(!isset($this->registeredHandles[get_class($event)])){
			return;
		}
		/** @var string|bool $world */
		$world = true;
		/** @var event\MinigameHandler[] $handles */
		$handles = $this->registeredHandles[get_class($event)];
		if($event instanceof BlockEvent){
			$world = $event->getBlock()->getLevel()->getName();
		}
		if($event instanceof DataPacketReceiveEvent){
			$world = $event->getPlayer()->getLevel()->getName();
		}
		if($event instanceof DataPacketSendEvent){
			$world = $event->getPlayer()->getLevel()->getName();
		}
		if($event instanceof EntityEvent){
			$world = $event->getEntity()->getLevel()->getName();
		}
		if($event instanceof InventoryEvent){
			$holder = $event->getInventory()->getHolder();
			find_world_by_holder:
			if($holder instanceof Entity){
				$world = $holder->getLevel()->getName();
			}
			elseif($holder instanceof Tile){
				$world = $holder->getLevel()->getName();
			}
			elseif($holder instanceof Inventory){
				$holder = $holder->getHolder();
				goto find_world_by_holder;
			}
		}
		if($event instanceof LevelEvent){
			$world = $event->getLevel()->getName();
		}
		if($event instanceof PlayerEvent){
			$world = $event->getPlayer()->getLevel()->getName();
		}
		foreach($handles as $handle){
			if($handle->getMinigame()->ownsWorld($world)){
				$handle->callEvent($event);
				break;
			}
		}
	}
	public function getMinigameByLevel(Level $level){
		foreach($this->games as $game){
			if($game->ownsWorld($level->getName())){
				return $game;
			}
		}
		return null;
	}
	public function onPluginDisabled(PluginDisableEvent $event){
		foreach($this->games as $i => $game){
			if($game->getOwningPlugin() === $event->getPlugin()){
				$this->disableGame($i);
			}
		}
	}
	public function disableGame($id){
		if(!isset($this->games[$id])){
			return;
		}
		$this->games[$id]->disable();
		unset($this->games[$id]);
	}
	/**
	 * @param Server $server
	 * @return self
	 */
	public static function getSafeInstance(Server $server){ // thread-safe if $server is thread-safe
		return $server->getPluginManager()->getPlugin("DynamicHub");
	}
	public static function registerMinigame(Server $server, Minigame $minigame){
		self::getSafeInstance($server)->register($minigame);
	}
	public function getMinigameConfig(Minigame $minigame){
		$name = $minigame->getName();
		if(!$this->cfg->exists($name)){
			$this->cfg->set($name, $minigame->getDefaultConfig());
		}
		$listener = new CallbackArrUpdateListener(function(Arr $arr) use($name){
			$this->cfg->set($name, $arr->getAll());
			$this->cfg->save();
		});
		return new Arr($listener, $this->cfg->get($name));
	}

	/**
	 * @deprecated
	 * @throws \BadMethodCallException
	 */
	public function saveConfig(){
		throw new \BadMethodCallException("DynamicHub::saveConfig() is unsupported.");
	}
	/**
	 * @deprecated
	 * @throws \BadMethodCallException
	 */
	public function saveDefaultConfig(){
		throw new \BadMethodCallException("DynamicHub::saveDefaultConfig() is unsupported.");
	}
	/**
	 * @deprecated
	 * @throws \BadMethodCallException
	 */
	public function reloadConfig(){
		throw new \BadMethodCallException("DynamicHub::reloadConfig() is unsupported.");
	}
	/**
	 * @deprecated
	 * @throws \BadMethodCallException
	 */
	public function getConfig(){
		throw new \BadMethodCallException("DynamicHub::getConfig() is unsupported.");
	}
}
