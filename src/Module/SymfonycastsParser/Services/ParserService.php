<?php

namespace App\Module\SymfonycastsParser\Services;

use App\Module\SymfonycastsParser\Services\Exceptions\ProcessingException;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Symfony\Component\Filesystem\Filesystem;

class ParserService
{
    private $filesystem;
    private $downloadDirAbsPath;
    private $webdriver;

    public function __construct(Filesystem $filesystem, string $downloadDirAbsPath)
    {
        $this->filesystem = $filesystem;
        $this->downloadDirAbsPath = $downloadDirAbsPath;
        $currentDownloadDirAbsPath = $this->downloadDirAbsPath . '/current_download_dir';
        $this->filesystem->mkdir($currentDownloadDirAbsPath);
        $host = 'http://localhost:4444';
        $options = new ChromeOptions();
        $options->setExperimentalOption("prefs", [
            "download.prompt_for_download" => false,
            "download.directory_upgrade" => true,
            "safebrowsing.enabled" => true,
            "download.default_directory" => $currentDownloadDirAbsPath,
            "plugins.always_open_pdf_externally" => true,
        ]);

        $caps = DesiredCapabilities::chrome();
        $caps->setCapability(ChromeOptions::CAPABILITY, $options);
        $this->webdriver = RemoteWebDriver::create($host, $caps);

        $this->login('login','pass');
    }

    public function parseCoursePage($courseUrl)
    {
        $coursePage = $this->webdriver->get($courseUrl);
        $courseTitleSelector = WebDriverBy::cssSelector('h1');
        $lessonUrlSelector = WebDriverBy::cssSelector('ul.chapter-list a');

        $courseTitleText = $coursePage->findElement($courseTitleSelector)->getText();

        /**
         * @RemoteWebElement[]
         */
        $lessonUrlElements = $coursePage->findElements($lessonUrlSelector);
        $lessonPageUrls = [];

        foreach ($lessonUrlElements as $lessonUrlElement) {
            $lessonPageUrls[] = $lessonUrlElement->getAttribute('href');
        }

        foreach ($lessonPageUrls as $index => $lessonPageUrl) {
            if ($index > 0) {
                $this->parseLessonPage($lessonPageUrl, true);
            } else {
                $this->parseLessonPage($lessonPageUrl, true, true, true);
            }
        }

        $olddir = $this->downloadDirAbsPath . '/current_download_dir';
        $newdir = $this->downloadDirAbsPath . '/' . $this->prepareStringForFilesystem($courseTitleText);
        $this->filesystem->rename($olddir, $newdir);
        $this->webdriver->close();
        return true;
    }

    private function parseLessonPage(
        $lessonPageUrl,
        $parseVideoFlag = true,
        $parseScriptFlag = false,
        $parseCodeArchiveFlag = false
    ) {
        $this->webdriver->get($lessonPageUrl);

        if ($parseVideoFlag) {
            $this->clickDropdownOptionAndDownload('.dropdown-menu a[data-download-type=video]');
        }
        if ($parseScriptFlag) {
            $this->clickDropdownOptionAndDownload('.dropdown-menu a[data-download-type=script]');
        }
        if ($parseCodeArchiveFlag) {
            $this->clickDropdownOptionAndDownload('.dropdown-menu a[data-download-type=code]');
        }

        $this->waitFilesToDownload();
    }

    private function prepareStringForFilesystem(string $string)
    {
        if (empty($string)) {
            throw new ProcessingException('String cannot be empty');
        }
        $processedString = str_replace(' ', '_', preg_replace('/[^a-z\d ]+/', '', strtolower($string)));
        return $processedString;
    }

    private function click(string $cssSelector)
    {
        $selectorObject = WebDriverBy::cssSelector($cssSelector);
        $this
            ->webdriver
            ->findElement($selectorObject)
            ->click();
    }

    private function waitToBeClickable(string $cssSelector)
    {
        $selectorObject = WebDriverBy::cssSelector($cssSelector);
        $this->webdriver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable($selectorObject)
        );
    }

    private function clickDropdownOptionAndDownload(string $cssSelector)
    {
        $this->waitToBeClickable('#downloadDropdown');
        $this->click('#downloadDropdown');
        $this->waitToBeClickable('.dropdown-menu.show');
        $this->click($cssSelector);
    }

    private function searchUnfinishedDownloadingFiles()
    {
        return glob($this->downloadDirAbsPath . '/current_download_dir/*.crdownload');
    }

    private function waitFilesToDownload()
    {
        do {
            var_dump($this->searchUnfinishedDownloadingFiles());
            echo date('H:i:s') . PHP_EOL;
            sleep(5);
        } while (!empty($this->searchUnfinishedDownloadingFiles()));
    }

    private function login($login, $password)
    {
        $this->webdriver->get('https://symfonycasts.com/login');
        $this->waitToBeClickable('#email');
        $this->click('#email');
        $this->webdriver->getKeyboard()->sendKeys($login);
        $this->waitToBeClickable('#password');
        $this->click('#password');
        $this->webdriver->getKeyboard()->sendKeys($password);
        $this->click('#_submit');
    }
}
