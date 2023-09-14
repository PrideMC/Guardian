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
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use PrideMC\Guardian\Anticheat;

use function assert;
use function microtime;

class NoPacket extends Anticheat implements Listener
{
	public function __construct()
	{
		parent::__construct(Anticheat::NOPACKET);
	}

	private array $elapse = [];

	public function handleEvent(DataPacketReceiveEvent $event) : void
	{
		if($this->handlePacket($event->getPacket(), $event->getOrigin())) {
			$event->cancel();
		}
	}

	public function handlePacket(Packet|DataPacket $packet, NetworkSession $session) : bool
	{
		assert($packet instanceof PlayerAuthInputPacket);
		if(($player = $session->getPlayer()) === null) {
			return false;
		}

		$this->elapse[$player->getUniqueId()->__toString()] = microtime(true);
		$last = $this->elapse[$player->getUniqueId()->__toString()];
		$elapse = microtime(true) - $last;

		if($elapse > 3) {
			$this->fail($player);
			return true;
		}

		return false;
	}
}
