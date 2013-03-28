<?php

class Dota2_API
{
    protected $_apiKey;
    
    const HEROES_NAME_PREFIX = 'npc_dota_hero_';
    
    const STEAM_ID_UPPER_32_BITS = '00000001000100000000000000000001';
    
    /**
     * DO NOT FORGET TO CHANGE THESE PATH ACCORDING TO YOUR ENVIRONMENT 
     */
    const API_URL_ITEMS = 'http://localhost/dota2/json/items.json';
    const API_URL_ITEMS_DATA = 'http://localhost/dota2/json/items_data.json';
    
    const API_URL_HEROES = 'https://api.steampowered.com/IEconDOTA2_570/GetHeroes/v0001/';
    const API_URL_HEROES_DATA = 'http://www.dota2.com/jsfeed/heropickerdata?v=170666872723459802&l=portuguese';
    
    const API_URL_MATCH_DETAILS = 'https://api.steampowered.com/IDOTA2Match_570/GetMatchDetails/V001/';
    
    const API_URL_PLAYER_SUMMARIES = 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/';
    
    const API_URL_MATCH_HISTORY = 'https://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/V001/';
    
    /**
     * CONVENTION OF THE URLS OF THE IMAGES ON THE SITE DOTA2.COM 
     */
    const URL_IMAGE_ITEMS = 'http://media.steampowered.com/apps/dota2/images/items/%NAME%_lg.png';
    const URL_IMAGE_HEROES = 'http://media.steampowered.com/apps/dota2/images/heroes/%NAME%_full.png';
    
    public function __construct($key)
    {
        $this->_apiKey = $key;
    }
    
    public static function dump($a, $exit = true)
    {
        $style = implode(';', array(
            'color' => '#FFF',
            'padding' => '25px',   
            'background-color' => '#333',   
            'boder' => '1px dashed #FFF000'
        ));
        
        echo '<pre style="'. $style .'">';
        print_r($a);
        echo '</pre>';
        
        if($exit)
            exit;
    }
    
    // gets the lower 32-bits of a 64-bit steam id
    public function to32b($id) 
    {
        $upper = gmp_mul(bindec(self::STEAM_ID_UPPER_32_BITS), "4294967296");
        return gmp_strval(gmp_sub($id, $upper));
    }

    // creates a 64-bit steam id from the lower 32-bits
    public function to64b($id, $hi = false) 
    {
        if($hi === false) 
            $hi = bindec(self::STEAM_ID_UPPER_32_BITS);

        // workaround signed/unsigned braindamage on x32
        $hi = sprintf("%u", $hi);
        $id = sprintf("%u", $id);

        return gmp_strval(gmp_add(gmp_mul($hi, "4294967296"), $id));      
    } 
    
    public function getJson($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_REFERER, $_SERVER['REQUEST_URI']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $result = curl_exec($ch) or die(curl_error($ch));
        curl_close($ch);
        
        $json = json_decode($result);
        
        if(!($json instanceof stdClass))
            throw new Exception_Dota2_API('not is Object');
        
        return $json;
    }
    
    public function download($url, $pathSafe)
    {
        $headers = @get_headers($url);
        if(strpos(strtolower($headers[0]), 'not found') !== false)
            return false;
        
        $content = file_get_contents($url);
        return file_put_contents($pathSafe, $content);
    }
    
    public function getHeroes($id = null)
    {
        $url = self::API_URL_HEROES . '?key=' . $this->_apiKey;
        $obj = $this->getJson($url);
        
        $url_data = self::API_URL_HEROES_DATA;
        $obj_data = $this->getJson($url_data);
        
        if(!isset($obj->result))
            throw new Exception_Dota2_API('->result not exist');
        
        if(!isset($obj->result->heroes))
            throw new Exception_Dota2_API('->result->heroes not exist');
        
        $heroes = $obj->result->heroes;
        
        if($id)
        {
            if(!isset($heroes[$id]))
                throw new Exception_Dota2_API('not exist Heroes with id = "'. $id .'"');
            
            $heroes = array($heroes[$id]);
        }
        
        foreach($heroes as &$hero)
        {
            $name = $hero->name = str_replace(self::HEROES_NAME_PREFIX, '', $hero->name);
            $hero->image = str_replace('%NAME%', $hero->name, self::URL_IMAGE_HEROES);
            $hero->data = isset($obj_data->$name) ? $obj_data->$name : false;
        }
        
        return $id ? $heroes[0] : $heroes;
    }
    
    public function getItems($id = null)
    {
        $url = self::API_URL_ITEMS;
        $obj = $this->getJson($url);
        
        $url_lore = self::API_URL_ITEMS_DATA;
        $obj_lore = $this->getJson($url_lore);
        
        if(!isset($obj->items))
            throw new Exception_Dota2_API('->items not exist');
        
        $items = $obj->items;
        
        if($id)
        {
            if(!isset($items[$id]))
                throw new Exception_Dota2_API('not exist Item with id = "'. $id .'"');
            
            $items = array($items[$id]);
        }
        
        foreach($items as &$item)
        {
            $name = $item->name;
            $item->image = str_replace('%NAME%', $item->name, self::URL_IMAGE_ITEMS);
            $item->data = isset($obj_lore->$name) ? $obj_lore->$name : false;
        }
        
        return $id ? $items[0] : $items;
    }
    
    public function getMatch($id)
    {
        $url = self::API_URL_MATCH_DETAILS .'?key='. $this->_apiKey . '&match_id='. $id;
        $json = $this->getJson($url);
        
        if(!isset($json->result))
            throw new Exception_Dota2_API('not found match');
        
        return $json->result;
    }
    
    public function getMatchHistory($id)
    {
        $url = self::API_URL_MATCH_HISTORY .'?key='. $this->_apiKey . '&account_id='. $id;
        $json = $this->getJson($url);
        
        var_dump(count($json->result->matches));
        exit;
        
        return $json;
        
        if(!isset($json->result))
            throw new Exception_Dota2_API('not found match');
        
        return $json->result;
    }
    
    public function getPlayer($id, $to64b = true)
    {
        if($to64b)
            $id = $this->to64b($id);
        
        $url = self::API_URL_PLAYER_SUMMARIES .'?key='. $this->_apiKey . '&steamids='. $id;
        $json = $this->getJson($url);
        
        if(empty($json->response->players))
            throw new Exception_Dota2_API('not found player');
        
        return $json->response->players[0];
    }
    
    protected function downloadMedias($store, $pathSafe)
    {
        if(!file_exists($pathSafe))
            mkdir($pathSafe, 0777, true);
        
        $items = $store;
        
        $downloadedFiles = array();
        
        foreach($items as $item)
        {
            $pathInfo = pathinfo($item->image);
            $downloadedFiles[$pathInfo['basename']] = $this->download($item->image, $pathSafe . $pathInfo['basename']);
        }
        
        return $downloadedFiles;
    }
    
    public function downloadHeroesImages($pathSafe = 'images/heroes/')
    {
        $store = $this->getHeroes();
        return $this->downloadMedias($store, $pathSafe);
    }
    
    public function downloadItemsImages($pathSafe = 'images/items/')
    {
        $store = $this->getItems();
        return $this->downloadMedias($store, $pathSafe);
    }
}

class Exception_Dota2_API extends Exception
{
}