<?php

namespace App\Module\SymfonycastsParser\Services;

use App\Module\SymfonycastsParser\Services\Exceptions\ProcessingException;
use Symfony\Component\Filesystem\Filesystem;

class ParserService
{
    private $filesystem;
    private $downloadDirAbsPath;
    private $webdriver;

    public function __construct(Filesystem $filesystem, string $downloadDirAbsPath, string $smfCastsLogin, string $smfCastsPassword)
    {
        $this->filesystem = $filesystem;
        $this->downloadDirAbsPath = $downloadDirAbsPath;
        $currentDownloadDirAbsPath = $this->downloadDirAbsPath . '/current_download_dir';
        $this->filesystem->mkdir($currentDownloadDirAbsPath);
        $host = 'http://localhost:4444';
        $this->webdriver = new WebdriverFacade($host, $currentDownloadDirAbsPath);

        $this->login($smfCastsLogin, $smfCastsPassword);
    }

    public function parseCoursePage($courseUrl)
    {
        $lessonPageUrls = [];
        $this->webdriver->openUrl($courseUrl);
        $courseTitleText = $this->webdriver->findOne('h1')->getText();
        $lessonUrlElements = $this->webdriver->findAll('ul.chapter-list a');

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
        $this->webdriver->openUrl($lessonPageUrl);

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

    private function clickDropdownOptionAndDownload(string $cssSelector)
    {
        $this->webdriver->waitToBeClickable('#downloadDropdown');
        $this->webdriver->click('#downloadDropdown');
        $this->webdriver->waitToBeClickable('.dropdown-menu.show');
        $this->webdriver->click($cssSelector);
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
        $this->webdriver->openUrl('https://symfonycasts.com/login');
        $this->webdriver->fillInput('#email', $login);
        $this->webdriver->fillInput('#password', $password);
        $this->webdriver->click('#_submit');
    }
}
