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


    protected Manifest $manifest;


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
}
