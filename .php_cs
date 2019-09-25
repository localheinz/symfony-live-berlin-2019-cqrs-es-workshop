<?php

declare(strict_types=1);

use Localheinz\PhpCsFixer\Config;

$config = Config\Factory::fromRuleSet(new Config\RuleSet\Php73());

$config->getFinder()
    ->ignoreDotFiles(false)
    ->in(__DIR__)
    ->exclude([
        '.build',
        'data',
        'template',
    ])
    ->name('.php_cs');

$config->setCacheFile(__DIR__ . '/.build/php-cs-fixer/.php_cs.cache');

return $config;
