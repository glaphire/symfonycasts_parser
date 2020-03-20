<?php

namespace App\Module\SymfonycastsParser\Services;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class WebdriverFacade
{
    private $webdriver;
    private $downloadDefaultDirectoryAbsPath;

    public function __construct(string $host, string $downloadDirAbsPath, string $profileDirectoryAbsPath)
    {
        $options = new ChromeOptions();

        $options->addArguments([
            "--user-data-dir=$profileDirectoryAbsPath",
        ]);

        $downloadDefaultDirectoryAbsPath = $downloadDirAbsPath . "/current_download_dir";

        $options->setExperimentalOption("prefs", [
            "download.prompt_for_download" => false,
            "download.directory_upgrade" => true,
            "safebrowsing.enabled" => true,
            "download.default_directory" => $downloadDefaultDirectoryAbsPath,
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
        return glob($this->downloadDefaultDirectoryAbsPath . '/current_download_dir/*.crdownload');
    }

    public function waitFilesToDownload()
    {
        do {
            var_dump($this->searchUnfinishedDownloadingFiles());
            echo date('H:i:s') . PHP_EOL;
            sleep(5);
        } while (!empty($this->searchUnfinishedDownloadingFiles()));
    }
}