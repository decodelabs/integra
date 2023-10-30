<?php

/**
 * @package Integra
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Integra\Structure;

class Package
{
    public function __construct(
        public string $name,
        public string $version
    ) {
    }
}
