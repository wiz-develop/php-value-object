<?php

declare(strict_types=1);

require 'vendor/autoload.php';

/**
 * PHP CS Fixer Configuration File.
 */

// paths for ignore formatting
$finder = \PhpCsFixer\Finder::create()
    ->exclude([
        'vendor',
    ])
    ->in(__DIR__);

$config = new \WizDevelop\PhpCsFixerConfig\Config(allowRisky: true);

// custom rules
return $config
    ->setRiskyAllowed(true)
    ->setFinder($finder);
