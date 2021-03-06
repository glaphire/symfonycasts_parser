<?php

namespace App\Module\SymfonycastsParser\PageObject;

use App\Module\SymfonycastsParser\WebdriverFacade\WebdriverFacadeInterface;
use Facebook\WebDriver\Exception\NoSuchElementException;

class LoginPage extends AbstractPageObject
{
    public const FORM_INPUT_LOGIN = '#email';
    public const FORM_INPUT_PASSWORD = '#password';
    public const FORM_SUBMIT_BUTTON = '#_submit';

    private $login;
    private $password;

    public function __construct(WebdriverFacadeInterface $webdriver, $login, $password)
    {
        $this->login = $login;
        $this->password = $password;
        $this->webdriver = $webdriver;
    }

    public function login()
    {
        if (!$this->isAuthorized()) {
            $this->webdriver->fillInput(self::FORM_INPUT_LOGIN, $this->login);
            $this->webdriver->fillInput(self::FORM_INPUT_PASSWORD, $this->password);
            $this->webdriver->click(self::FORM_SUBMIT_BUTTON);
        }
    }

    private function isAuthorized()
    {
        try {
            $this->webdriver->waitToBeClickable('.navbar');
            $this->webdriver->findOne('a[title*="Account Menu"]');

            return true;
        } catch (NoSuchElementException $e) {
            return false;
        }
    }
}
