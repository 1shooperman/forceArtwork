<?php

require_once('./lib/scraperClass.php');

$gameId = (int) $argv[1];

$gameXml = Scraper::getLocalGameXml();

$doc = new SimpleXMLElement($gameXml);

$gameData = Scraper::parseData($doc);

$gameName = preg_replace(
                '/[^a-z0-9]/i',
                '_',
                trim(
                    (string) $gameData->name
                )
            );

$dom = new DOMDocument("1.0");
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($gameData->asXML());

$fp = fopen("data/$gameName.xml", "w+");
fwrite($fp, $dom->saveXML());
fclose($fp);