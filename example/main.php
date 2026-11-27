#! /usr/bin/env php
<?php

/**
 * main.php
 * @Auteur: Christophe Dufour
 * 
 * Fichier de test
 */

require dirname(__DIR__) . "/vendor/autoload.php";

use ChrisDfr\GetOptions\GetOptions;

if($argc == 1) {
    $command = "./main -a=x}x@x/x -bcd YYYY -ef -gh ZZZZ --help --aaa yyy/sdf --bbb=zertè§è§gf@ihj --aze -iVVVVVV -a xXx";
    $argv = explode(' ', $command);
    $argc = count($argv);
}

$short_options = "a:cefi:";
$long_options = [
    "aaa:",
    "bbb:",
    "help",
    "version"
];

try {
    $options = GetOptions::options($short_options, $long_options, false);
}
catch(Exception $e) {
    fwrite(STDERR, "$argv[0]: [" . get_class($e) . "] " .  $e->getMessage());
    exit(1);
}
catch(ValueError $e) {
    fwrite(STDERR, basename(__FILE__) . ": [" . get_class($e) . "] " .  $e->getMessage());
    exit(1);
}

echo "Options valide:" . PHP_EOL;

foreach(GetOptions::$options as $opt) {
    foreach($opt as $key => $value) {
        if($value !== false) {
            echo "  - " . $key, " => " . $value . PHP_EOL;
        }
        else {
            echo "  - " . $key, "  " . PHP_EOL;
        }
    }
}

echo "Options invalides: ";

foreach(GetOptions::$invalidOptions as $key) {
    echo $key, "  ";
}

echo PHP_EOL;
echo "Arguments positionnels: ";

foreach(GetOptions::$positional as $key) {
    echo $key, "  ";
}