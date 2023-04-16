#!/usr/bin/env php
<?php
namespace YellowedPages\Data;
use \Exception;
use \RuntimeException;

////////////////////////////////////////////////////////////////////////
// Includes autoloader
foreach ([
    'vendor/autoload.php',
    __DIR__ . '/../../autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php'
    ] as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}


////////////////////////////////////////////////////////////////////////
// Sanity check and configuration
if ($argc < 3) {
    die(
        "Usage:" . PHP_EOL
      . "\t " . $argv[0] . " <config> <command>" . PHP_EOL
      . "Where:" . PHP_EOL
      . "\t<config>:   Path to configuration file" . PHP_EOL
      . "\t<command>:  Command to execute" . PHP_EOL
      . "\t\tcreate:  Creates ElasticSearch index" . PHP_EOL
      . "\t\tdelete:  Deletes ElasticSearch index" . PHP_EOL
      . "\t\treset:   Resets ElasticSearch index" . PHP_EOL
    );
}
if (!is_readable($argv[1])) {
    throw new RuntimeException('Cannot read configuration from ' . $argv[1] . '.');
}
$configuration = parse_ini_file($argv[1], true);
if (empty($configuration)) {
    throw new RuntimeException('Cannot read configuration from ' . $argv[1] . '.');
}


////////////////////////////////////////////////////////////////////////
// Reads parameters and sets up connections
$command = trim($argv[2]);
$elastic_service = Service\ElasticSearchManager::fromConfiguration($configuration['ElasticSearch']);


////////////////////////////////////////////////////////////////////////
// Executes command
if ($command === 'create') {
    try {
        print("Creating ElasticSearch index...");
        $elastic_service->create();
        print(" done" . PHP_EOL);
    }
    catch (Exception $e) {
        printf(" failed: %s" . PHP_EOL, $e->getMessage());
    }
}

elseif ($command === 'delete') {
    try {
        print("Deleting ElasticSearch index...");
        $elastic_service->delete();
        print(" done" . PHP_EOL);
    }
    catch (Exception $e) {
        printf(" failed: %s" . PHP_EOL, $e->getMessage());
    }
}

elseif ($command === 'reset') {
    try {
        print("Resetting ElasticSearch index...");
        $elastic_service->delete();
        $elastic_service->create();
        print(" done" . PHP_EOL);
    }
    catch (Exception $e) {
        printf(" failed: %s" . PHP_EOL, $e->getMessage());
    }
}

else {
    die("Unknown command $command" . PHP_EOL);
}