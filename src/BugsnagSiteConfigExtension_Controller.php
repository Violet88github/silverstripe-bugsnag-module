<?php

namespace Violet88\BugsnagModule;

use Psr\Log\LoggerInterface;
use RuntimeException;
use SilverStripe\Admin\CMSMenu;
use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\SiteConfig\SiteConfig;

class BugsnagSiteConfigExtension_Controller extends LeftAndMain {
  private static $url_segment = 'admin/settings/bugsnag';

  private static $allowed_actions = [
    'doForceError',
    'doForceWarning',
    'doForceInfo',
  ];

  public function init() {
    parent::init();

    CMSMenu::remove_menu_item('Violet88-BugsnagModule-BugsnagSiteConfigExtension_Controller');
  }

  public function doForceError($request) {
    $bugsnag = Injector::inst()->get(Bugsnag::class);
    $bugsnag->sendException(new RuntimeException("Force BugSnag error"), 'error');

    return $this->redirect('/admin/settings#Root_Bugsnag');
  }

  public function doForceWarning($request) {
    $bugsnag = Injector::inst()->get(Bugsnag::class);
    $bugsnag->sendException(new RuntimeException("Force BugSnag warning"), 'warning');

    return $this->redirect('/admin/settings#Root_Bugsnag');
  }

  public function doForceInfo($request) {
    $bugsnag = Injector::inst()->get(Bugsnag::class);
    $bugsnag->sendException(new RuntimeException("Force BugSnag info"), 'info');

    return $this->redirect('/admin/settings#Root_Bugsnag');
  }
}
