<?php

namespace App\Module\SymfonycastsParser\WebdriverFacade;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverElement;

interface WebdriverFacadeInterface
{
    public function click(string $cssSelector): void;

    public function waitToBeClickable(string $cssSelector): void;

    public function waitAndClick(string $cssSelector): void;

    public function openUrl(string $url): WebDriver;

    public function findOne(string $cssSelector): WebDriverElement;

    /**
     * @return WebDriverElement[]
     */
    public function findAll(string $cssSelector);

    public function fillInput(string $cssSelector, string $text): void;

    public function waitFilesToDownload(): void;

    public function quit(): void;
}
