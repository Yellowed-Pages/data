<?php
namespace YellowedPages\Data\DataMapper\ElasticSearch;

class Entity {
    private $host;
    private $port;
    private $username;
    private $password;
    private $index;

    public function __construct(
        string $host,
        int $port,
        string $username,
        string $password,
        string $index
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->index = $index;
    }

    public static function fromConfiguration(array $configuration): self {
        return new Entity(
            $configuration['host'],
            $configuration['port'],
            $configuration['username'],
            $configuration['password'],
            $configuration['index']
        );
    }

    public function create(array $entity): self {
        $url = sprintf(
            "https://%s:%s@%s:%d/%s/_doc/%s",
            $this->username,
            $this->password,
            $this->host,
            $this->port,
            $this->index,
            $entity['id']
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($entity));
        $result = curl_exec($ch);
        print_r($result);
        return $this;
    }
}