# Bugsnag module for Silverstripe
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


**Note:** Make sure the required modules are installed before using the module.

## License
See [License](license.md)

## Documentation
 * [Documentation readme](docs/en/readme.md)

## Configuration
For base use add the following to your configuration yaml
```yaml
Violet88\BugsnagModule\Bugsnag:
  API_KEY: "<YOUR BUGSNAG API KEY>"
  STANDARD_SEVERITY: "<STANDARD SEVERITY LEVEL FOR BUGSNAG (info, warning, error>"
  ACTIVE: <true OR false, depending on whether bugsnag should be ACTIVE>
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
    'bugsnag_build': 'Violet88\BugsnagModule\BugsnagController'
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
