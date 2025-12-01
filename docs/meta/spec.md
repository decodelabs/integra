# Integra — Package Specification

> **Cluster:** `tooling`
> **Language:** `php`
> **Milestone:** `m4`
> **Repo:** `https://github.com/decodelabs/integra`
> **Role:** Composer integration tools

## Overview

### Purpose

Integra provides inspection tools for `composer.json` files and a programmatic front-end to control Composer within PHP projects. It enables applications to:

- Inspect and read `composer.json` manifest data
- Execute Composer commands programmatically
- Manage package installation and removal (local and global)
- Run Composer scripts and binaries
- Access Composer configuration
- Discover project structure (root directory, bin directory, composer file location)

Integra simplifies Composer integration in PHP applications by providing a clean, object-oriented API for interacting with Composer projects, eliminating the need to manually parse JSON files or execute shell commands.

### Non-Goals

- Integra does not provide Composer package resolution or dependency solving
- It does not implement Composer's plugin system
- It does not provide package repository management
- It does not handle Composer authentication or credentials
- It does not provide Composer cache management
- It does not implement Composer's autoloader generation

## Role in the Ecosystem

### Cluster & Positioning

Integra belongs to the **tooling** cluster, providing development and build-time utilities. It sits alongside other tooling packages like Chronicle (release notes), Effigy (CLI entry point), and Hatch (code generation).

### Usage Contexts

Integra is used for:

- Build scripts and deployment tools that need to interact with Composer
- Development tools that inspect project dependencies
- CLI applications that manage Composer packages programmatically
- Automated testing and CI/CD pipelines
- Package management interfaces and dashboards
- Project scaffolding and setup tools

## Public Surface

### Key Types

- **`Project`** — Main class representing a Composer project. Provides methods for running Composer commands, managing packages, accessing manifest data, and discovering project structure.

- **`Manifest`** — Class for reading and inspecting `composer.json` files. Provides typed access to all major Composer configuration sections.

- **`Structure\Package`** — Data class representing a Composer package with name and version constraint.

- **`Structure\Author`** — Data class representing a package author with name, email, homepage URL, and role.

- **`Structure\Funding`** — Data class representing a funding source with type and URL.

### Main Entry Points

- **`Project::__construct(?Dir $dir, Systemic $systemic)`** — Creates a project instance. Searches for `composer.json` starting from the given directory (or current working directory) and walking up the directory tree.

- **`Project::getLocalManifest()`** — Returns the `Manifest` instance for the project's `composer.json`.

- **`Project::run(string $arg, string ...$args)`** — Executes a Composer command in the project directory.

- **`Project::runGlobal(string $arg, string ...$args)`** — Executes a global Composer command.

- **`Project::runScript(string $name, string ...$args)`** — Runs a Composer script by name.

- **`Project::runBin(string $name, string ...$args)`** — Executes a binary from `vendor/bin` via Composer.

- **`Project::runGlobalBin(string $name, string ...$args)`** — Executes a globally installed binary via Composer.

- **`Project::install(string $name, string ...$other)`** — Installs packages via `composer require`.

- **`Project::installDev(string $name, string ...$other)`** — Installs dev packages via `composer require --dev`.

- **`Project::installGlobal(string $name, string ...$other)`** — Installs global packages via `composer global require`.

- **`Project::uninstall(string $name, string ...$other)`** — Removes packages via `composer remove`.

- **`Project::hasPackage(string $package)`** — Checks if a package is required (production or dev).

- **`Project::hasScript(string $name)`** — Checks if a script is defined in `composer.json`.

- **`Project::hasBin(string $name)`** — Checks if a binary exists in `vendor/bin`.

- **`Project::getConfig(string $key)`** — Gets a Composer configuration value.

- **`Project::setConfig(string $key, string $value)`** — Sets a Composer configuration value.

- **`Manifest::reload()`** — Reloads the manifest from the `composer.json` file.

- **`Manifest::getName()`** — Gets the package name.

- **`Manifest::getDescription()`** — Gets the package description.

- **`Manifest::getRequiredPackages()`** — Gets all required packages as an array of `Package` objects.

- **`Manifest::getRequiredDevPackages()`** — Gets all dev-required packages as an array of `Package` objects.

- **`Manifest::hasPackage(string $package)`** — Checks if a package is required.

- **`Manifest::getScripts()`** — Gets all defined scripts as an array.

- **`Manifest::hasScript(string $name)`** — Checks if a script is defined.

## Dependencies

### Decode Labs

- **`atlas`** — Used for file and directory operations to locate and read `composer.json` files.

- **`coercion`** — Used for type coercion when reading configuration values from the manifest.

- **`collections`** — Used for `Tree` data structure to represent nested JSON data from `composer.json`.

- **`exceptional`** — Used for exception handling throughout the package.

