<?php

namespace App\Module\SymfonycastsParser\Webdriver;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;

class ChromeWebdriverFactory
{
    private string $host;

    private ?string $downloadDirAbsPath = null;

    private string $profileDirAbsPath;

    public function __construct(string $host, ?string $downloadDirAbsPath, string $profileDirAbsPath)
    {
        $this->host = $host;
        $this->downloadDirAbsPath = $downloadDirAbsPath;
        $this->profileDirAbsPath = $profileDirAbsPath;
    }

    /**
     * @return RemoteWebDriver|WebDriver
     */
    public function create()
    {
        $options = new ChromeOptions();

        $options->addArguments([
            "--user-data-dir={$this->profileDirAbsPath}",
        ]);

        $options->setExperimentalOption('prefs', [
            'download.prompt_for_download' => false,
            'download.directory_upgrade' => true,
            'safebrowsing.enabled' => true,
            'download.default_directory' => $this->downloadDirAbsPath,
            'plugins.always_open_pdf_externally' => true,
        ]);

        $caps = DesiredCapabilities::chrome();

        //ChromeOptions::CAPABILITY is deprecated, but without it it's impossible to set capabities
        $caps->setCapability(ChromeOptions::CAPABILITY, $options);
        return RemoteWebDriver::create($this->host, $caps);
    }
}