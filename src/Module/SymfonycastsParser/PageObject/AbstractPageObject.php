<?php

namespace App\Module\SymfonycastsParser\PageObject;

use App\Module\SymfonycastsParser\Services\WebdriverFacade;

abstract class AbstractPageObject
{
    /**
     * @var WebdriverFacade $webdriver
     */
    protected $webdriver;

    public function openPage(string $url)
    {
        $this->webdriver->openUrl($url);
    }
}