# Documentation
## Setting up a bugsnag project
1. Go to your bugsnag dashboard and press 'NEW PROJECT'
2. For the question 'Where does your application run?' Choose 'Server'
3. For the question 'What platform or programming language are you
4. using?' Choose 'PHP'
5. For the question 'What framework are you using?' Choose 'Other'
6. Name the project and press 'continue'
7. Make sure you add the following to your env file
```bash
BUGSNAG_API_KEY=<YOUR BUGSNAG API KEY>
BUGSNAG_STANDARD_SEVERITY=<STANDARD SEVERITY LEVEL FOR BUGSNAG (info, warning, error>
BUGSNAG_ACTIVE=<true OR false, depending on whether bugsnag should be ACTIVE>
```
8. Test if the module is working by sending an exception to bugsnag using the following code
```php
$bugsnag = Injector::inst()->get(Bugsnag::class);
$bugsnag->sendException(new RuntimeException('Test exception'));
```
9. If everything is setup correctly, you'll see the exception in your bugsnag dashboard


## Catching an error and sending it to Bugsnag
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
## Sending current release revision to Bugsnag
### Through code
```php
use Violet88\BugsnagModule\Bugsnag;
use SilverStripe\Core\Injector\Injector;

$bugsnag = Injector::inst()->get(Bugsnag::class);
// Arguments are in the following order: repository, revision, provider, builderName
$bugsnag->notifyBuild('https://github.com/Violet88github/bugsnag-module', '1.0.0', 'github', 'Sven');
```
### Through CLI
1. Add the following to your routes yaml
```yaml
SilverStripe\Control\Director:
  rules:
    'bugsnag_build': 'Violet88\BugsnagModule\BugsnagController'
```
2. After that run one of the following commands
#### Using Sake
run the following command in your project root, replacing these arguments with your own.
```bash
vendor/bin/sake bugsnag_build "repository=https://github.com/Violet88github/bugsnag-module&revision=1.0.0&provider=github&builderName=Sven&appVersion=1.0.0"
```
#### Using the Silverstripe cli script
run the following command in your project root, replacing these arguments with your own.
```bash
php vendor/silverstripe/framework/cli-script.php bugsnag_build repository=https://github.com/Violet88github/bugsnag-module revision=1.0.0 provider=github builderName=sven appVersion=1.0.0
```

## Catching unhandled exceptions
1. Add the following to your configuration yaml
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
2. Now any unhandled errors will be sent to Bugsnag

## Setting up a Bugsnag error message
Using switches or custom metadata, you can configure the Bugsnag error message. The following standard switches are available and are chainable.
### User
If a user is logged in, you can use the addUserInfo() switch to add their user info to the Bugsnag message. In the code this would work as follows:
```php
$bugsnag = Injector::inst()->get(Bugsnag::class);
$bugsnag
    ->addUserInfo() //Add false as an argument to remove user info
    ->sendException(new RuntimeException('Test exception'));
```
### Version
If you have set a project version in your composer.json, you can use the addVersion() switch to add or remove the version in the Bugsnag message. In the code this would work as follows:
```php
$bugsnag = Injector::inst()->get(Bugsnag::class);
$bugsnag
    ->addVersion() //Add false as an argument toremove the version
    ->sendException(new RuntimeException('Test exception'));
```
### Installed packages (without versions)
To add a simple list of packages that are installed in the project, you can use the addInstalledPackages() switch. This list does not include versions, if you wish to include those continue to the next switch. In the code this would work as follows:
```php
$bugsnag = Injector::inst()->get(Bugsnag::class);
$bugsnag
    ->addPackages() //Add false as an argument to remove the list of packages
    ->sendException(new RuntimeException('Test exception'));
```
This will automatically retrieve the version as set in your composer.json
### Installed packages (with versions)
To add a list of packages that are installed in the project, including their versions, you can use the addPackagesWithVersions() switch. In the code this would work as follows:
```php
$bugsnag = Injector::inst()->get(Bugsnag::class);
$bugsnag
    ->addPackagesWithVersions() //Add false as an argument to remove the list of packages
    ->sendException(new RuntimeException('Test exception'));
```
## Custom metadata
The following custom metadata functions are available
#### App version
```php
$bugsnag = Injector::inst()->get(Bugsnag::class);
$bugsnag
    ->setAppVersion('1.0.0') /* If this version corresponds to an existing release version in Bugsnag, it will be findable under that release in the dashboard */
    ->sendException(new RuntimeException('Test exception'));
```
#### Release Stage
```php
$bugsnag = Injector::inst()->get(Bugsnag::class);
$bugsnag
    ->setReleaseStage('production')
    ->sendException(new RuntimeException('Test exception'));
```

#### Endpoint
This can be useful when you are using an on premise Bugsnag server
```php
$bugsnag = Injector::inst()->get(Bugsnag::class);
$bugsnag
    ->setEndpoint('https://bugsnag.example.com')
->sendException(new RuntimeException('Test exception'));
```

#### Last resort
If all else falls short, you can always add your own key => value pair to the metadata.
```php
$bugsnag = Injector::inst()->get(Bugsnag::class);
$bugsnag
    ->addExtraOption('key', 'value')
->sendException(new RuntimeException('Test exception'));
```
To remove certain metadata, you can use the removeExtraOption() function
```php
$bugsnag = Injector::inst()->get(Bugsnag::class);
$bugsnag
    ->removeExtraOption('key')
->sendException(new RuntimeException('Test exception'));
```
