<?php
namespace YellowedPages\Data\DataMapper\Json;
use \OutOfBoundsException;
use \JsonException;

class Entity {
    private $folder;

    public function __construct(string $folder) {
        $this->folder = $folder;
    }

    public static function fromFolder(string $folder): self {
        return new Entity($folder);
    }

    public function getFolder(): string {
        return $this->folder;
    }

    public function read(string $identifier): array {
        $path = $this->folder . '/' . str_replace(':', '/', $identifier) . '.json';
        if (!is_readable($path)) {
            throw new OutOfBoundsException("Entity $identifier does not exists");
        }
        $content = file_get_contents($path);
        $data = json_decode($content, true);
        if (empty($data)) {
            throw new JsonException("Cannot parse $identifier");
        }
        return $data;
    }

    public function search(?string $type): array {
        $type_folder = $type ?? '*';
        $pattern = $this->folder . '/' . $type_folder . '/*.json';
        return array_map(
            fn (string $path) => basename(str_replace('/', ':', substr($path, strlen($this->folder) + 1)), '.json'),
            glob($pattern)
        );
    }
}