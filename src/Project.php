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
use DecodeLabs\Monarch;
use DecodeLabs\Systemic;

class Project
{
    public protected(set) Dir $rootDir;
    public protected(set) Dir $binDir;
    public protected(set) File $composerFile;

    protected Manifest $manifest;

    /**
     * @var array<string,string>
     */
    protected array $paths = [];


    public function __construct(
        ?Dir $dir,
        protected Systemic $systemic
    ) {
        $dir ??= Atlas::getDir(Monarch::getPaths()->working);
        $this->composerFile = $this->findComposerJson($dir);
        $this->rootDir = $this->composerFile->getParent() ?? $dir;
        $this->binDir = $this->findBinDir();
    }

    protected function findComposerJson(
        Dir $dir
    ): File {
        $fallback = $dir->getFile('composer.json');

        do {
            $file = $dir->getFile('composer.json');

            if ($file->exists()) {
                return $file;
            }

            $dir = $dir->getParent();
        } while ($dir !== null);

        return $fallback;
    }

    protected function findBinDir(): Dir
    {
        if (
            (!$path = getenv('COMPOSER_BIN_DIR')) ||
            false === ($path = realpath($path))
        ) {
            $path = null;
        }

        $path = $path ??
            Coercion::tryString($this->getLocalManifest()->getConfig()['bin-dir']) ??
            'vendor/bin';

        return $this->rootDir->getDir($path);
    }




    public function getLocalManifest(): Manifest
    {
        if (!isset($this->manifest)) {
            $this->manifest = new Manifest($this->composerFile);
        }

        return $this->manifest;
    }


    public function setBinaryPath(
        string $binary,
        string|File $path
    ): void {
        if ($path instanceof File) {
            $path = $path->path;
        }

        if ($path !== $binary) {
            $this->paths[$binary] = $path;
        }
    }

    public function getBinaryPath(
        string $binary
    ): string {
        if (isset($this->paths[$binary])) {
            return $this->paths[$binary];
        }

        return Monarch::getPaths()->resolve($binary);
    }

    public function removeBinaryPath(
        string $binary
    ): void {
        unset($this->paths[$binary]);
    }

    public function hasBinaryPath(
        string $binary
    ): bool {
        return isset($this->paths[$binary]);
    }


    public function run(
        string $arg,
        string ...$args
    ): bool {
        $args = $this->reorderArguments([$arg, ...$args]);

        if (null === ($composer = $this->systemic->os->which('composer'))) {
            throw Exceptional::NotFound(
                message: 'Unable to locate global composer executable'
            );
        }

        array_unshift($args, $this->getBinaryPath('php'), $composer);
        return $this->systemic->run($args, $this->rootDir);
    }

    public function runGlobal(
        string $arg,
        string ...$args
    ): bool {
        return $this->run('global', $arg, ...$args);
    }



    protected const array ComposerPassthrough = [
        '--no-interaction',
        '--no-plugins',
        '--no-scripts'
    ];

    /**
     * @param array<string> $args
     * @return array<string>
     */
    protected function reorderArguments(
        array $args
    ): array {
        $escape = false;
        $output = $script = [];

        foreach ($args as $arg) {
            if ($arg === '--') {
                $escape = true;
                continue;
            }

            if (in_array($arg, self::ComposerPassthrough)) {
                $output[] = $arg;
            } elseif ($escape) {
                $script[] = $arg;
            } else {
                $output[] = $arg;
            }
        }

        if (!empty($script)) {
            $output[] = '--';
            $output = array_merge($output, $script);
        }

        return $output;
    }



    public function setConfig(
        string $key,
        string $value
    ): bool {
        return $this->run('config', $key, $value);
    }

    public function getConfig(
        string $key
    ): ?string {
        if (null === ($composer = $this->systemic->os->which('composer'))) {
            throw Exceptional::NotFound(
                message: 'Unable to locate global composer executable'
            );
        }

        $output = $this->systemic->capture([
            $this->getBinaryPath('php'),
            $composer,
            'config',
            '--absolute',
            $key
        ], $this->rootDir);

        if (
            !$output->wasSuccessful() ||
            !$output->hasOutput()
        ) {
            return null;
        }

        return trim((string)$output->getOutput());
    }



    public function hasScript(
        string $name
    ): bool {
        return $this->getLocalManifest()->hasScript($name);
    }

    /**
     * @return array<string, string>
     */
    public function getScripts(): array
    {
        return $this->getLocalManifest()->getScripts();
    }

    public function runScript(
        string $name,
        string ...$args
    ): bool {
        return $this->run('run-script', $name, '--', ...$args);
    }




    public function hasBin(
        string $name
    ): bool {
        return
            false === strpos($name, '/') &&
            $this->rootDir->getFile('vendor/bin/' . $name)->exists();
    }

    /**
     * @return array<File>
     */
    public function getBins(): array
    {
        return $this->binDir->listFiles();
    }

    public function runBin(
        string $name,
        string ...$args
    ): bool {
        return $this->run('exec', $name, '--', ...$args);
    }

    public function runGlobalBin(
        string $name,
        string ...$args
    ): bool {
        return $this->runGlobal('exec', $name, '--', ...$args);
    }




    public function install(
        string $name,
        string ...$other
    ): bool {
        return $this->run('require', $name, ...$other);
    }

    public function installDev(
        string $name,
        string ...$other
    ): bool {
        return $this->run(...['require', $name, ...$other, '--dev']);
    }

    public function installGlobal(
        string $name,
        string ...$other
    ): bool {
        return $this->runGlobal(...['require', $name, ...$other, '--with-all-dependencies']);
    }

    public function installDevGlobal(
        string $name,
        string ...$other
    ): bool {
        return $this->runGlobal(...['require', $name, ...$other, '--dev', '--with-all-dependencies']);
    }

    public function uninstall(
        string $name,
        string ...$other
    ): bool {
        return $this->run('remove', $name, ...$other);
    }

    public function uninstallDev(
        string $name,
        string ...$other
    ): bool {
        return $this->run(...['remove', $name, ...$other, '--dev']);
    }

    public function uninstallGlobal(
        string $name,
        string ...$other
    ): bool {
        return $this->runGlobal('remove', $name, ...$other);
    }

    public function uninstallDevGlobal(
        string $name,
        string ...$other
    ): bool {
        return $this->runGlobal(...['remove', $name, ...$other, '--dev']);
    }


    public function hasPackage(
        string $package
    ): bool {
        $manifest = $this->getLocalManifest();
        return $manifest->hasPackage($package);
    }



    /**
     * @return Tree<string|int|float|bool>
     */
    public function getExtra(): Tree
    {
        return $this->getLocalManifest()->getExtra();
    }
}
