<?php

declare(strict_types=1);

namespace App\Module\SymfonycastsParser\PageObject;

use App\Module\SymfonycastsParser\Webdriver\WebdriverFacadeInterface;

abstract class AbstractPageObject
{
    protected WebdriverFacadeInterface $webdriver;

    public function openPage(string $url): void
    {
        $this->webdriver->openUrl($url);
    }
}
