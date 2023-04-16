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
      . "\t\tread <id>:      Shows entity with given identifier" . PHP_EOL
      . "\t\tlist [<type>]:  Lists identifiers of entities" . PHP_EOL
      . "\t\tcheck:          Performs an integrity check over every data file" . PHP_EOL
      . "\t\tindex [<id>]:   Sends entities to remote index" . PHP_EOL
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
$json_mapper = DataMapper\Json\Entity::fromFolder($configuration['FileSystem']['entities']);
$elastic_mapper = DataMapper\ElasticSearch\Entity::fromConfiguration($configuration['ElasticSearch']);
$printer = Helper\EntityPrinter::fromDefault();


////////////////////////////////////////////////////////////////////////
// Executes command
if ($command === 'read' && count($argv) > 3) {
    $identifier = trim($argv[3]);
    try {
        $data = $json_mapper->read($identifier);
        $printer($data);
    }
    catch (Exception $e) {
        die($e->getMessage() . PHP_EOL);
    }
}

elseif ($command === 'list') {
    $type = $argv[3] ?? null;
    $identifiers = $json_mapper->search($type);
    printf(
        "Available entities%s (%d):" . PHP_EOL,
        (!is_null($type) ? " for type $type" : ""),
        count($identifiers)
    );
    array_walk(
        $identifiers,
        fn (string $identifier) => print("\t$identifier" . PHP_EOL)
    );
}

elseif ($command === 'check') {
    $identifiers = $json_mapper->search(null);
    $errors = [];
    foreach ($identifiers as $identifier) {
        try {
            print("\tChecking $identifier...");
            $json_mapper->read($identifier);
            print(" done" . PHP_EOL);
        }
        catch (Exception $e) {
            printf(" failed" . PHP_EOL);
            $errors[] = $identifier;
        }
    }
    printf(
        "There were %d errors: %s" . PHP_EOL,
        count($errors),
        implode(', ', $errors)
    );
}

elseif ($command === 'index') {
    $identifiers = count($argv) > 3
        ? [trim($argv[3])]
        : $json_mapper->search(null)
    ;
    $errors = [];
    foreach ($identifiers as $identifier) {
        try {
            print("\tSending $identifier...");
            $entity = $json_mapper->read($identifier);
            $elastic_mapper->create($entity);
            print(" done" . PHP_EOL);
        }
        catch (Exception $e) {
            printf(" failed" . PHP_EOL);
            $errors[] = $identifier;
        }
    }
    printf(
        "There were %d errors: %s" . PHP_EOL,
        count($errors),
        implode(', ', $errors)
    );
}

else {
    die("Unknown command $command" . PHP_EOL);
}