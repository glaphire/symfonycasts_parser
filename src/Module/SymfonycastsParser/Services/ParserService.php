<?php

namespace App\Module\SymfonycastsParser\Services;

use App\Module\SymfonycastsParser\Services\Exceptions\ProcessingException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\ProcessManager\ChromeManager;

class ParserService
{
    private $filesystem;
    private $downloadDirAbsPath;
    private $browserClient;
    private $courseFolderAbsPath = null;

    public function __construct(Filesystem $filesystem, string $downloadDirAbsPath)
    {
        $this->filesystem = $filesystem;
        $this->downloadDirAbsPath = $downloadDirAbsPath;

        //$this->browserClient =  Client::createChromeClient(null, null, ['download.default_directory'=>'/home/dariia/Music']);
        $this->browserClient =  Client::createChromeClient(null, null, ['download.default_directory'=>'/home/dariia/my_scripts']);
    }

    public function parseCoursePage($courseUrl)
    {
        $crawler = $this->browserClient->request('GET', $courseUrl);
        $this->browserClient->manage()->window()->maximize();
        $courseTitle = $crawler->filter('h1')->text();
        $this->setCourseFolderAbsPath($courseTitle);

        $lessonPageUrls = $crawler
            ->filter('ul.chapter-list a')
            ->each(function (Crawler $node) {
                return $node->link();//->getUri();
            });

        foreach ($lessonPageUrls as $lessonPageUrl) {
            //var_dump($lessonLink);
            $this->parseLessonPage($lessonPageUrl);

            //TODO: delete break after writing lesson download
            break;
        }
        return true;

    }

    private function parseLessonPage($lessonPageUrl)
    {
        $this->browserClient->click($lessonPageUrl);
        $downloadDropdownButtonSelector = WebDriverBy::cssSelector('#downloadDropdown');
        $downloadDropdownListSelector = WebDriverBy::cssSelector('.dropdown-menu.show');

        $this->browserClient->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable($downloadDropdownButtonSelector)
        );

        $this
            ->browserClient
            ->findElement($downloadDropdownButtonSelector)
            ->click();

        $this->browserClient->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable($downloadDropdownListSelector)
        );

        $this
            ->browserClient
            ->findElement(WebDriverBy::cssSelector('.dropdown-menu a[data-download-type=code]'))
            ->click();
        //var_dump($select);

//        $linkToCodeArchive = $crawler->filter('.dropdown-menu a[data-download-type=code]')->attr('href');
//        $linkToVideo = $crawler->filter('.dropdown-menu a[data-download-type=video]')->attr('href');
//        $linkToCourseScript = $crawler->filter('.dropdown-menu a[data-download-type=script]')->attr('href');
  //      $this->browserClient->click($linkToCodeArchive);
        sleep(5);
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