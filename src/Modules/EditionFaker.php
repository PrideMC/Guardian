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
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\utils\TextFormat as TF;
use PrideMC\Guardian\Anticheat;
use PrideMC\Guardian\Loader;
use function explode;
use function in_array;
use function strtoupper;

class EditionFaker extends Anticheat implements Listener{

	public const IP_LIMIT = 3;

	public const NULL_MODELS = [
		DeviceOS::ANDROID,
		DeviceOS::OSX,
		DeviceOS::WINDOWS_10,
		DeviceOS::WIN32,
		DeviceOS::DEDICATED,
	];

	public const DEVICE_OS_LIST = [
		DeviceOS::ANDROID,
		DeviceOS::IOS,
		DeviceOS::AMAZON,
		DeviceOS::WINDOWS_10,
		DeviceOS::WIN32,
		DeviceOS::PLAYSTATION,
		DeviceOS::NINTENDO,
		DeviceOS::XBOX
	];

	public function checkOS(PlayerPreLoginEvent $event) : void{
		$playerInfo = $event->getPlayerInfo();
		$extraData = $playerInfo->getExtraData();
		$nickname = $playerInfo->getUsername();

		/** @var Player[] $playersToKick */
		$playersToKick = [];
		$count = 0;
		foreach (Loader::getInstance()->getServer()->getOnlinePlayers() as $player) {
			if (!($player->isConnected())) {
				continue;
			}

			if ($event->getIp() === $player->getNetworkSession()->getIp()) {
				$playersToKick[] = $player;
				$count++;
			}
		}

		if ($count >= EditionFaker::IP_LIMIT) {
			foreach ($playersToKick as $player) {
				if ($player->isConnected()) {
					$this->notifyAdmins($player, true);
					$this->kick($player, $this->typeToReasonString($this->getFlagId()));
				}
			}
			$this->notifyAdmins($nickname, true);
			$event->setKickFlag(PlayerPreLoginEvent::KICK_FLAG_PLUGIN, TF::GRAY . "Error: " . $this->typeToReasonString($this->getFlagId()));
			return;
		}

		if (!(in_array($extraData["DeviceOS"], EditionFaker::DEVICE_OS_LIST, true))) {
			$this->notifyAdmins($nickname, true);
			$event->setKickFlag(PlayerPreLoginEvent::KICK_FLAG_PLUGIN, TF::GRAY . "Error: " . $this->typeToReasonString($this->getFlagId()));
			return;
		}

		if (!(in_array($extraData["DeviceOS"], EditionFaker::NULL_MODELS, true)) && $extraData["DeviceModel"] === "") {
			$this->notifyAdmins($nickname, true);
			$event->setKickFlag(PlayerPreLoginEvent::KICK_FLAG_PLUGIN, TF::GRAY . "Error: " . $this->typeToReasonString($this->getFlagId()));
			return;
		}

		if ($extraData["DeviceOS"] === DeviceOS::ANDROID) {
			$model = explode(" ", $extraData["DeviceModel"], 2)[0];
			if ($model !== strtoupper($model) && $model !== "") {
				$this->notifyAdmins($nickname, true);
			$event->setKickFlag(PlayerPreLoginEvent::KICK_FLAG_PLUGIN, TF::GRAY . "Error: " . $this->typeToReasonString($this->getFlagId()));
				return;
			}
		}

		if ($extraData["DeviceOS"] === DeviceOS::IOS) {
			if ($extraData["DeviceId"] !== strtoupper($extraData["DeviceId"])) {
				$this->notifyAdmins($nickname, true);
				$event->setKickFlag(PlayerPreLoginEvent::KICK_FLAG_PLUGIN, TF::GRAY . "Error: " . $this->typeToReasonString($this->getFlagId()));
			}
		}
	}
}
