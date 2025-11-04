<?php

/**
 * Integra
 * @license https://opensource.org/licenses/MIT
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
