<?php

namespace App\Module\SymfonycastsParser\PageObject;

use App\Module\SymfonycastsParser\Services\WebdriverFacade;

class BasePageObject
{
    protected $webdriver;

    public function __construct(WebdriverFacade $webdriver)
    {
        $this->webdriver = $webdriver;
    }
}