- **`kairos`** — Used for date/time handling when reading release dates from the manifest.

- **`lucid`** — Used for value sanitization and validation.

- **`monarch`** — Used to determine working directory and resolve paths.

- **`systemic`** — Used to execute Composer commands and locate the Composer executable.

### External

- No external dependencies beyond Decode Labs packages.

## Behaviour & Contracts

### Invariants

- `composer.json` is searched by walking up the directory tree from the given directory
- If `composer.json` is not found, a fallback file reference is created
- Bin directory is determined from `COMPOSER_BIN_DIR` environment variable, `config.bin-dir`, or defaults to `vendor/bin`
- Composer executable is located via `Systemic::os->which('composer')`
- Manifest data is cached per `Project` instance until `reload()` is called
- Command arguments are reordered to place passthrough flags (`--no-interaction`, `--no-plugins`, `--no-scripts`) before script arguments
- Global commands automatically include `--with-all-dependencies` for install operations

### Input & Output Contracts

- **`Project::__construct(?Dir $dir, Systemic $systemic)`** — Accepts optional directory (defaults to current working directory) and `Systemic` instance. Locates `composer.json` and initializes project structure.

- **`Project::run(string $arg, string ...$args): bool`** — Executes Composer command with given arguments. Returns true on success, false on failure.

- **`Project::runGlobal(string $arg, string ...$args): bool`** — Executes global Composer command. Returns true on success, false on failure.

- **`Project::runScript(string $name, string ...$args): bool`** — Runs Composer script with optional arguments. Returns true on success, false on failure.

- **`Project::runBin(string $name, string ...$args): bool`** — Executes binary via `composer exec`. Returns true on success, false on failure.

- **`Project::install(string $name, string ...$other): bool`** — Installs packages via `composer require`. Returns true on success, false on failure.

- **`Project::installDev(string $name, string ...$other): bool`** — Installs dev packages. Returns true on success, false on failure.

- **`Project::uninstall(string $name, string ...$other): bool`** — Removes packages via `composer remove`. Returns true on success, false on failure.

- **`Project::hasPackage(string $package): bool`** — Returns true if package is in `require` or `require-dev`, false otherwise.

- **`Project::getConfig(string $key): ?string`** — Returns configuration value as string, or null if not found or command failed.

- **`Project::setConfig(string $key, string $value): bool`** — Sets configuration value. Returns true on success, false on failure.

- **`Manifest::reload(): void`** — Reloads manifest data from `composer.json` file.

- **`Manifest::getName(): ?string`** — Returns package name or null if not set.

- **`Manifest::getRequiredPackages(): array<string, Package>`** — Returns array of required packages keyed by package name.

- **`Manifest::hasPackage(string $package): bool`** — Returns true if package is required, false otherwise.

- **`Manifest::getScripts(): array<string, string>`** — Returns array of scripts keyed by script name.

## Error Handling

Integra uses the Exceptional pattern for error handling. Key exception types:

- **`NotFound`** — Thrown when Composer executable cannot be located via `which('composer')`.

Exceptions preserve the original service context and include detailed error messages.

## Configuration & Extensibility

### Extension Points

- **Binary Path Overrides** — Use `setBinaryPath()` to override binary locations for custom PHP or Composer executables.

- **Manifest Access** — Access raw manifest data via `Manifest::__get()` to read any `composer.json` field not covered by typed methods.

- **Custom Command Execution** — Use `run()` or `runGlobal()` with any Composer command and arguments.

### Configuration

- **Composer Binary** — Located automatically via `Systemic::os->which('composer')`. Can be overridden via binary path system.

- **PHP Binary** — Located via `Monarch::getPaths()->resolve('php')`. Can be overridden via `setBinaryPath('php', $path)`.

- **Bin Directory** — Determined from `COMPOSER_BIN_DIR` environment variable, `config.bin-dir` in `composer.json`, or defaults to `vendor/bin`.

- **Project Root** — Automatically discovered by searching for `composer.json` starting from given directory and walking up the tree.

## Interactions with Other Packages

- **Atlas** — Used for file and directory operations to locate and read `composer.json` files.

- **Systemic** — Used to execute Composer commands and locate the Composer executable via `os->which()`.

- **Monarch** — Used to determine working directory and resolve binary paths.

- **Collections** — Used for `Tree` data structure to represent nested JSON data.

- **Coercion** — Used for type conversion when reading configuration values.

- **Kairos** — Used for date/time parsing when reading release dates.

- **Lucid** — Used for value sanitization and validation.

## Usage Examples

### Basic Project Setup

```php
use DecodeLabs\Integra\Project;
use DecodeLabs\Monarch;
use DecodeLabs\Systemic;

$systemic = Monarch::getService(Systemic::class);
$project = new Project(null, $systemic); // Uses current working directory

echo $project->rootDir->path; // Project root directory
echo $project->binDir->path; // Bin directory (vendor/bin)
echo $project->composerFile->path; // composer.json location
```

