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

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\player\Player;
use PrideMC\Guardian\Anticheat;
use function mb_strlen;

class BadPackets extends Anticheat implements Listener{

	public function __construct(){
		parent::__construct(Anticheat::BADPACKET);
	}

	public array $packetsPerSecond = [];

	public const MAX_PACKETS = 650;

	public const MESSAGE_LIMIT = 500;

	// limit the packet recieve.
	public function badPacketV1(DataPacketReceiveEvent $event) : void{
		$player = $event->getOrigin()->getPlayer();
		$packet = $event->getPacket();

		if (!($player instanceof Player)) {
			return;
		}

		if (!(isset($this->packetsPerSecond[$player->getUniqueId()->getBytes()]))) {
			$this->packetsPerSecond[$player->getUniqueId()->getBytes()] = 0;
		}

		if($this->packetsPerSecond[$player->getUniqueId()->getBytes()] > BadPackets::MAX_PACKETS){
			$this->kick($player, $this->typeToReasonString($this->getFlagId()));
		} else {
			$this->packetsPerSecond[$player->getUniqueId()->getBytes()]++;
		}
	}

	// some people bypass message limit, so to prevent message vulnerabilities, we check this.
	public function badPacketV2(DataPacketReceiveEvent $event) : void{
		$player = $event->getOrigin()->getPlayer();
		$packet = $event->getPacket();

		if (!($player instanceof Player)) {
			return;
		}

		if ($packet instanceof TextPacket) {
			if (mb_strlen($packet->message) > BadPackets::MESSAGE_LIMIT) {
				if ($player->getServer()->isOp($player->getName())) {
					return;
				}
				$this->fail($player);
			}
		}
	}
}
