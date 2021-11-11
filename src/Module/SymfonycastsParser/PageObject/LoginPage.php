<?php declare(strict_types=1);

namespace App\Module\SymfonycastsParser\PageObject;

use App\Module\SymfonycastsParser\Webdriver\WebdriverFacadeInterface;
use Facebook\WebDriver\Exception\NoSuchElementException;

class LoginPage extends AbstractPageObject
{
    //SLTCR means SELECTOR
    private const SLCTR_FORM_INPUT_LOGIN = '#email';
    private const SLCTR_FORM_INPUT_PASSWORD = '#password';
    private const SLCTR_FORM_SUBMIT_BUTTON = '#_submit';

    private const SLCTR_HEADER_NAVBAR = '.navbar';
    private const SLCTR_HEADER_ACCOUNT_MENU = 'a[title*="Account Menu"]';

    private string $login;
    private string $password;

    public function __construct(WebdriverFacadeInterface $webdriver, string $login, string $password)
    {
        $this->login = $login;
        $this->password = $password;
        $this->webdriver = $webdriver;
    }

    public function login()
    {
        if (!$this->isAuthorized()) {
            $this->webdriver->fillInput(self::SLCTR_FORM_INPUT_LOGIN, $this->login);
            $this->webdriver->fillInput(self::SLCTR_FORM_INPUT_PASSWORD, $this->password);
            $this->webdriver->click(self::SLCTR_FORM_SUBMIT_BUTTON);
        }
    }

    private function isAuthorized()
    {
        try {
            $this->webdriver->waitToBeClickable(self::SLCTR_HEADER_NAVBAR);
            $this->webdriver->findOne(self::SLCTR_HEADER_ACCOUNT_MENU);

            return true;
        } catch (NoSuchElementException $e) {
            return false;
        }
    }
}
