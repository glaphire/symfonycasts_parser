<?php

namespace App\Module\SymfonycastsParser\PageObject;

use App\Module\SymfonycastsParser\Service\WebdriverFacade\ChromeWebdriverFacade;

abstract class AbstractPageObject
{
    /**
     * @var ChromeWebdriverFacade $webdriver
     */
    protected $webdriver;

    public function openPage(string $url)
    {
        $this->webdriver->openUrl($url);
    }
}