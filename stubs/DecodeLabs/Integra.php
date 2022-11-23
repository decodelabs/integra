<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Integra\Context as Inst;
use DecodeLabs\Atlas\Dir as RunDirPlugin;
use DecodeLabs\Atlas\Dir as RootDirPlugin;
use DecodeLabs\Atlas\File as ComposerFilePlugin;
use DecodeLabs\Integra\Manifest as Ref0;

class Integra implements Proxy
{
    use ProxyTrait;

    const VENEER = 'DecodeLabs\Integra';
    const VENEER_TARGET = Inst::class;

    public static Inst $instance;
    public static RunDirPlugin $runDir;
    public static RootDirPlugin $rootDir;
    public static ComposerFilePlugin $composerFile;

    public static function getLocalManifest(): Ref0 {
        return static::$instance->getLocalManifest();
    }
};
