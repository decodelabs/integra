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
use DecodeLabs\Terminus\Session as Ref1;
use DecodeLabs\Systemic\Process\Launcher as Ref2;

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
    public static function setPhpBinary(string $bin): Inst {
        return static::$instance->setPhpBinary(...func_get_args());
    }
    public static function getPhpBinary(): string {
        return static::$instance->getPhpBinary();
    }
    public static function setSession(Ref1 $session): Inst {
        return static::$instance->setSession(...func_get_args());
    }
    public static function getSession(): ?Ref1 {
        return static::$instance->getSession();
    }
    public static function newComposerLauncher(array|string|null $args = NULL): Ref2 {
        return static::$instance->newComposerLauncher(...func_get_args());
    }
    public static function forceLocal(bool $force = true): Inst {
        return static::$instance->forceLocal(...func_get_args());
    }
    public static function isForcedLocal(): bool {
        return static::$instance->isForcedLocal();
    }
    public static function run(string $arg, string ...$args): bool {
        return static::$instance->run(...func_get_args());
    }
    public static function runGlobal(string $arg, string ...$args): bool {
        return static::$instance->runGlobal(...func_get_args());
    }
    public static function runScript(string $name, string ...$args): bool {
        return static::$instance->runScript(...func_get_args());
    }
    public static function runBin(string $name, string ...$args): bool {
        return static::$instance->runBin(...func_get_args());
    }
    public static function install(string $name, ?string $version = NULL): bool {
        return static::$instance->install(...func_get_args());
    }
    public static function installGlobal(string $name, ?string $version = NULL): bool {
        return static::$instance->installGlobal(...func_get_args());
    }
    public static function hasPackage(string $package): bool {
        return static::$instance->hasPackage(...func_get_args());
    }
};
