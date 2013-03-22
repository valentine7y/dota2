<?php

require_once 'Dota2_API.php';

$api = new Dota2_API('72F82C4B0CBCC1704CDD2130E685E767');

//Dota2_API::dump($api->getHeroes(), false);
//Dota2_API::dump($api->getItems(), false);
//Dota2_API::dump($api->downloadHeroesImages(), false);
Dota2_API::dump($api->downloadItemsImages(), false);