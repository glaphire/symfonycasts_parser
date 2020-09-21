<?php

namespace App\Module\SymfonycastsParser\Services;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class ChromeWebdriverFacade
{
    private $webdriver;
    private $downloadDirectoryAbsPath;

    private const DOWNLOADING_RETRY_SECONDS = 5;
    private const CHROME_UNFINISHED_FILES_PATTERN = "*.crdownload";

    //TODO: move browser setup to separate method
    public function __construct(string $host, string $downloadDirAbsPath, string $profileDirAbsPath)
    {
        $options = new ChromeOptions();

        $options->addArguments([
            "--user-data-dir=$profileDirAbsPath",
        ]);

        $this->downloadDirectoryAbsPath = $downloadDirAbsPath;

        $options->setExperimentalOption("prefs", [
            "download.prompt_for_download" => false,
            "download.directory_upgrade" => true,
            "safebrowsing.enabled" => true,
            "download.default_directory" => $this->downloadDirectoryAbsPath,
            "plugins.always_open_pdf_externally" => true,
        ]);

        $caps = DesiredCapabilities::chrome();
        $caps->setCapability(ChromeOptions::CAPABILITY, $options);
        $this->webdriver = RemoteWebDriver::create($host, $caps);
    }

    public function click(string $cssSelector)
    {
        $this
            ->webdriver
            ->findElement(WebDriverBy::cssSelector($cssSelector))
            ->click();
    }

    public function waitToBeClickable(string $cssSelector)
    {
        $this->webdriver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector($cssSelector))
        );
    }

    public function waitAndClick(string $cssSelector)
    {
        $this->waitToBeClickable($cssSelector);
        $this->click($cssSelector);
    }

    public function openUrl(string $url)
    {
        $this->webdriver = $this->webdriver->get($url);
        return $this->webdriver;
    }

    public function findOne($cssSelector)
    {
        return $this->webdriver->findElement(WebDriverBy::cssSelector($cssSelector));
    }

    public function findAll($cssSelector)
    {
        return $this->webdriver->findElements(WebDriverBy::cssSelector($cssSelector));
    }

    public function fillInput(string $cssSelector, string $text)
    {
        $this->waitAndClick($cssSelector);
        $this->webdriver->getKeyboard()->sendKeys($text);
    }

    private function searchUnfinishedDownloadingFiles()
    {
        return glob($this->downloadDirectoryAbsPath . '/' . self::CHROME_UNFINISHED_FILES_PATTERN);
    }

    public function waitFilesToDownload()
    {
        do {
            sleep(self::DOWNLOADING_RETRY_SECONDS);
        } while (!empty($this->searchUnfinishedDownloadingFiles()));
    }

    public function getDownloadDirectoryAbsPath()
    {
        return $this->downloadDirectoryAbsPath;
    }

    public function close()
    {
        return $this->webdriver->close();
    }
}
