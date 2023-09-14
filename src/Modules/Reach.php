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

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use PrideMC\Guardian\Anticheat;

class Reach extends Anticheat implements Listener
{
	public const MAX_PLAYER_REACH = 8.1;
	public const MAX_PLAYER_REACH_V2 = 4.0;

	private const MAX_REACH_DISTANCE_CREATIVE_V3 = 13;
	private const MAX_REACH_DISTANCE_SURVIVAL_V3 = 7;
	private const MAX_REACH_DISTANCE_ENTITY_INTERACTION_V3 = 8;

	public function __construct()
	{
		parent::__construct(Anticheat::REACH);
	}

	public function reachV1(EntityDamageByEntityEvent $event) : void
	{
		if(($player = $event->getEntity()) instanceof Player && ($damager = $event->getDamager()) instanceof Player) {
			if($damager->getGamemode()->equals(GameMode::CREATIVE())) {
				return;
			}
			if($damager->getGamemode()->equals(GameMode::SPECTATOR())) {
				return;
			}
			if($player->getLocation()->distance($damager->getLocation()) > Reach::MAX_PLAYER_REACH) {
				$this->fail($damager);
			}
		}
	}

	// V2 - just check again...
	public function reachV2(EntityDamageEvent $event)
	{
		if($event instanceof EntityDamageByEntityEvent && $event->getEntity() instanceof Player && $event->getDamager() instanceof Player) {
			if($event->getDamager()->getGamemode()->equals(GameMode::CREATIVE())) {
				return;
			}
			if($event->getDamager()->getGamemode()->equals(GameMode::SPECTATOR())) {
				return;
			}
			if($event->getEntity()->getLocation()->distanceSquared($event->getDamager()->getLocation()) > Reach::MAX_PLAYER_REACH_V2) {
				$this->fail($event->getDamager());
			}
		}
	}

	// V3 - just checking again
	public function reachV3(EntityDamageEvent $event)
	{
		if($event instanceof EntityDamageByEntityEvent && $event->getEntity() instanceof Player && $event->getDamager() instanceof Player) {
			if(!$event->getDamager()->canInteract($event->getEntity()->getLocation()->add(0.5, 0.5, 0.5), $event->getEntity()->isCreative() ? self::MAX_REACH_DISTANCE_CREATIVE_V3 : self::MAX_REACH_DISTANCE_SURVIVAL_V3)) {
				$this->fail($event->getDamager());
			}
			if(!$event->getDamager()->canInteract($event->getEntity()->getLocation(), self::MAX_REACH_DISTANCE_ENTITY_INTERACTION_V3)) {
				$this->fail($event->getDamager());
				$event->cancel();
			}
		}
	}

	public function reachBlockV1(PlayerInteractEvent $event) : void
	{
		if(!$event->getPlayer()->canInteract($event->getBlock()->getPosition()->add(0.5, 0.5, 0.5), $event->getPlayer()->isCreative() ? self::MAX_REACH_DISTANCE_CREATIVE_V3 : self::MAX_REACH_DISTANCE_SURVIVAL_V3)) {
			$this->fail($event->getPlayer());
		}
	}
}
