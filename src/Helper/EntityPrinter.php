<?php
namespace YellowedPages\Data\Helper;

class EntityPrinter {
    public static function fromDefault(): self {
        return new EntityPrinter;
    }
    
    public function __invoke(array $data) {
        print_r($data);
    }
}