### Inspecting Manifest

```php
$manifest = $project->getLocalManifest();

echo $manifest->getName(); // Package name
echo $manifest->getDescription(); // Package description

// Get required packages
foreach ($manifest->getRequiredPackages() as $package) {
    echo $package->name . ': ' . $package->version;
}

// Get scripts
$scripts = $manifest->getScripts();
foreach ($scripts as $name => $command) {
    echo "$name: $command";
}
```

### Running Composer Commands

```php
// Update dependencies
$project->run('update');

// Global update
$project->runGlobal('update');

// Run a script
$project->runScript('test', '--verbose');

// Execute a binary
$project->runBin('phpstan', '--debug');

// Execute global binary
$project->runGlobalBin('phpstan', '--debug');
```

### Package Management

```php
// Check if package is installed
if (!$project->hasPackage('decodelabs/exceptional')) {
    // Install package
    $project->install('decodelabs/exceptional');
}

// Install dev package
$project->installDev('phpunit/phpunit');

// Install multiple packages
$project->install('package1', 'package2', 'package3');

// Install global package
$project->installGlobal('hirak/prestissimo');

// Remove package
$project->uninstall('package1');

// Remove dev package
$project->uninstallDev('phpunit/phpunit');
```

### Configuration Management

```php
// Get configuration value
$binDir = $project->getConfig('bin-dir');

// Set configuration value
$project->setConfig('cache-dir', '/tmp/composer-cache');
```

### Binary Management

```php
// Check if binary exists
if ($project->hasBin('phpstan')) {
    $project->runBin('phpstan', 'analyse');
}

// Get all binaries
$bins = $project->getBins();
foreach ($bins as $bin) {
    echo $bin->name;
}

// Override binary path
$project->setBinaryPath('php', '/usr/local/bin/php8.4');
```

### Accessing Extra Data

```php
// Get extra data from composer.json
$extra = $project->getExtra();
$myConfig = $extra->myKey->as('string');

// Access raw manifest data
$manifest = $project->getLocalManifest();
$customField = $manifest->__get('custom-field')->as('string');
```

## Implementation Notes (for Contributors)

### Architecture

- **Project Discovery** — `composer.json` is located by walking up the directory tree from the given directory until found, or using a fallback reference if not found.

- **Manifest Caching** — Manifest data is loaded once per `Project` instance and cached until `reload()` is called. This avoids repeated file reads.

- **Command Execution** — Commands are executed via `Systemic::run()` with PHP and Composer binaries. Arguments are reordered to ensure passthrough flags come before script arguments.

- **Binary Path Resolution** — Binary paths can be overridden per binary name, falling back to Monarch path resolution.

- **Tree Data Structure** — Manifest uses `Collections\Tree` to represent nested JSON data, providing type-safe access via `as()` method.

- **Package Detection** — Package detection uses case-sensitive key lookup in `require` and `require-dev` sections.

- **Script Execution** — Scripts are executed via `composer run-script` with arguments passed after `--` separator.

- **Bin Execution** — Binaries are executed via `composer exec` with arguments passed after `--` separator.

### Performance Considerations

- Manifest data is cached per instance to avoid repeated JSON parsing
- Composer executable is located once per command execution
- File existence checks are minimized through caching

### Design Decisions

- **Automatic Discovery** — Walking up the directory tree to find `composer.json` allows flexible project structure without requiring explicit paths.

- **Type-Safe Access** — Using `Tree` with `as()` method provides type-safe access to manifest data while maintaining flexibility.

- **Command Abstraction** — High-level methods (`install()`, `runScript()`, etc.) provide convenient wrappers while `run()` allows arbitrary command execution.

- **Binary Path Overrides** — Custom binary path system allows integration with custom PHP or Composer installations.

- **Global Command Support** — Separate methods for global operations simplify global package management.

## Testing & Quality

**Code Quality:** 4.5/5 — Mature, well-structured codebase with comprehensive functionality and type safety.

**README Quality:** 4/5 — Good documentation with clear usage examples covering main use cases.

**Documentation:** 0/5 — No formal documentation beyond README.

**Tests:** 0/5 — No test suite currently.

See `composer.json` for supported PHP versions.

## Roadmap & Future Ideas

- Enhanced documentation and API reference
- Test suite implementation
- Support for Composer plugins
- Package repository inspection
- Dependency graph analysis
- Composer cache management
- Lock file inspection (`composer.lock`)
- Performance optimizations for large manifests

## References

- [Composer Documentation](https://getcomposer.org/doc/)
- [Composer Schema](https://getcomposer.org/doc/04-schema.md)
- [Decode Labs Chorus](https://github.com/decodelabs/chorus)
- [Integra Repository](https://github.com/decodelabs/integra)

