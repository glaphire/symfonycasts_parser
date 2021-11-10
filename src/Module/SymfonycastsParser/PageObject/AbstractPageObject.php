<?php

namespace App\Module\SymfonycastsParser\PageObject;

use App\Module\SymfonycastsParser\WebdriverFacade\WebdriverFacadeInterface;

abstract class AbstractPageObject
{
    protected WebdriverFacadeInterface $webdriver;

    public function openPage(string $url)
    {
        $this->webdriver->openUrl($url);
    }
}
