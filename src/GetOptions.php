<?php

/**
 * GetOptions.php
 * @Auteur: Christophe Dufour
 * 
 * GetOptions permet de gérer les options passées en ligne de commande un script PHP
 */

namespace ChrisDfr\GetOptions;

use ValueError;

class GetOptions {

    public const SHORT_OPTIONS_VALIDATION_PATTERN = "/^([a-zA-Z0-9]{1}[:]?)+$/";
    public const LONG_OPTIONS_VALIDATION_PATTERN = "/^[a-zA-Z0-9]{2,}[:]?$/";
    public const SHORT_OPTIONS_PATTERN = "/^-([a-zA-Z0-9]{1})[=]?(.*)$/";
    public const LONG_OPTIONS_PATTERN = "/^--([a-zA-Z0-9]{2,})[=]?(.*)$/";


    protected static string $short_options;
    protected static array $long_options;
    protected static bool $showErrors;
    public protected(set) static array $options = [];
    public protected(set) static array $positional = [];
    public protected(set) static array $invalidOptions = [];

    /**
     * Analyse les options passées à la ligne de commande et retourne la valeur de options
     * 
     * @param string $short_options
     * Chaine de caractère des options courtes
     * @param array $long_options
     * [optional] Tableau des options longues
     * @param bool $showErrors
     * [optional] Affiche un message d'erreur en cas d'option invalide
     * @return array
     * 
     */
    public static function options(string $short_options, array $long_options = [], bool $showErrors = false): array
    {
        global $argv;
        self::$short_options = $short_options;
        self::$long_options = $long_options;
        self::$showErrors = $showErrors;

        self::verifyShortOption();

        if(!empty($long_options)) {
            self::verifyLongOption();
        }

        $arguments = array_slice($argv, 1);

        while($subject = current($arguments)) {
            if(preg_match(self::SHORT_OPTIONS_PATTERN, $subject)) {
                self::analyzeShortOptions($subject, $arguments);
            }
            elseif(preg_match(self::LONG_OPTIONS_PATTERN, $subject, $matches)) {
                self::analyzeLongOptions($subject, $matches, $arguments);
            }
            else {
                self::$positional[] = $subject;
            }

            next($arguments);
        }

        return self::$options;
    }

    /**
     * Vérifie la chaine de caractères des options courtes
     * @throws ValueError
     * @return void
     */
    protected static function verifyShortOption(): void
    {
        if(!preg_match(self::SHORT_OPTIONS_VALIDATION_PATTERN, self::$short_options)) {
            throw new ValueError("short_options: format de chaine invalide");
        }
    }

    /**
     * Vérifie le tableau des options longues
     * 
     * @throws ValueError
     * @return void
     */
    protected static function verifyLongOption(): void
    {
        foreach(self::$long_options as $options) {
            if(!preg_match(self::LONG_OPTIONS_VALIDATION_PATTERN, $options)) {
                throw new ValueError("long_options: format de chaine invalide");
            }
        }
    }

    /**
     * Analise les options courtes
     * 
     * @param string $subject
     * @param array &$arguments
     * @throws ValueError
     * @return void
     */
    protected static function analyzeShortOptions(string $subject, array &$arguments): void
    {
        $subject = substr($subject, 1);

        while(!empty($subject)) {
            if(($pos = strpos(self::$short_options, $subject[0])) !== false) {
                if(isset(self::$short_options[$pos+1]) && self::$short_options[$pos+1] == ':') {
                    if(isset($subject[1])) {
                        if($subject[1] == '=') {
                            self::$options[][$subject[0]] = substr($subject, 2);
                        }
                        else {
                            self::$options[][$subject[0]] = substr($subject, 1);
                        }
                    }
                    else {
                        if(($next = next($arguments)) !== false) {
                            if($next[0] != '-') {
                                self::$options[][$subject] = $next;
                            }
                            else {
                                throw new ValueError("Valeur requise pour l'option -- $subject.");
                            }
                        }
                        else {
                            throw new ValueError("Valeur requise pour l'option -- $subject.");
                        }
                    }

                    $subject = "";
                }
                else {
                    self::$options[][$subject[0]] = false;
                }
            }
            else {
                self::$invalidOptions [] = $subject[0];

                if(self::$showErrors) {
                    fwrite(STDERR, "Option invalide ignorée -- $subject[0]" . PHP_EOL);
                }
            }

            $subject = substr($subject, 1);
        }
    }

    /**
     * Analise les options longues
     * 
     * @param string $subject
     * @param array $matches
     * @param array &$arguments
     * @return void
     */
    protected static function analyzeLongOptions(string $subject, array $matches, array &$arguments): void
    {
        $subject = $matches[1];

        if(in_array($subject, self::$long_options) || 
            in_array($subject . ":", self::$long_options)) {
            
            if(($pos = array_search($subject, self::$long_options)) == false) {
                $pos = array_search($subject . ':', self::$long_options);
            }

            if(substr(self::$long_options[$pos], -1, 1) == ':') {
                if(!empty($matches[2])) {
                    self::$options[][$subject] = $matches[2];
                }
                else {
                    if(($next = next($arguments)) !== false) {
                        if($next[0] != '-') {
                            self::$options[][$subject] = $next;
                        }
                        else {
                            throw new ValueError("Valeur requise pour l'option -- $subject.");
                        }
                    }
                    else {
                        throw new ValueError("Valeur requise pour l'option -- $subject.");
                    }
                }
            }
            else {
                self::$options[][$subject] = false;
            }
        }
        else {
            self::$invalidOptions[] = $subject;

            if(self::$showErrors) {
                fwrite(STDERR, "Option invalide ignorée -- $subject" . PHP_EOL);
            }
        }
    }

    /**
     * Retourne le tableau des arguments positionnel
     * 
     * @return array
     */
    public static function positional(): array
    {
        return self::$positional;
    }

    /**
     * Retourne le tableau des options invalides
     * 
     * @return array
     */
    public static function invalidOptions(): array
    {
        return self::$invalidOptions;
    }

}
