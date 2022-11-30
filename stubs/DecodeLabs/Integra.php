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
use DecodeLabs\Atlas\Dir as BinDirPlugin;
use DecodeLabs\Atlas\File as ComposerFilePlugin;
use DecodeLabs\Integra\Manifest as Ref0;
use DecodeLabs\Terminus\Session as Ref1;
use DecodeLabs\Collections\Tree as Ref2;

class Integra implements Proxy
{
    use ProxyTrait;

    const VENEER = 'DecodeLabs\Integra';
    const VENEER_TARGET = Inst::class;

    public static Inst $instance;
    public static RunDirPlugin $runDir;
    public static RootDirPlugin $rootDir;
    public static BinDirPlugin $binDir;
    public static ComposerFilePlugin $composerFile;

    public static function getLocalManifest(): Ref0 {
        return static::$instance->getLocalManifest();
    }
    public static function setPhpBinary(?string $bin): Inst {
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
    public static function forceLocal(bool $force = true): Inst {
        return static::$instance->forceLocal(...func_get_args());
    }
    public static function isForcedLocal(): bool {
        return static::$instance->isForcedLocal();
    }
    public static function setCiMode(bool $mode): Inst {
        return static::$instance->setCiMode(...func_get_args());
    }
    public static function isCiMode(): bool {
        return static::$instance->isCiMode();
    }
    public static function run(string $arg, string ...$args): bool {
        return static::$instance->run(...func_get_args());
    }
    public static function runGlobal(string $arg, string ...$args): bool {
        return static::$instance->runGlobal(...func_get_args());
    }
    public static function hasScript(string $name): bool {
        return static::$instance->hasScript(...func_get_args());
    }
    public static function getScripts(): array {
        return static::$instance->getScripts();
    }
    public static function runScript(string $name, string ...$args): bool {
        return static::$instance->runScript(...func_get_args());
    }
    public static function hasBin(string $name): bool {
        return static::$instance->hasBin(...func_get_args());
    }
    public static function getBins(): array {
        return static::$instance->getBins();
    }
    public static function runBin(string $name, string ...$args): bool {
        return static::$instance->runBin(...func_get_args());
    }
    public static function runGlobalBin(string $name, string ...$args): bool {
        return static::$instance->runGlobalBin(...func_get_args());
    }
    public static function install(string $name, string ...$other): bool {
        return static::$instance->install(...func_get_args());
    }
    public static function installDev(string $name, string ...$other): bool {
        return static::$instance->installDev(...func_get_args());
    }
    public static function installGlobal(string $name, string ...$other): bool {
        return static::$instance->installGlobal(...func_get_args());
    }
    public static function installDevGlobal(string $name, string ...$other): bool {
        return static::$instance->installDevGlobal(...func_get_args());
    }
    public static function uninstall(string $name, string ...$other): bool {
        return static::$instance->uninstall(...func_get_args());
    }
    public static function uninstallDev(string $name, string ...$other): bool {
        return static::$instance->uninstallDev(...func_get_args());
    }
    public static function uninstallGlobal(string $name, string ...$other): bool {
        return static::$instance->uninstallGlobal(...func_get_args());
    }
    public static function uninstallDevGlobal(string $name, string ...$other): bool {
        return static::$instance->uninstallDevGlobal(...func_get_args());
    }
    public static function preparePackageInstallName(string $name, ?string $version = NULL): string {
        return static::$instance->preparePackageInstallName(...func_get_args());
    }
    public static function hasPackage(string $package): bool {
        return static::$instance->hasPackage(...func_get_args());
    }
    public static function getExtra(): Ref2 {
        return static::$instance->getExtra();
    }
};
