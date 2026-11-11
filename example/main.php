#! /usr/bin/env php
<?php

/**
 * main.php
 * @Auteur: Christophe Dufour
 * 
 * Fichier de test
 */

require "../vendor/autoload.php";
use Blackfox\GetOptions\GetOptions;

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

//$options = GetOptions::options($short_options, $long_options , true, false);

try {
    //$options = GetOptions::options($short_options, $long_options , true, false);
    $options = GetOptions::options($short_options, $long_options, false);
}
catch(Exception $e) {
    fwrite(STDERR, "$argv[0]: " . $e->getMessage());
    exit(1);
}

var_dump($options);
var_dump(GetOptions::invalidOptions());
var_dump(GetOptions::positional());
