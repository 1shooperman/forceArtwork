<?php
class Scraper {

    public static $_url = "http://thegamesdb.net/api/GetGame.php?id=";

    protected function Scraper(){}

    public static function getRemoteGameXml($id) 
    {
        $ch = curl_init(); 
        
        curl_setopt($ch, CURLOPT_URL, static::_url . $id); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        
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

    /**
     * @see https://stackoverflow.com/questions/6348602/download-remote-file-to-server-with-php
     */
    public static function getImage($imageUrl, $name = '') 
    {
        $destination = "data/";
        if (strlen($name)) {
            $destination .= $name;
        }
        
        $fp = fopen($destination, 'w+');
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_BINARYTRANSFER, true );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt( $ch, CURLOPT_FILE, $fp );
        curl_exec( $ch );
        curl_close( $ch );
        fclose( $fp );

        return true;
    }

    public static function getGame($id) 
    {

    }
}