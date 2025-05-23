<?php

/**
 * @package Integra
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Integra;

use DateTime;
use DecodeLabs\Atlas;
use DecodeLabs\Atlas\File;
use DecodeLabs\Coercion;
use DecodeLabs\Collections\Tree;
use DecodeLabs\Integra\Structure\Author;
use DecodeLabs\Integra\Structure\Funding;
use DecodeLabs\Integra\Structure\Package;

class Manifest
{
    protected File $file;

    /**
     * @var Tree<string|int|float|bool>
     */
    protected Tree $data;

    public function __construct(
        File $file
    ) {
        $this->file = $file;
        $this->reload();
    }

    /**
     * Reload data
     */
    public function reload(): void
    {
        if (!$this->file->exists()) {
            /** @phpstan-ignore-next-line */
            $this->data = new Tree();
            return;
        }

        $json = json_decode($this->file->getContents(), true);
        /**
         * @var Tree<string|int|float|bool> $tree
         * @phpstan-ignore-next-line
         */
        $tree = new Tree($json);
        $this->data = $tree;
    }


    /**
     * Get node from tree
     *
     * @return Tree<string|int|float|bool>
     */
    public function __get(
        string $name
    ): Tree {
        return $this->data->__get($name);
    }


    /**
     * Get package name
     */
    public function getName(): ?string
    {
        return $this->data->name->as('?string');
    }

    /**
     * Get package description
     */
    public function getDescription(): ?string
    {
        return $this->data->description->as('?string');
    }

    /**
     * Get package type
     */
    public function getType(): ?string
    {
        return $this->data->type->as('?string');
    }

    /**
     * Get package keywords
     *
     * @return array<string>
     */
    public function getKeywords(): ?array
    {
        return $this->data->keywords->as('string[]');
    }

    /**
     * Get homepage URL
     */
    public function getHomepageUrl(): ?string
    {
        return $this->data->homepage->as('?string');
    }

    /**
     * Get readme path
     */
    public function getReadmeFile(): ?File
    {
        if (null === ($path = $this->data->readme->as('?string'))) {
            return null;
        }

        return Atlas::file(dirname((string)$this->file) . '/' . $path);
    }

    /**
     * Get release date
     */
    public function getReleaseDate(): ?DateTime
    {
        return $this->data->time->as('?date');
    }

    /**
     * Get license
     *
     * @return string|array<string>|null
     */
    public function getLicense(): string|array|null
    {
        if ($this->data->license->count()) {
            $arr = $this->data->license->toArray();
            return Coercion::tryString(array_pop($arr));
        }

        return $this->data->license->as('?string');
    }

    /**
     * Get authors
     *
     * @return array<Author>
     */
    public function getAuthors(): array
    {
        $output = [];

        foreach ($this->data->authors as $node) {
            $output[] = new Author(
                $node->name->as('?string'),
                $node->email->as('?string'),
                $node->homepage->as('?string'),
                $node->role->as('?string')
            );
        }

        return $output;
    }

    /**
     * Get support info
     *
     * @return array<string,string>|null
     */
    public function getSupport(): ?array
    {
        if (!isset($this->data->support)) {
            return null;
        }

        /** @var array<string,string> */
        $output = $this->data->support->as('string[]');
        return $output;
    }

    /**
     * Get funding sources
     *
     * @return array<Funding>
     */
    public function getFundingSources(): array
    {
        $output = [];

        foreach ($this->data->funding as $node) {
            $output[] = new Funding(
                $node->type->as('string'),
                $node->url->as('string')
            );
        }

        return $output;
    }

    /**
     * Get require list
     *
     * @return array<string, Package>
     */
    public function getRequiredPackages(): array
    {
        return $this->getPackageMap('require');
    }

    /**
     * Get require-dev list
     *
     * @return array<string, Package>
     */
    public function getRequiredDevPackages(): array
    {
        return $this->getPackageMap('require-dev');
    }

    /**
     * Has package in either list
     */
    public function hasPackage(
        string $package
    ): bool {
        return
            isset($this->data->require->{$package}) ||
            isset($this->data->{'require-dev'}->{$package});
    }

    /**
     * Get installed packages
     *
     * @return array<string, Package>
     */
    public function getInstalledPackages(): array
    {
        return array_merge(
            $this->getRequiredPackages(),
            $this->getRequiredDevPackages()
        );
    }

    /**
     * Get required extensions
     *
     * @return array<string>
     */
    public function getRequiredExtensions(): array
    {
        $output = [];

        foreach ($this->getInstalledPackages() as $package) {
            $matches = [];

            if (!preg_match('/^ext-([a-zA-Z0-9-_]+)$/', $package->name, $matches)) {
                continue;
            }

            $name = $matches[1];
            $output[] = $name;
        }

        return $output;
    }

    /**
     * Get package version
     */
    public function getPackageVersion(
        string $package
    ): ?string {
        return
            $this->data->require->__get($package)->as('?string') ??
            $this->data->__get('require-dev')->__get($package)->as('?string');
    }

    /**
     * Get conflict list
     *
     * @return array<string,Package>
     */
    public function getConflictingPackages(): array
    {
        return $this->getPackageMap('conflict');
    }

    /**
     * Get replace list
     *
     * @return array<string,Package>
     */
    public function getReplacedPackages(): array
    {
        return $this->getPackageMap('replace');
    }

    /**
     * Get provide list
     *
     * @return array<string, Package>
     */
    public function getProvidedPackages(): array
    {
        return $this->getPackageMap('provide');
    }

    /**
     * Get require list
     *
     * @return array<string, Package>
     */
    protected function getPackageMap(
        string $dataKey
    ): array {
        $output = [];

        foreach ($this->data->__get($dataKey) as $key => $node) {
            $output[(string)$key] = new Package(
                (string)$key,
                $node->as('string')
            );
        }

        return $output;
    }

    /**
     * Get suggested packages
     *
     * @return array<string,string>
     */
    public function getSuggestedPackages(): array
    {
        /** @var array<string,string> */
        $output = $this->data->suggest->as('string[]');
        return $output;
    }

    /**
     * Get autoload config
     *
     * @return Tree<string|int|float|bool>
     */
    public function getAutoloadConfig(): Tree
    {
        return $this->data->autoload;
    }


    /**
     * Get minimum stability
     */
    public function getMinimumStability(): string
    {
        return $this->data->__get('minimum-stability')->as('string', [
            'default' => 'stable'
        ]);
    }

    /**
     * Get prefer stable
     */
    public function shouldPreferStable(): bool
    {
        return $this->data->__get('prefer-stable')->as('bool', [
            'default' => false
        ]);
    }

    /**
     * Get repository config
     *
     * @return Tree<string|int|float|bool>
     */
    public function getRepositoryConfig(): Tree
    {
        return $this->data->repositories;
    }

    /**
     * Get config
     *
     * @return Tree<string|int|float|bool>
     */
    public function getConfig(): Tree
    {
        return $this->data->config;
    }

    /**
     * Get extra
     *
     * @return Tree<string|int|float|bool>
     */
    public function getExtra(): Tree
    {
        return $this->data->extra;
    }

    /**
     * Get bin files
     *
     * @return array<string>
     */
    public function getBinFiles(): array
    {
        $output = [];

        foreach ($this->data->bin as $node) {
            $output[] = $node->as('string');
        }

        return $output;
    }

    /**
     * Get scripts
     *
     * @return array<string,string>
     */
    public function getScripts(): array
    {
        /** @var array<string,string> */
        $output = $this->data->scripts->as('string[]');
        return $output;
    }

    /**
     * Has script
     */
    public function hasScript(
        string $name
    ): bool {
        return isset($this->data->scripts->{$name});
    }

    /**
     * Get archive config
     *
     * @return Tree<string|int|float|bool>
     */
    public function getArchiveConfig(): Tree
    {
        return $this->data->archive;
    }

    /**
     * Is abandoned
     */
    public function isAbandoned(): bool
    {
        return $this->data->abandoned->as('bool', [
            'default' => false
        ]);
    }

    /**
     * Get non-feature branches
     *
     * @return array<string>
     */
    public function getNonFeatureBranches(): array
    {
        return $this->data->__get('non-feature-branches')->as('string[]');
    }
}
