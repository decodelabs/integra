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
    public File $composerFile;


    protected ?string $phpBin = null;
    protected bool $forceLocal = false;
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
    public function setPhpBinary(string $bin): static
    {
        $this->phpBin = $bin;
        return $this;
    }

    /**
     * Get PHP binary
     */
    public function getPhpBinary(): string
    {
        return $this->phpBin ?? 'php';
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
     * Run composer command
     */
    public function run(
        string $arg,
        string ...$args
    ): bool {
        return $this->newComposerLauncher([$arg, ...$args])
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
     * Run script
     */
    public function runScript(
        string $name,
        string ...$args
    ): bool {
        return $this->run('run-script', $name, '--', ...$args);
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
     * Install package
     */
    public function install(
        string $name,
        ?string $version = null
    ): bool {
        $pkg = $name;

        if ($version !== null) {
            $pkg .= ':' . $version;
        }

        return $this->run('require', '"' . $pkg . '"');
    }

    /**
     * Install package
     */
    public function installGlobal(
        string $name,
        ?string $version = null
    ): bool {
        $pkg = $name;

        if ($version !== null) {
            $pkg .= ':' . $version;
        }

        return $this->runGlobal('require', '"' . $pkg . '"');
    }

    /**
     * Has package
     */
    public function hasPackage(string $package): bool
    {
        $manifest = $this->getLocalManifest();
        return $manifest->hasPackage($package);
    }
}
