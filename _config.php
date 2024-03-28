<?php

use SilverStripe\Admin\CMSMenu;
use Violet88\BugsnagModule\BugsnagSiteConfigExtension_Controller;

CMSMenu::remove_menu_class(BugsnagSiteConfigExtension_Controller::class);

// You need this file if you don't have anything in the _config folder. If that folder exists
// and is not empty then you can delete this file.
