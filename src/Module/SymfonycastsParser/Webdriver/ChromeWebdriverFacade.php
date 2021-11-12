<?php

declare(strict_types=1);

namespace App\Module\SymfonycastsParser\Webdriver;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;

class ChromeWebdriverFacade implements WebdriverFacadeInterface
{
    /**
     * @var RemoteWebDriver|WebDriver
     */
    private $webdriver;
    private string $downloadDirectoryAbsPath;

    private const DOWNLOADING_RETRY_SECONDS = 5;
    private const CHROME_UNFINISHED_FILES_PATTERN = '*.crdownload';

    public function __construct(ChromeWebdriverFactory $webdriverFactory, string $downloadDirAbsPath)
    {
        $this->webdriver = $webdriverFactory->create();
        $this->downloadDirectoryAbsPath = $downloadDirAbsPath;
    }

    public function click(string $cssSelector): void
    {
        $this
            ->webdriver
            ->findElement(WebDriverBy::cssSelector($cssSelector))
            ->click()
        ;
    }

    public function waitToBeClickable(string $cssSelector): void
    {
        $this->webdriver
            ->wait()
            ->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector($cssSelector)
                )
            )
        ;
    }

    public function waitAndClick(string $cssSelector): void
    {
        $this->waitToBeClickable($cssSelector);
        $this->click($cssSelector);
    }

    /**
     * @return RemoteWebDriver|WebDriver
     */
    public function openUrl(string $url)
    {
        $this->webdriver = $this->webdriver->get($url);

        return $this->webdriver;
    }

    public function findOne(string $cssSelector): WebDriverElement
    {
        return $this
            ->webdriver
            ->findElement(WebDriverBy::cssSelector($cssSelector))
        ;
    }

    /**
     * @return WebDriverElement[]
     */
    public function findAll(string $cssSelector)
    {
        return $this
            ->webdriver
            ->findElements(WebDriverBy::cssSelector($cssSelector))
        ;
    }

    public function fillInput(string $cssSelector, string $text): void
    {
        $this->waitAndClick($cssSelector);
        $this
            ->webdriver
            ->getKeyboard()
            ->sendKeys($text)
        ;
    }

    /**
     * @return array|false
     */
    private function searchUnfinishedDownloadingFiles()
    {
        return glob($this->downloadDirectoryAbsPath.'/'.self::CHROME_UNFINISHED_FILES_PATTERN);
    }

    public function waitFilesToDownload(): void
    {
        do {
            sleep(self::DOWNLOADING_RETRY_SECONDS);
        } while (!empty($this->searchUnfinishedDownloadingFiles()));
    }

    public function getDownloadDirectoryAbsPath(): string
    {
        return $this->downloadDirectoryAbsPath;
    }

    public function quit(): void
    {
        $this->webdriver->quit();
    }
}
