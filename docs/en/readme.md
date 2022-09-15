# Documentation
## Setting up a bugsnag project
1. Go to your bugsnag dashboard and press 'NEW PROJECT'
2. For the question 'Where does your application run?' Choose 'Server'
3. For the question 'What platform or programming language are you using?' Choose 'PHP'
4. For the question 'What framework are you using?' Choose 'Other'
5. Name the project and press 'continue'
6. Make sure you add the following to your site config
```yaml
Violet88\BugsnagModule\Bugsnag:
  API_KEY: "<YOUR BUGSNAG API KEY>"
  STANDARD_SEVERITY: "<STANDARD SEVERITY LEVEL FOR BUGSNAG (info, warning, error>"
  active: <TRUE OR FALSE, depending on whether bugsnag should be active>
```
7. Test if the module is working by sending an exception to bugsnag using the following code
```php
$bugsnag = Injector::inst()->get(Bugsnag::class);
$bugsnag->sendException(new RuntimeException('Test exception'));
```
8. If everything is setup correctly, you'll see the exception in your bugsnag dashboard


## Catching an error and sending it to Bugsnag
```php
use Violet88\BugsnagModule\Bugsnag;

try{
    //do something
} catch (Exception $e) {
    $bugsnag = Injector::inst()->get(Bugsnag::class);
    $bugsnag->sendException($e);
}
```
