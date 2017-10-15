<?php
class Scraper {

    public static $_url = "http://thegamesdb.net/api/GetGame.php?id=";

    protected function Scraper(){}

    public static function getRemoteGameXml($id) 
    {
        $ch = curl_init(); 
        
        curl_setopt($ch, CURLOPT_URL, static::$_url . $id); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        
        $remoteXml = curl_exec($ch); 

        curl_close($ch); 
        
        return $remoteXml;
    }

    /**
     * for testing
     */
    public static function getLocalGameXml()
    {
        $localXml = file_get_contents("./example.xml");

        return $localXml;
    }

    public static function parseData(SimpleXMLElement $xml)
    {
        // path, image
        $gameData = array();  
        $gameData['name'] = (string) $xml->Game->GameTitle;
        $gameData['desc'] = (string) $xml->Game->Overview;
        $gameData['releasedate'] = static::parseDate((string) $xml->Game->ReleaseDate);
        $gameData['developer'] = (string) $xml->Game->Developer;
        $gameData['publisher'] = (string) $xml->Game->Publisher;
        $gameData['genre'] = (string) $xml->Game->Genres->genre;
        $gameData['remoteImage'] = static::parseImage($xml);

        $gameXml = new SimpleXMLElement('<game/>');
        $input = array_flip($gameData);
        array_walk_recursive($input, array($gameXml, 'addChild'));

        return $gameXml;
    }

    public static function parseDate(string $date)
    {
        $epoch = strtotime($date);
        $dt = new DateTime("@$epoch");
        return $dt->format('Ymd') . 'T000000';
    }

    public static function parseImage(SimpleXMLElement $xml) 
    {
        $base = (string) $xml->baseImgUrl;
        $images = (array) $xml->Game->Images;

        $image = '';
        foreach ($images['boxart'] as $img) {
            if (strpos($img, 'front')) {
                $image = $img;
                break;
            }
        }

        return $base . $image;
    }

    public static function getName(SimpleXMLElement $xml) 
    {
        $gameName = preg_replace(
            '/[^a-z0-9]/i',
            '_',
            trim(
                (string) $xml->name
            )
        );

        return $gameName;
    }

    /**
     * @see https://stackoverflow.com/questions/6348602/download-remote-file-to-server-with-php
     */
    public static function getImage($imageUrl, $name = 'foo') 
    {
        $pathInfo = pathinfo($imageUrl);
        $ext = $pathInfo['extension'];

        $destination = "data/";
        if (strlen($name)) {
            $destination .= "$name.$ext";
        }
        
        $fp = fopen($destination, 'w+');
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $imageUrl );
        curl_setopt( $ch, CURLOPT_BINARYTRANSFER, true );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        
        //curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt( $ch, CURLOPT_FILE, $fp );
        curl_exec( $ch );
        curl_close( $ch );
        fclose( $fp );

        return true;
    }

    public static function getGame($gameId, $src = 'local') 
    {
        if ($src === 'remote') {
            $gameXml = static::getRemoteGameXml($gameId);
        } else {
            $gameXml = static::getLocalGameXml();
        }

        $doc = new SimpleXMLElement($gameXml);
        
        $gameData = Scraper::parseData($doc);

        return $gameData;
    }
}