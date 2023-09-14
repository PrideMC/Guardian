<?php

/*
 *
 *       _____      _     _      __  __  _____
 *      |  __ \    (_)   | |    |  \/  |/ ____|
 *      | |__) | __ _  __| | ___| \  / | |
 *      |  ___/ '__| |/ _` |/ _ \ |\/| | |
 *      | |   | |  | | (_| |  __/ |  | | |____
 *      |_|   |_|  |_|\__,_|\___|_|  |_|\_____|
 *            A minecraft bedrock server.
 *
 *      This project and it’s contents within
 *     are copyrighted and trademarked property
 *   of PrideMC Network. No part of this project or
 *    artwork may be reproduced by any means or in
 *   any form whatsoever without written permission.
 *
 *  Copyright © PrideMC Network - All Rights Reserved
 *                     Season #5
 *
 *  www.mcpride.tk                 github.com/PrideMC
 *  twitter.com/PrideMC         youtube.com/c/PrideMC
 *  discord.gg/PrideMC           facebook.com/PrideMC
 *               bit.ly/JoinInPrideMC
 *  #PrideGames                           #PrideMonth
 *
 */

declare(strict_types=1);

namespace PrideMC\Guardian\Modules;

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\GameMode;
use PrideMC\Guardian\Anticheat;

use function ceil;
use function floor;
use function microtime;

class Instabreak extends Anticheat implements Listener
{
	/** @var float[] */
	private $breakTimes = [];

	public function __construct()
	{
		parent::__construct(Anticheat::INSTABREAK);
	}

	public function onPlayerInteract(PlayerInteractEvent $event) : void
	{
		if($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
			$this->breakTimes[$event->getPlayer()->getUniqueId()->getBytes()] = floor(microtime(true) * 20);
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) : void
	{
		if(!$event->getInstaBreak()) {
			$player = $event->getPlayer();

			if($player->getGamemode()->equals(GameMode::SPECTATOR())) {
				return;
			}

			if(!isset($this->breakTimes[$uuid = $player->getUniqueId()->getBytes()])) {
				$this->fail($player);
				$event->cancel();
				return;
			}

			$target = $event->getBlock();
			$item = $event->getItem();

			$expectedTime = ceil($target->getBreakInfo()->getBreakTime($item) * 20);

			if(($haste = $player->getEffects()->get(VanillaEffects::HASTE())) !== null) {
				$expectedTime *= 1 - (0.2 * $haste->getEffectLevel());
			}

			if(($miningFatigue = $player->getEffects()->get(VanillaEffects::MINING_FATIGUE())) !== null) {
				$expectedTime *= 1 + (0.3 * $miningFatigue->getEffectLevel());
			}

			$expectedTime -= 1; //1 tick compensation

			$actualTime = ceil(microtime(true) * 20) - $this->breakTimes[$uuid];

			if($actualTime < $expectedTime) {
				$this->fail($player);
				$event->cancel();
				return;
			}

			unset($this->breakTimes[$uuid]);
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void
	{
		unset($this->breakTimes[$event->getPlayer()->getUniqueId()->getBytes()]);
	}
}
