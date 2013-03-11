<?php

use Behat\Behat\Context\BehatContext;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

use Behat\MinkExtension\Context\MinkContext;

class FeatureContext extends MinkContext
{
    /**
     * @Given /^I am logged out$/
     */
    public function iAmLoggedOut()
    {
        $this->getSession()->visit("/user/logout");
    }

}