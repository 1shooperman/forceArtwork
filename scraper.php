<?php

require_once('./lib/scraperClass.php');

$gameId = (int) $argv[1];
$source = @$argv[2] or 'local';

$gameData = Scraper::getGame($gameId, $source);

$gameName = Scraper::getName($gameData);

if ($source === 'remote') {
    Scraper::getImage((string)$gameData->remoteImage, $gameName);
}

$dom = new DOMDocument("1.0");
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($gameData->asXML());

$fp = fopen("data/$gameName.xml", "w+");
fwrite($fp, $dom->saveXML());
fclose($fp);