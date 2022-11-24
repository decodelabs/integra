<?php

/**
 * @package Integra
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

/**
 * global helpers
 */

namespace DecodeLabs\Integra
{
    use DecodeLabs\Integra;
    use DecodeLabs\Veneer;

    // Register the Veneer facade
    Veneer::register(Context::class, Integra::class);
}
