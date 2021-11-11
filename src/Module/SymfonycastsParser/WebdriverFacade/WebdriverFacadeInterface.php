<?php

namespace App\Module\SymfonycastsParser\WebdriverFacade;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverElement;

interface WebdriverFacadeInterface
{
    public function click(string $cssSelector): void;

    public function waitToBeClickable(string $cssSelector): void;

    public function waitAndClick(string $cssSelector): void;

    /**
     * @return RemoteWebDriver|WebDriver
     */
    public function openUrl(string $url);

    public function findOne(string $cssSelector): WebDriverElement;

    /**
     * @return WebDriverElement[]
     */
    public function findAll(string $cssSelector);

    public function fillInput(string $cssSelector, string $text): void;

    public function waitFilesToDownload(): void;

    public function getDownloadDirectoryAbsPath(): string;

    public function quit(): void;
}
