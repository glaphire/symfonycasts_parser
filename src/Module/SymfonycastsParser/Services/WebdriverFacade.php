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

    public function __construct(string $host, string $downloadDefaultDirectoryAbsPath)
    {
        $options = new ChromeOptions();
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
        return $this->webdriver->get($url);
    }

    public function findOne($cssSelector)
    {
        return $this->webdriver->findElement(WebDriverBy::cssSelector($cssSelector));
    }

    public function fillInput(string $cssSelector, string $text)
    {
        $this->waitAndClick($cssSelector);
        $this->webdriver->getKeyboard()->sendKeys($text);
    }
}
