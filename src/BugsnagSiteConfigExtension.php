<?php

namespace Violet88\BugsnagModule;

use App\Extensions\SiteConfigExtension;
use LeKoala\CmsActions\CustomAction;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\Debug;

class BugsnagSiteConfigExtension extends Extension {

  public function updateCMSFields(FieldList $fields) {

    $bugsnagState = $this->getBugsnagActiveState();
    $bugsnagReleaseStage = $this->getBugsnagReleaseStage();

    $fields->addFieldsToTab('Root.Bugsnag', [
      HeaderField::create('Bugsnag settings'),

      FieldGroup::create([
        TextField::create('BugsnagActiveState', 'Bugsnag active state')
          ->setValue($bugsnagState['code'])
          ->setAttribute('style', $bugsnagState['style'])
          ->setReadonly(true),

        TextField::create('BugsnagReleaseStage', 'Bugsnag release stage')
          ->setValue($bugsnagReleaseStage['code'])
          ->setAttribute('style', $bugsnagReleaseStage['style'])
          ->setReadonly(true)
      ]),

      HeaderField::create('Bugsnag testing buttons'),
      FieldGroup::create([
        FormAction::create('doForceError', 'Force error')
          ->setAttribute('onclick', "location.href='admin/settings/bugsnag/doForceError'")
          ->addExtraClass('btn action btn-danger'),
        FormAction::create('doForceWarning', 'Force warning')
          ->setAttribute('onclick', "location.href='admin/settings/bugsnag/doForceWarning'")
          ->addExtraClass('btn action btn-warning'),
        FormAction::create('doForceInfo', 'Force info')
          ->setAttribute('onclick', "location.href='admin/settings/bugsnag/doForceInfo'")
          ->addExtraClass('btn action btn-info')
      ])

    ]);

    return $fields;
  }

  private function getBugsnagActiveState() {
    $state = Environment::getEnv('BUGSNAG_ACTIVE');

    switch($state) {
      case false:
        return [
          "code" => "ENV variable 'BUGSNAG_ACTIVE' not set",
          "style" => $this->getInputStyling('bad'),
        ];
      case "":
        return [
          "code" => "Active state not set",
          "style" => $this->getInputStyling('warning'),
        ];
      case "false":
        return [
          "code" => "Bugsnag inactive",
          "style" => $this->getInputStyling('bad'),
        ];
      case "true":
        return [
          "code" => "Bugsnag active",
          "style" => $this->getInputStyling('good'),
        ];
    }
  } 

  private function getBugsnagReleaseStage() {
    $state = Environment::getEnv('BUGSNAG_RELEASE_STAGE');

    switch($state) {
      case false:
        return [
          "code" => "ENV variable 'BUGSNAG_RELEASE_STAGE' not set, default to 'development' stage.",
          "style" => $this->getInputStyling('bad'),
        ];
      case "":
        return [
          "code" => "Release stage not set, default to 'development' stage.",
          "style" => $this->getInputStyling('warning'),
        ];
      default:
      return [
        "code" => $state,
        "style" => $this->getInputStyling('good'),
      ];
    }
  } 

  private function getInputStyling($type) {
    switch($type) {
      case 'good': return "background-color: #d1e7dd; color: #0f5132; border-color: #badbcc;";
      case 'warning': return "background-color: #fff3cd; color: #664d03; border-color: #ffecb5;";
      case 'bad': return "background-color: #f8d7da; color: #842029; border-color: #f5c2c7;";
    }
  }
}