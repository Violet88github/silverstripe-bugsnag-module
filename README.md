# Bugsnag module for Silverstripe
[![CI](https://github.com/Violet88github/silverstripe-bugsnag-module/actions/workflows/cicd.yml/badge.svg)](https://github.com/Violet88github/silverstripe-bugsnag-module/actions/workflows/cicd.yml)
## Requirements

* SilverStripe ^4.0
* [silverstripe/framework](https://packagist.org/packages/silverstripe/framework)
* [bugsnag/bugsnag](https://packagist.org/packages/bugsnag/bugsnag)
* [guzzlehttp/guzzle](https://packagist.org/packages/guzzlehttp/guzzle)
* [silverstripe/admin](https://packagist.org/packages/silverstripe/admin)

## Dev requirements
* [phpunit/phpunit](https://packagist.org/packages/phpunit/phpunit)
* [squizlabs/php_codesniffer](https://packagist.org/packages/squizlabs/php_codesniffer)

## Installation
To install run the following command
```bash
composer require violet88/bugsnag-silverstripe
```

**Note:** Make sure the required modules are installed before using the module.

## License
See [License](license.md)

## Documentation
 * [Documentation readme](docs/en/readme.md)

## Configuration
For base use, add the following to your .env file

<strong>When running local, to prevent Bugsnag from being filled with errors, set BUGSNAG_ACTIVE to false OR do not declare it. (If not declared messages will also not be sent to Bugsnag.</strong>

```bash
BUGSNAG_API_KEY=<YOUR BUGSNAG API KEY>
BUGSNAG_STANDARD_SEVERITY=<STANDARD SEVERITY LEVEL FOR BUGSNAG (info OR warning OR error)>
BUGSNAG_ACTIVE=<true OR false, depending on whether bugsnag should be ACTIVE>
```
For using the BugsnagLogger as the standard error logger, add the following to your configuration yaml
```yaml
SilverStripe\Core\Injector\Injector:
  Psr\Log\LoggerInterface:
    calls:
      BugsnagHandler: [pushHandler, ['%$BugsnagHandler']]
  BugsnagHandler:
    class: Violet88\BugsnagModule\BugsnagLogger
    constructor:
      - '%$Violet88\BugsnagModule\Bugsnag'
```
For using the CLI command to sent your current release revision to Bugsnag, add the following to your routes yaml
```yaml
SilverStripe\Control\Director:
    rules:
        'bugsnag//build': 'Violet88\BugsnagModule\BugsnagController'
        'bugsnag//initial': 'Violet88\BugsnagModule\BugsnagController'
```

## Basic usage
For sending a basic error to Bugsnag, use the following code
```php
use Violet88\BugsnagModule\Bugsnag;
use Exception;
use SilverStripe\Core\Injector\Injector;

try{
    //do something
} catch (Exception $e) {
    $bugsnag = Injector::inst()->get(Bugsnag::class);
    $bugsnag->sendException($e);
}
```

## Maintainers
 * Sven van der Zwet <s.vanderzwet@student.avans.nl>

## Bugtracker
Bugs are tracked in the issues section of this repository. Before submitting an issue please read over
existing issues to ensure yours is unique.

If the issue does look like a new bug:

 - Create a new issue
 - Choose the issue template for 'Bugs'
 - Follow the instructions in the template

Please report security issues to the module maintainers directly. Please don't file security issues in the bugtracker.

## Development and contribution
If you would like to make contributions to the module please ensure you raise a pull request and discuss with the module maintainers.
