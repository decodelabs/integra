<?php

/**
 * @package Integra
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Integra\Structure;

class Funding
{
    public function __construct(
        public ?string $type = null,
        public ?string $url = null
    ) {
    }
}
