<?php

namespace App\Module\SymfonycastsParser\PageObject;

use App\Module\SymfonycastsParser\Services\WebdriverFacade;
use Facebook\WebDriver\Exception\NoSuchElementException;

class LoginPage extends BasePageObject
{
    private $login;
    private $password;

    public const FORM_INPUT_LOGIN = '#email';
    public const FORM_INPUT_PASSWORD = '#password';
    public const FORM_SUBMIT_BUTTON = '#_submit';

    public const LOGIN_PAGE_URL = 'https://symfonycasts.com/login';

    public function __construct(WebdriverFacade $webdriver, $login, $password)
    {
        $this->login = $login;
        $this->password = $password;

        $webdriver->openUrl(self::LOGIN_PAGE_URL);
        parent::__construct($webdriver);
    }

    public function login()
    {
        if(!$this->isAuthorized()) {
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