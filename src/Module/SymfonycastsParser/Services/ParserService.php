<?php

namespace App\Module\SymfonycastsParser\Services;

use App\Module\SymfonycastsParser\PageObject\LessonPage;
use App\Module\SymfonycastsParser\Services\Exceptions\ProcessingException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Symfony\Component\Filesystem\Filesystem;

class ParserService
{
    private $filesystem;
    private $downloadDirAbsPath;
    private $webdriver;
    private $smfCastsLogin;
    private $smfCastsPassword;

    public function __construct(Filesystem $filesystem, WebdriverFacade $webdriver, $downloadDirAbsPath, string $smfCastsLogin, string $smfCastsPassword)
    {
        $this->filesystem = $filesystem;
        $this->downloadDirAbsPath = $downloadDirAbsPath;
        $currentDownloadDirAbsPath = $this->downloadDirAbsPath . '/current_download_dir';
        $this->filesystem->mkdir($currentDownloadDirAbsPath);
        $this->webdriver = $webdriver;
        $this->smfCastsLogin = $smfCastsLogin;
        $this->smfCastsPassword = $smfCastsPassword;
    }

    public function parseCoursePage($courseUrl)
    {
        $lessonPageUrls = [];
        $this->webdriver->openUrl($courseUrl);
        if(!$this->isAuthorized()) {
            $this->login($this->smfCastsLogin, $this->smfCastsPassword);
            $this->webdriver->openUrl($courseUrl);
        }
        $courseTitleText = $this->webdriver->findOne('h1')->getText();
        $lessonUrlElements = $this->webdriver->findAll('ul.chapter-list a');

        foreach ($lessonUrlElements as $lessonUrlElement) {
            $lessonPageUrls[] = $lessonUrlElement->getAttribute('href');
        }

        foreach ($lessonPageUrls as $index => $lessonPageUrl) {
            $lessonPage = new LessonPage($this->webdriver);
            if ($index > 0) {
                $lessonPage->parseLessonPage($lessonPageUrl, true);
            } else {
                $lessonPage->parseLessonPage($lessonPageUrl, true, true, true);
            }
        }

        $olddir = $this->downloadDirAbsPath . '/current_download_dir';
        $newdir = $this->downloadDirAbsPath . '/' . $this->prepareStringForFilesystem($courseTitleText);
        $this->filesystem->rename($olddir, $newdir);
        //$this->webdriver->close();
        return true;
    }

    private function prepareStringForFilesystem(string $string)
    {
        if (empty($string)) {
            throw new ProcessingException('String cannot be empty');
        }
        $processedString = str_replace(' ', '_', preg_replace('/[^a-z\d ]+/', '', strtolower($string)));
        return $processedString;
    }

    private function login($login, $password)
    {
        $this->webdriver->openUrl('https://symfonycasts.com/login');
        $this->webdriver->fillInput('#email', $login);
        $this->webdriver->fillInput('#password', $password);
        $this->webdriver->click('#_submit');
    }

    private function isAuthorized()
    {
        try {
            $this->webdriver->waitToBeClickable('.navbar');
            $this->webdriver->findOne('a[title*="Account Menu"]');
            return true;
        } catch (NoSuchElementException $e) {
            return false;
        }
    }
}
