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
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use PrideMC\Guardian\Anticheat;
use function abs;

class Killaura extends Anticheat implements Listener {

	public function __construct()
	{
		parent::__construct(Anticheat::KILLAURA);
	}

	// Commonly in Toolbox
	public function killauraV1(Packet $packet, Player $player) : void{
		if($player->getGamemode()->equals(GameMode::CREATIVE())) return;
		if($player->getGamemode()->equals(GameMode::SPECTATOR())) return;
		if(!$packet instanceof DataPacket) return;
		$swing = null;
		if($packet instanceof AnimatePacket){
			if($packet->action === AnimatePacket::ACTION_SWING_ARM){
				$swing = true;
			} else {
				$swing = false; // player detected killaura. :>
			}
		}

		if($packet instanceof InventoryTransactionPacket && $packet->trData->getTypeId() === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY && $packet->trData->getTypeId() === UseItemOnEntityTransactionData::ACTION_ATTACK){
			if(!$swing && $swing !== null){
				$this->fail($player);
			}
		}
	}

	public function processEvent(DataPacketReceiveEvent $event) : void{
		if($event->getOrigin()->getPlayer() !== null){
			$this->killauraV1($event->getPacket(), $event->getOrigin()->getPlayer());
		}
	}

	// check player yaw if their head is actually hitting the player, but might possible to bypass if player has aimbot
	public function killauraV2(EntityDamageByEntityEvent $event) : void{
		$entity = $event->getEntity();
		$damager = $event->getDamager();
		if($damager instanceof Player){
			$alpha = abs($damager->getLocation()->yaw - $entity->getLocation()->yaw) / 2;
			if(!($alpha >= 50 && $alpha <= 140)){
				$event->cancel();
				$this->fail($damager);
			}
		}
	}
}
