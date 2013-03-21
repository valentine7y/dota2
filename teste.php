<?php

$json = json_decode(file_get_contents('items.json'));

echo '<pre>';
print_r($json);
