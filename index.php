<?php

require_once 'Dota2_API.php';

$api = new Dota2_API('<YOUR_STEAM_API_KEY>');

Dota2_API::dump($api->getHeroes(), false);
Dota2_API::dump($api->getItems(), false);