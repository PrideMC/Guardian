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

use pocketmine\block\BlockTypeIds;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\UpdateAdventureSettingsPacket;
use pocketmine\player\Player;
use PrideMC\Guardian\Anticheat;

use function in_array;
use function intval;

class Flight extends Anticheat implements Listener
{
	public function __construct()
	{
		parent::__construct(Anticheat::FLIGHT);
	}

	public function flightV1(PlayerMoveEvent $event)
	{
		$player = $event->getPlayer();
		if($player->getServer()->isOp($player->getName())) {
			return;
		}
		$beneath = $event->getPlayer()->getWorld()->getBlock($event->getPlayer()->getPosition()->floor()->subtract(0, 1, 0));
		if($beneath->getTypeId() === BlockTypeIds::AIR) {
			$event->cancel();
			$this->fail($player);
		}
	}

	// for toolbox: aka CreativeFly
	public function flightV2(PlayerToggleFlightEvent $event) : void
	{
		$player = $event->getPlayer();

		if($player->getServer()->isOp($player->getName())) {
			return;
		}
		$event->cancel();
		$this->fail($player);
	}

	// for toolbox, if toggle flight event isn't working, to prevent bypassing.
	public function flightV3(PlayerMoveEvent $event) : void
	{
		$player = $event->getPlayer();
		if($player->getServer()->isOp($player->getName())) {
			return;
		}
		if(!$player->getAllowFlight()) {
			if($player->isFlying()) {
				$event->cancel();
				$this->fail($player);
			}
		}
	}

	// for most advance clients, manipulating fly packets
	public function flightV4(DataPacketReceiveEvent $event)
	{
		$player = $event->getOrigin()->getPlayer();
		$packet = $event->getPacket();

		if($player === null) {
			return;
		}
		if($player->getServer()->isOp($player->getName())) {
			return;
		}
		if($packet instanceof UpdateAdventureSettingsPacket) {
			if(!$player->isCreative() && !$player->isSpectator() && !$player->getAllowFlight()) {
				switch ($packet->flags) {
					case 614:
					case 615:
					case 103:
					case 102:
					case 38:
					case 39:
						$event->cancel();
						$this->fail($player);
						break;
				}
				if((($packet->flags >> 9) & 0x01 === 1) || (($packet->flags >> 7) & 0x01 === 1) || (($packet->flags >> 6) & 0x01 === 1)) {
					$this->fail($player);
					$event->cancel();
				}
			}
		}
	}

	// advance position checking: we make sure the player blocks surroundings are calculated.
	public function flightV5(PlayerMoveEvent $event) : void
	{
		$player = $event->getPlayer();
		$oldPos = $event->getFrom();
		$newPos = $event->getTo();
		$surroundingBlocks = $this->GetSurroundingBlocks($player);
		if(!$player->isCreative() && !$player->isSpectator() && !$player->getAllowFlight()) {
			if ($oldPos->getY() <= $newPos->getY()) {
				if($player->getInAirTicks() > 40) {
					$maxY = $player->getWorld()->getHighestBlockAt(intval($newPos->getX()), intval($newPos->getZ()));
					if($newPos->getY() - 2 > $maxY) {
						if(
							!in_array(BlockTypeIds::OAK_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::COBBLESTONE_WALL, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::ACACIA_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::OAK_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::BIRCH_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::DARK_OAK_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::JUNGLE_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::NETHER_BRICK_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::SPRUCE_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::WARPED_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::MANGROVE_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::CRIMSON_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::CHERRY_FENCE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::ACACIA_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::OAK_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::BIRCH_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::DARK_OAK_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::JUNGLE_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::SPRUCE_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::WARPED_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::MANGROVE_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::CRIMSON_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::CHERRY_FENCE_GATE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::GLASS_PANE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::HARDENED_GLASS_PANE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::STAINED_GLASS_PANE, $surroundingBlocks, true)
							|| !in_array(BlockTypeIds::STAINED_HARDENED_GLASS_PANE, $surroundingBlocks, true)
						) {
							$event->cancel();
							$this->fail($player);
						}
					}
				}
			}
		}
	}

	public function GetSurroundingBlocks(Player $player) : array
	{
		$world = $player->getWorld();

		$posX = $player->getLocation()->getX();
		$posY = $player->getLocation()->getY();
		$posZ = $player->getLocation()->getZ();

		$pos1 = new Vector3($posX, $posY, $posZ);
		$pos2 = new Vector3($posX - 1, $posY, $posZ);
		$pos3 = new Vector3($posX - 1, $posY, $posZ - 1);
		$pos4 = new Vector3($posX, $posY, $posZ - 1);
		$pos5 = new Vector3($posX + 1, $posY, $posZ);
		$pos6 = new Vector3($posX + 1, $posY, $posZ + 1);
		$pos7 = new Vector3($posX, $posY, $posZ + 1);
		$pos8 = new Vector3($posX + 1, $posY, $posZ - 1);
		$pos9 = new Vector3($posX - 1, $posY, $posZ + 1);

		$bpos1 = $world->getBlock($pos1)->getTypeId();
		$bpos2 = $world->getBlock($pos2)->getTypeId();
		$bpos3 = $world->getBlock($pos3)->getTypeId();
		$bpos4 = $world->getBlock($pos4)->getTypeId();
		$bpos5 = $world->getBlock($pos5)->getTypeId();
		$bpos6 = $world->getBlock($pos6)->getTypeId();
		$bpos7 = $world->getBlock($pos7)->getTypeId();
		$bpos8 = $world->getBlock($pos8)->getTypeId();
		$bpos9 = $world->getBlock($pos9)->getTypeId();

		return  [$bpos1, $bpos2, $bpos3, $bpos4, $bpos5, $bpos6, $bpos7, $bpos8, $bpos9];
	}
}
