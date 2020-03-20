<?php

namespace App\Module\SymfonycastsParser\Services;

use App\Module\SymfonycastsParser\PageObject\CoursePage;
use App\Module\SymfonycastsParser\PageObject\LessonPage;
use App\Module\SymfonycastsParser\PageObject\LoginPage;
use App\Module\SymfonycastsParser\Services\Exceptions\ProcessingException;
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
        $loginPage = new LoginPage($this->webdriver, $this->smfCastsLogin, $this->smfCastsPassword);
        $loginPage->login();

        $coursePage = new CoursePage($this->webdriver, $courseUrl);

        $courseTitleText = $coursePage->getCourseName();
        $lessonPageUrls = $coursePage->getLessonsUrls();

        foreach ($lessonPageUrls as $lessonNumber => $lessonPageUrl) {
            $lessonPage = new LessonPage($this->webdriver, $lessonPageUrl);

            if ($lessonNumber == 0) {
                $lessonPage->downloadCourseCodeArchive();
                $lessonPage->downloadCourseScript();
            }

            $lessonPage->downloadVideo();
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
}
