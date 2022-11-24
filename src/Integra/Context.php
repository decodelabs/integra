<?php

/**
 * @package Integra
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Integra;

use DecodeLabs\Atlas;
use DecodeLabs\Atlas\Dir;
use DecodeLabs\Atlas\File;
use DecodeLabs\Coercion;
use DecodeLabs\Collections\Tree;
use DecodeLabs\Exceptional;
use DecodeLabs\Systemic;
use DecodeLabs\Systemic\Process\Launcher;
use DecodeLabs\Terminus;
use DecodeLabs\Terminus\Session;
use DecodeLabs\Veneer\LazyLoad;
use DecodeLabs\Veneer\Plugin;

#[LazyLoad]
class Context
{
    #[Plugin]
    public Dir $runDir;

    #[Plugin]
    public Dir $rootDir;

    #[Plugin]
    public Dir $binDir;

    #[Plugin]
    public File $composerFile;


    protected bool $forceLocal = false;
    protected bool $ciMode = false;

    protected ?string $phpBinary = null;
    protected Manifest $manifest;
    protected ?Session $session = null;


    public function __construct(?Dir $runDir = null)
    {
        if (!$runDir) {
            if (false === ($dir = getcwd())) {
                throw Exceptional::Runtime('Unable to get current working directory');
            }

            $runDir = Atlas::dir($dir);
        }

        $this->runDir = $runDir;
        $this->composerFile = $this->findComposerJson();
        $this->rootDir = $this->composerFile->getParent() ?? clone $runDir;
        $this->binDir = $this->findBinDir();
    }

    /**
     * Find composer json
     */
    protected function findComposerJson(): File
    {
        $dir = $this->runDir;

        do {
            $file = $dir->getFile('composer.json');

            if ($file->exists()) {
                return $file;
            }

            $dir = $dir->getParent();
        } while ($dir !== null);

        return $this->runDir->getFile('composer.json');
    }

    /**
     * Find bin dir
     */
    protected function findBinDir(): Dir
    {
        if (
            (!$path = getenv('COMPOSER_BIN_DIR')) ||
            false === ($path = realpath($path))
        ) {
            $path = null;
        }

        $path = $path ??
            Coercion::toStringOrNull($this->getLocalManifest()->getConfig()['bin-dir']) ??
            'vendor/bin';

        return $this->rootDir->getDir($path);
    }




    /**
     * Get composer.json manifest
     */
    public function getLocalManifest(): Manifest
    {
        if (!isset($this->manifest)) {
            $this->manifest = new Manifest($this->composerFile);
        }

        return $this->manifest;
    }

    /**
     * Set PHP binary
     *
     * @return $this;
     */
    public function setPhpBinary(?string $bin): static
    {
        $this->phpBinary = $bin;
        return $this;
    }

    /**
     * Get PHP binary
     */
    public function getPhpBinary(): string
    {
        return
            $this->phpBinary ??
            Systemic::$os->which('php') ??
            'php';
    }



    /**
     * Set CLI session
     *
     * @return $this
     */
    public function setSession(Session $session): static
    {
        $this->session = $session;
        return $this;
    }

    /**
     * Get CLI session
     */
    public function getSession(): ?Session
    {
        return $this->session;
    }


    /**
     * New composer launcher
     *
     * @param string|array<string>|null $args
     */
    public function newComposerLauncher(
        string|array|null $args = null
    ): Launcher {
        if ($args === null) {
            $args = [];
        } elseif (!is_array($args)) {
            $args = (array)$args;
        }

        if (null === ($composer = Systemic::$os->which('composer'))) {
            throw Exceptional::NotFound('Unable to locate global composer executable');
        }

        array_unshift($args, $composer);

        return Systemic::$process->newLauncher(
            $this->getPhpBinary(),
            $args,
            $this->rootDir,
            $this->session ??
                class_exists(Terminus::class) ?
                    Terminus::getSession() : null
        );
    }

    /**
     * Force local calls
     *
     * @return $this;
     */
    public function forceLocal(bool $force = true): static
    {
        $this->forceLocal = $force;
        return $this;
    }

    /**
     * Is forced local
     */
    public function isForcedLocal(): bool
    {
        return $this->forceLocal;
    }

    /**
     * Set CI mode
     *
     * @return $this;
     */
    public function setCiMode(bool $mode): static
    {
        $this->ciMode = $mode;
        return $this;
    }

    /**
     * Get CI mode
     */
    public function isCiMode(): bool
    {
        return $this->ciMode;
    }




    /**
     * Run composer command
     */
    public function run(
        string $arg,
        string ...$args
    ): bool {
        if (!(
            $this->forceLocal &&
            $arg === 'global'
        )) {
            $args = [$arg, ...$args];
        }

        $args = $this->reorderArguments($args);

        return $this->newComposerLauncher($args)
            ->launch()
            ->wasSuccessful();
    }

    /**
     * Run composer command
     */
    public function runGlobal(
        string $arg,
        string ...$args
    ): bool {
        $args = [$arg, ...$args];

        if (!$this->forceLocal) {
            $args = ['global', ...$args];
        }

        return $this->run(...$args);
    }

    /**
     * @param array<string> $args
     * @return array<string>
     */
    protected function reorderArguments(array $args): array
    {
        static $find = [
            '--no-interaction',
            '--no-plugins',
            '--no-scripts'
        ];

        $escape = false;
        $output = $script = [];

        foreach ($args as $arg) {
            if ($arg === '--') {
                $escape = true;
                continue;
            }

            if (in_array($arg, $find)) {
                $output[] = $arg;
            } elseif ($escape) {
                $script[] = $arg;
            } else {
                $output[] = $arg;
            }
        }

        if (
            $this->ciMode &&
            !in_array('--no-interaction', $output)
        ) {
        }

        if (!empty($script)) {
            $output[] = '--';
            $output = array_merge($output, $script);
        }

        return $output;
    }



    /**
     * Has script
     */
    public function hasScript(string $name): bool
    {
        return $this->getLocalManifest()->hasScript($name);
    }

    /**
     * Get scripts list
     *
     * @return array<string, string>
     */
    public function getScripts(): array
    {
        return $this->getLocalManifest()->getScripts();
    }

    /**
     * Run script
     */
    public function runScript(
        string $name,
        string ...$args
    ): bool {
        return $this->run('run-script', $name, '--', ...$args);
    }



    /**
     * Has bin
     */
    public function hasBin(string $name): bool
    {
        return
            false === strpos($name, '/') &&
            $this->rootDir->getFile('vendor/bin/' . $name)->exists();
    }

    /**
     * Get bin list
     *
     * @return array<File>
     */
    public function getBins(): array
    {
        return $this->binDir->listFiles();
    }

    /**
     * Run binary
     */
    public function runBin(
        string $name,
        string ...$args
    ): bool {
        return $this->run('exec', $name, '--', ...$args);
    }

    /**
     * Run global binary
     */
    public function runGlobalBin(
        string $name,
        string ...$args
    ): bool {
        return $this->runGlobal('exec', $name, '--', ...$args);
    }




    /**
     * Install package
     */
    public function install(
        string $name,
        string ...$other
    ): bool {
        return $this->run('require', $name, ...$other);
    }

    /**
     * Install package
     */
    public function installDev(
        string $name,
        string ...$other
    ): bool {
        return $this->run(...['require', $name, ...$other, '--dev']);
    }

    /**
     * Install package
     */
    public function installGlobal(
        string $name,
        string ...$other
    ): bool {
        return $this->runGlobal('require', $name, ...$other);
    }

    /**
     * Install package
     */
    public function installDevGlobal(
        string $name,
        string ...$other
    ): bool {
        return $this->runGlobal(...['require', $name, ...$other, '--dev']);
    }

    /**
     * Uninstall package
     */
    public function uninstall(
        string $name,
        string ...$other
    ): bool {
        return $this->run('remove', $name, ...$other);
    }

    /**
     * Uninstall package
     */
    public function uninstallDev(
        string $name,
        string ...$other
    ): bool {
        return $this->run(...['remove', $name, ...$other, '--dev']);
    }

    /**
     * Uninstall package
     */
    public function uninstallGlobal(
        string $name,
        string ...$other
    ): bool {
        return $this->runGlobal('remove', $name, ...$other);
    }

    /**
     * Uninstall package
     */
    public function uninstallDevGlobal(
        string $name,
        string ...$other
    ): bool {
        return $this->runGlobal(...['remove', $name, ...$other, '--dev']);
    }

    /**
     * Prepare package install name
     */
    public function preparePackageInstallName(
        string $name,
        ?string $version = null
    ): string {
        $pkg = $name;

        if ($version !== null) {
            $pkg .= ':' . $version;
        }

        return '"' . $pkg . '"';
    }

    /**
     * Has package
     */
    public function hasPackage(string $package): bool
    {
        $manifest = $this->getLocalManifest();
        return $manifest->hasPackage($package);
    }



    /**
     * Get extra config
     *
     * @phpstan-return Tree<string|int|float|null>
     */
    public function getExtra(): Tree
    {
        return $this->getLocalManifest()->getExtra();
    }
}
