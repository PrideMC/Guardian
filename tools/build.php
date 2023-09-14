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

const COMPRESS_FILES = true;
const COMPRESSION = Phar::GZ;

$from = getcwd() . DIRECTORY_SEPARATOR;
$to = getcwd() . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR;

@mkdir($to, 0777, true);

copyDirectory($from . "src", $to . "src");
copyDirectory($from . "resources", $to . "resources");

yaml_emit_file($to . "plugin.yml", (array) yaml_parse_file($from . "plugin.yml"));

$outputPath = getcwd() . DIRECTORY_SEPARATOR . "Guardian.phar";

@unlink($outputPath);

$phar = new Phar($outputPath);
$phar->buildFromDirectory($to);

if (COMPRESS_FILES) $phar->compressFiles(COMPRESSION);

removeDirectory($to);

print("Succeed! Output path: $outputPath");

function copyDirectory(string $from, string $to) : void{
	@mkdir($to, 0777, true);
	$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
	foreach($files as $fileInfo){
		$target = str_replace($from, $to, $fileInfo->getPathname());
		if($fileInfo->isDir()) @mkdir($target, 0777, true);
		else{
			$contents = file_get_contents($fileInfo->getPathname());
			file_put_contents($target, $contents);
		}
	}
}

function removeDirectory(string $dir) : void{
	$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
	foreach($files as $fileInfo){
		if($fileInfo->isDir()) rmdir($fileInfo->getPathname());
		else unlink($fileInfo->getPathname());
	}
	rmdir($dir);
}
