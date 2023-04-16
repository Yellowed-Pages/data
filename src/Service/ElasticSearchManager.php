<?php
namespace YellowedPages\Data\Service;

class ElasticSearchManager {
    const DEFAULT_SETTINGS_PATH = __DIR__ . '/../../assets/index-settings.json';
    const DEFAULT_MAPPING_PATH = __DIR__ . '/../../assets/index-mapping.json';
    private $host;
    private $port;
    private $username;
    private $password;
    private $index;
    private $settings_path;
    private $mapping_path;

    public function __construct(
        string $host,
        int $port,
        string $username,
        string $password,
        string $index,
        string $settings_path,
        string $mapping_path
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->index = $index;
        $this->settings_path = $settings_path;
        $this->mapping_path = $mapping_path;
    }

    public static function fromConfiguration(array $configuration): self {
        return new ElasticSearchManager(
            $configuration['host'],
            $configuration['port'],
            $configuration['username'],
            $configuration['password'],
            $configuration['index'],
            self::DEFAULT_SETTINGS_PATH,
            self::DEFAULT_MAPPING_PATH
        );
    }

    public function create() {
        $url = sprintf(
            "https://%s:%s@%s:%d/%s",
            $this->username,
            $this->password,
            $this->host,
            $this->port,
            $this->index
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Creates index
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PUT, 1);
        $result = curl_exec($ch);
        print_r($result);

        // Closes index
        curl_setopt($ch, CURLOPT_URL, $url . '/_close?wait_for_active_shards=0');
        curl_setopt($ch, CURLOPT_PUT, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        $result = curl_exec($ch);
        print_r($result);

        // Creates settings
        $fh = fopen($this->settings_path, 'r');
        curl_setopt($ch, CURLOPT_URL, $url . '/_settings');
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_PUT, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_INFILE, $fh);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($this->settings_path));
        $result = curl_exec($ch);
        fclose($fh);
        print_r($result);

        // Creates mapping
        $fh = fopen($this->mapping_path, 'r');
        curl_setopt($ch, CURLOPT_URL, $url . '/_mapping');
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_PUT, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_INFILE, $fh);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($this->mapping_path));
        $result = curl_exec($ch);
        fclose($fh);
        print_r($result);

        // Opens index
        curl_setopt($ch, CURLOPT_URL, $url . '/_open');
        curl_setopt($ch, CURLOPT_PUT, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_INFILE, null);
        curl_setopt($ch, CURLOPT_INFILESIZE, 0);
        $result = curl_exec($ch);
        print_r($result);
    }

    public function delete() {
        $url = sprintf(
            "https://%s:%s@%s:%d/%s",
            $this->username,
            $this->password,
            $this->host,
            $this->port,
            $this->index
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        print_r($result);
    }
}