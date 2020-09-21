<?php

namespace App\Module\SymfonycastsParser\PageObject;

use App\Module\SymfonycastsParser\Service\Exceptions\ProcessingException;
use App\Module\SymfonycastsParser\Service\WebdriverFacade\ChromeWebdriverFacade;

class PageFactory
{
    private $webdriver;
    private $login;
    private $password;

    public function __construct(ChromeWebdriverFacade $webdriver, $login, $password)
    {
        $this->webdriver = $webdriver;
        $this->login = $login;
        $this->password = $password;
    }

    private function allowedTypes()
    {
        return [
            'login',
            'course',
            'lesson',
        ];
    }

    public function create($pageType)
    {
        switch ($pageType) {
            case 'login':
                $page = new LoginPage($this->webdriver, $this->login, $this->password);
                break;
            case 'course':
                $page = new CoursePage($this->webdriver);
                break;
            case 'lesson':
                $page = new LessonPage($this->webdriver);
                break;
            default:
                $errorMessage = "Page with type '$pageType' not found."
                    . " Allowed types:" . implode(', ', $this->allowedTypes());
                throw new ProcessingException($errorMessage);
                break;
        }

        return $page;
    }
}