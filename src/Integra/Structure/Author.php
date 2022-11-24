<?php

/**
 * @package Integra
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Integra\Structure;

class Author
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $homepageUrl = null,
        public ?string $role = null
    ) {
    }
}
