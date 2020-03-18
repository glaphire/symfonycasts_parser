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
    private $courseFolderAbsPath = null;

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
        ]);

        $caps = DesiredCapabilities::chrome();
        $caps->setCapability(ChromeOptions::CAPABILITY, $options);
        $this->webdriver = RemoteWebDriver::create($host, $caps);
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
        foreach ($lessonUrlElements as $lessonUrlElement) {
            $lessonPageUrl = $lessonUrlElement->getAttribute('href');
            $this->parseLessonPage($lessonPageUrl);

            //TODO: delete break after writing lesson download
            break;
        }
        return true;

    }

    private function parseLessonPage($lessonPageUrl)
    {
        $lessonPage = $this->webdriver->get($lessonPageUrl);
        $downloadDropdownButtonSelector = WebDriverBy::cssSelector('#downloadDropdown');
        $downloadDropdownListSelector = WebDriverBy::cssSelector('.dropdown-menu.show');

        $this->webdriver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable($downloadDropdownButtonSelector)
        );

        $this
            ->webdriver
            ->findElement($downloadDropdownButtonSelector)
            ->click();

        $this->webdriver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable($downloadDropdownListSelector)
        );

        $this
            ->webdriver
            ->findElement(WebDriverBy::cssSelector('.dropdown-menu a[data-download-type=code]'))
            ->click();
//        do {
//
//        } while ();
        /*
         * do {

        filesize1 = f.length();  // check file size
        Thread.sleep(5000);      // wait for 5 seconds
        filesize2 = f.length();  // check file size again

        } while (length2 != length1);
         */
       // sleep(15);
        //$this->webdriver->close();

//        $linkToCodeArchive = $crawler->filter('.dropdown-menu a[data-download-type=code]')->attr('href');
//        $linkToVideo = $crawler->filter('.dropdown-menu a[data-download-type=video]')->attr('href');
//        $linkToCourseScript = $crawler->filter('.dropdown-menu a[data-download-type=script]')->attr('href');
  //      $this->browserClient->click($linkToCodeArchive);

    }

    private function prepareStringForFilesystem(string $string)
    {
        if (empty($string)) {
            throw new ProcessingException('String cannot be empty');
        }
        $processedString = str_replace(' ', '_', preg_replace('/[^a-z\d ]+/', '', strtolower($string)));
        return $processedString;
    }

    private function setCourseFolderAbsPath($courseTitle)
    {
        $preparedCourseName = $this->prepareStringForFilesystem($courseTitle);
        $this->courseFolderAbsPath = $this->downloadDirAbsPath . '/' . $preparedCourseName;
        //TODO: move to separate method
        $this->filesystem->mkdir($this->courseFolderAbsPath);
    }
}