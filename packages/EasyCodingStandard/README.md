# The Easiest Way to Use Any Coding Standard

[![Build Status](https://img.shields.io/travis/Symplify/EasyCodingStandard/master.svg?style=flat-square)](https://travis-ci.org/Symplify/EasyCodingStandard)
[![Downloads total](https://img.shields.io/packagist/dt/symplify/easy-coding-standard.svg?style=flat-square)](https://packagist.org/packages/symplify/easy-coding-standard)
[![Subscribe](https://img.shields.io/badge/subscribe-to--releases-green.svg?style=flat-square)](https://libraries.io/packagist/symplify%2Feasy-coding-standard)

**Used by [Shopsys](https://github.com/shopsys/coding-standards), [Nette](https://github.com/nette/coding-standard), [Sylius](https://github.com/SyliusLabs/CodingStandard), [LMC](https://github.com/lmc-eu/php-coding-standard) and [Sunfox](https://github.com/sunfoxcz/coding-standard).**

![ECS-Run](docs/run-and-fix.gif)

## Features

- Use [PHP_CodeSniffer || PHP-CS-Fixer](https://www.tomasvotruba.cz/blog/2017/05/03/combine-power-of-php-code-sniffer-and-php-cs-fixer-in-3-lines/) - anything you like
- **2nd run under few seconds** with caching
- [Skipping files](#ignore-what-you-cant-fix) for specific checkers
- [Prepared checker sets](#use-prepared-checker-sets) - PSR2, Symfony, Common, Symplify and more...

## Install

```bash
composer require --dev symplify/easy-coding-standard
```

## Usage

### 1. Create Configuration and Setup Checkers

Create an `easy-coding-standard.yml` in your root directory and add [Sniffs](https://github.com/squizlabs/PHP_CodeSniffer) or [Fixers](https://github.com/FriendsOfPHP/PHP-CS-Fixer) you'd love to use.

Let's start with the most common one - `array()` => `[]`:

```yaml
services:
    PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer:
        syntax: short
```

### 2. Run in CLI

```bash
# dry
vendor/bin/ecs check src

# fix
vendor/bin/ecs check src --fix
```

*Tip: Do you want [autocomplete too](https://github.com/Haehnchen/idea-php-symfony2-plugin/issues/1153)? Just use Symfony Plugin*

![ECS-Run](docs/yaml-autocomplete.gif)

## More Features

### Use Prepared Checker Sets

There are prepared sets in [`/config` directory](config) that you can use:

- [clean-code.yml](config/clean-code.yml)
- [common.yml](config/common.yml)
- [php71.yml](config/php71.yml)
- [psr2.yml](config/psr2.yml)
- ...

You pick config in CLI with `--config`:

```bash
vendor/bin/ecs check src --config vendor/symplify/easy-coding-standard/config/clean-code.yml
```

**Too long? Try `--level` shortcut**:

```bash
vendor/bin/ecs check src --level clean-code
```

or include more of them in config:

```yaml
# easy-coding-standard.yml
imports:
    - { resource: 'vendor/symplify/easy-coding-standard/config/clean-code.yml' }
    - { resource: 'vendor/symplify/easy-coding-standard/config/psr2.yml' }
```

In case of [custom coding standard and include](https://github.com/lmc-eu/php-coding-standard/pull/6/files#diff-a8b950982764fcffe4b7b3acd261cf91) e.g. `psr2.yml` form this package, you might want to use `%vendor_dir%` or `%current_working_dir%` for:

```yaml
# lmc-coding-standard.yml
imports:
    - { resource: '%vendor_dir%/symplify/easy-coding-standard/config/psr2.yml' }
    # or
    - { resource: '%current_working_dir%/vendor/symplify/easy-coding-standard/config/psr2.yml' }
```

That would load file always from vendor dir, no matter where you are.

### Exclude Checkers

What if you add `symfony.yml` set, but don't like `PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer`?

```yaml
imports:
    - { resource: 'vendor/symplify/easy-coding-standard/config/symfony.yml' }

parameters:
    exclude_checkers:
        - 'PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer'
```

### Ignore What You Can't Fix

Sometimes, checker finds an error in code that inherits from code you can't change.

No worries! Just **skip checker for this file**:

```yaml
parameters:
    skip:
        SlevomatCodingStandard\Sniffs\TypeHints\TypeHintDeclarationSniff:
            # relative path to file (you can copy this from error report)
            - 'packages/EasyCodingStandard/packages/SniffRunner/src/File/File.php'

            # or multiple files by path to match against "fnmatch()"
            - '*packages/CodingStandard/src/Sniffs/*/*Sniff.php'
```

You can also skip specific codes that you know from PHP_CodeSniffer:

```yaml
parameters:
    skip:
        # code to skip for all files
        SlevomatCodingStandard\Sniffs\TypeHints\TypeHintDeclarationSniff.UselessDocComment: ~

        # code to skip for specific files/patterns
        SlevomatCodingStandard\Sniffs\TypeHints\TypeHintDeclarationSniff.MissingTraversableParameterTypeHintSpecification:
            -  '*src/Form/Type/*Type.php'
```

Or just 2 files?

```yml
parameters:
    exclude_files:
        # generated files
        - 'lib/PhpParser/Parser/Php5.php'
        - 'lib/PhpParser/Parser/Php7.php'
        # or with fnmatch() pattern
        - '*/lib/PhpParser/Parser/Php*.php'
```

### Do you need to Include tests, `*.php`, `*.inc` or `*.phpt` files?

Normally you want to exclude these files, because they're not common code - they're just test files or dummy fixtures. In case you want to check them as well, **you can**.

Let's say you want to include `*.phpt` files.

- Create a class in `src/Finder/PhpAndPhptFilesProvider.php`
- Implement `Symplify\EasyCodingStandard\Contract\Finder\CustomSourceProviderInterface`
- Register it as services to `easy-coding-standard.yml` like any other Symfony service:

    ```yaml
    services:
        App\Finder\PhpAndPhptFilesProvider: ~
    ```

The `PhpAndPhptFilesProvider` might look like this:

```php
namespace App\Finder;

use IteratorAggregate;
use Nette\Utils\Finder;
use SplFileInfo;
use Symplify\EasyCodingStandard\Contract\Finder\CustomSourceProviderInterface;

final class PhpAndPhptFilesProvider implements CustomSourceProviderInterface
{
    /**
     * @param string[] $source
     */
    public function find(array $source): IteratorAggregate
    {
        # $source is "source" argument passed in CLI
        # inc CLI: "vendor/bin/ecs check /src" => here: ['/src']
        return Finder::find('*.php', '*.phpt')->in($source);
    }
}
```

*Don't forget to autoload it with composer.*

**Use any Finder you like**: [Nette\Finder](https://doc.nette.org/en/finder) or [Symfony\Finder](https://symfony.com/doc/current/components/finder.html).

### FAQ

**How to show all loaded checkers?**

```bash
vendor/bin/ecs show

vendor/bin/ecs show --config ...
```

**How to clear cache?**

```bash
vendor/bin/ecs check src --clear-cache
```

**How about changing cache directory?**

```yaml
parameters:
    cache_directory: .ecs_cache # defaults to sys_get_temp_dir() . '/_easy_coding_standard/_changed_files_detector_tests'
```

**Can I use tabs or `\r\n` line endings?**

```yaml
parameters:
    indentation: 'tab' # "spaces" by default
    line_ending: '\r\n' # PHP_EOL by default
```

**How do I find the slowest checkers?**

```bash
vendor/bin/ecs check src --show-performance
```

## Contributing

Send [issue](https://github.com/Symplify/Symplify/issues) or [pull-request](https://github.com/Symplify/Symplify/pulls) to main repository.
