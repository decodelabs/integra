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
use DecodeLabs\Veneer\Plugin\Wrapper as PluginWrapper;
use DecodeLabs\Integra\Manifest as Ref0;
use DecodeLabs\Terminus\Session as Ref1;
use DecodeLabs\Collections\Tree as Ref2;

class Integra implements Proxy
{
    use ProxyTrait;

    public const Veneer = 'DecodeLabs\\Integra';
    public const VeneerTarget = Inst::class;

    protected static Inst $_veneerInstance;
    /** @var RunDirPlugin|PluginWrapper<RunDirPlugin> $runDir */
    public static RunDirPlugin|PluginWrapper $runDir;
    /** @var RootDirPlugin|PluginWrapper<RootDirPlugin> $rootDir */
    public static RootDirPlugin|PluginWrapper $rootDir;
    /** @var BinDirPlugin|PluginWrapper<BinDirPlugin> $binDir */
    public static BinDirPlugin|PluginWrapper $binDir;
    /** @var ComposerFilePlugin|PluginWrapper<ComposerFilePlugin> $composerFile */
    public static ComposerFilePlugin|PluginWrapper $composerFile;

    public static function getLocalManifest(): Ref0 {
        return static::$_veneerInstance->getLocalManifest();
    }
    public static function setPhpBinary(?string $bin): Inst {
        return static::$_veneerInstance->setPhpBinary(...func_get_args());
    }
    public static function getPhpBinary(): string {
        return static::$_veneerInstance->getPhpBinary();
    }
    public static function setSession(Ref1 $session): Inst {
        return static::$_veneerInstance->setSession(...func_get_args());
    }
    public static function getSession(): ?Ref1 {
        return static::$_veneerInstance->getSession();
    }
    public static function forceLocal(bool $force = true): Inst {
        return static::$_veneerInstance->forceLocal(...func_get_args());
    }
    public static function isForcedLocal(): bool {
        return static::$_veneerInstance->isForcedLocal();
    }
    public static function setCiMode(bool $mode): Inst {
        return static::$_veneerInstance->setCiMode(...func_get_args());
    }
    public static function isCiMode(): bool {
        return static::$_veneerInstance->isCiMode();
    }
    public static function run(string $arg, string ...$args): bool {
        return static::$_veneerInstance->run(...func_get_args());
    }
    public static function runGlobal(string $arg, string ...$args): bool {
        return static::$_veneerInstance->runGlobal(...func_get_args());
    }
    public static function hasScript(string $name): bool {
        return static::$_veneerInstance->hasScript(...func_get_args());
    }
    public static function getScripts(): array {
        return static::$_veneerInstance->getScripts();
    }
    public static function runScript(string $name, string ...$args): bool {
        return static::$_veneerInstance->runScript(...func_get_args());
    }
    public static function hasBin(string $name): bool {
        return static::$_veneerInstance->hasBin(...func_get_args());
    }
    public static function getBins(): array {
        return static::$_veneerInstance->getBins();
    }
    public static function runBin(string $name, string ...$args): bool {
        return static::$_veneerInstance->runBin(...func_get_args());
    }
    public static function runGlobalBin(string $name, string ...$args): bool {
        return static::$_veneerInstance->runGlobalBin(...func_get_args());
    }
    public static function install(string $name, string ...$other): bool {
        return static::$_veneerInstance->install(...func_get_args());
    }
    public static function installDev(string $name, string ...$other): bool {
        return static::$_veneerInstance->installDev(...func_get_args());
    }
    public static function installGlobal(string $name, string ...$other): bool {
        return static::$_veneerInstance->installGlobal(...func_get_args());
    }
    public static function installDevGlobal(string $name, string ...$other): bool {
        return static::$_veneerInstance->installDevGlobal(...func_get_args());
    }
    public static function uninstall(string $name, string ...$other): bool {
        return static::$_veneerInstance->uninstall(...func_get_args());
    }
    public static function uninstallDev(string $name, string ...$other): bool {
        return static::$_veneerInstance->uninstallDev(...func_get_args());
    }
    public static function uninstallGlobal(string $name, string ...$other): bool {
        return static::$_veneerInstance->uninstallGlobal(...func_get_args());
    }
    public static function uninstallDevGlobal(string $name, string ...$other): bool {
        return static::$_veneerInstance->uninstallDevGlobal(...func_get_args());
    }
    public static function preparePackageInstallName(string $name, ?string $version = NULL): string {
        return static::$_veneerInstance->preparePackageInstallName(...func_get_args());
    }
    public static function hasPackage(string $package): bool {
        return static::$_veneerInstance->hasPackage(...func_get_args());
    }
    public static function getExtra(): Ref2 {
        return static::$_veneerInstance->getExtra();
    }
};
