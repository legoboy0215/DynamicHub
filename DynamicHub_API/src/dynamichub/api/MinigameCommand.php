<?php

namespace dynamichub\api;

use pocketmine\command\CommandSender;

interface MinigameCommand{
	/**
	 * @return string
	 */
	public function getName();
	/**
	 * @return Minigame
	 */
	public function getMinigame();
	/**
	 * @param CommandSender $sender
	 * @return bool
	 */
	public function hasPermission(CommandSender $sender);
	/**
	 * @param CommandSender $sender
	 * @param array $args
	 * @return string|bool
	 */
	public function onRun(CommandSender $sender, array $args);
}
