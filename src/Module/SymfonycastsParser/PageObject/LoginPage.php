<?php declare(strict_types=1);

namespace App\Module\SymfonycastsParser\PageObject;

use App\Module\SymfonycastsParser\Webdriver\WebdriverFacadeInterface;
use Facebook\WebDriver\Exception\NoSuchElementException;

class LoginPage extends AbstractPageObject
{
    private const CSS_FORM_INPUT_LOGIN = '#email';
    private const CSS_FORM_INPUT_PASSWORD = '#password';
    private const CSS_FORM_SUBMIT_BUTTON = '.btn.btn-sm.btn-primary.text-center';

    private const CSS_HEADER_NAVBAR = '.navbar.nav-sfcasts-profile';
    private const CSS_HEADER_ACCOUNT_MENU = 'a[title*="Account Menu"]';

    private string $login;
    private string $password;

    public function __construct(WebdriverFacadeInterface $webdriver, string $login, string $password)
    {
        $this->login = $login;
        $this->password = $password;
        $this->webdriver = $webdriver;
    }

    public function login(): void
    {
        if (!$this->isAuthorized()) {
            $this->webdriver->fillInput(self::CSS_FORM_INPUT_LOGIN, $this->login);
            $this->webdriver->fillInput(self::CSS_FORM_INPUT_PASSWORD, $this->password);
            $this->webdriver->click(self::CSS_FORM_SUBMIT_BUTTON);
        }
    }

    private function isAuthorized(): bool
    {
        try {
            $this->webdriver->waitToBeClickable(self::CSS_HEADER_NAVBAR);
            $this->webdriver->findOne(self::CSS_HEADER_ACCOUNT_MENU);

            return true;
        } catch (NoSuchElementException $e) {
            return false;
        }
    }
}
