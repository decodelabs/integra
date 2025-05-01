# Integra

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/integra?style=flat)](https://packagist.org/packages/decodelabs/integra)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/integra.svg?style=flat)](https://packagist.org/packages/decodelabs/integra)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/integra.svg?style=flat)](https://packagist.org/packages/decodelabs/integra)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/integra/integrate.yml?branch=develop)](https://github.com/decodelabs/integra/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/integra?style=flat)](https://packagist.org/packages/decodelabs/integra)

### Composer file inspector and front-end

Integra provides inspection tools for your composer.json file and a front end to control composer within your project.

---

## Installation

Install via Composer:

```bash
composer require decodelabs/integra
```

## Usage

Load a project to work from:

```php
use DecodeLabs\Integra\Project;

$project = new Project('path/to/project/');
```

If no path is specified, the current working directory will be used.
Integra will search back up the file tree for the nearest composer.json.


```php
echo $project->rootDir; // Parent or current dir containing composer.json
echo $project->binDir; // Bin dir relative to composer
echo $project->composerFile; // Location  of composer.json

$project->run('update'); // composer update
$project->runGlobal('update'); // composer global update
$project->runScript('my-script'); // composer run-script my-script
$project->runBin('phpstan', '--debug'); // composer exec phpstan -- --debug
$project->runGlobalBin('phpstan', '--debug'); // composer global exec phpstan -- --debug

if(!$project->hasPackage('package1')) {
    $project->install('package1', 'package2'); // composer require package1 package2
}

$project->installDev('package1', 'package2'); // composer require package1 package2 --dev
$project->installGlobal('package1', 'package2'); // composer global require package1 package2
$project->installDevGlobal('package1', 'package2'); // composer global require package1 package2 --dev
```

### Manifest

Access the composer.json manifest:

```php
$manifest = $project->getLocalManifest();
echo $manifest->getDescription();

foreach($manifest->getRequiredPackages() as $package) {
    echo $package->name;
}
```

See the Manifest.php class for full data access interface - it maps to the majority of the documented composer config options.

## Licensing

Integra is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
