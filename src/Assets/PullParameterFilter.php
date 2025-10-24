<?php
declare(strict_types = 1);
namespace Slothsoft\SSE\Assets;

use Slothsoft\Core\IO\Sanitizer\FileNameSanitizer;
use Slothsoft\Core\IO\Sanitizer\IntegerSanitizer;
use Slothsoft\Farah\Module\Asset\ParameterFilterStrategy\AbstractMapParameterFilter;

class PullParameterFilter extends AbstractMapParameterFilter {
    
    protected function createValueSanitizers(): array {
        return [
            'name' => new FileNameSanitizer(),
            'lastId' => new IntegerSanitizer()
        ];
    }
}

