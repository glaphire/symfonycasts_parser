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
    private $temporaryDownloadDirPath;
    private $downloadDirAbsPath;
    private $webdriver;
    private $smfCastsLogin;
    private $smfCastsPassword;

    public function __construct(Filesystem $filesystem, WebdriverFacade $webdriver, $downloadDirAbsPath, string $smfCastsLogin, string $smfCastsPassword)
    {
        $this->filesystem = $filesystem;
        $this->downloadDirAbsPath = $downloadDirAbsPath;

        $this->webdriver = $webdriver;
        $this->temporaryDownloadDirPath = $this->webdriver->getDownloadDirectoryAbsPath();
        $this->filesystem->mkdir($this->temporaryDownloadDirPath);

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

        $courseDirPath = $this->downloadDirAbsPath . '/' . $this->prepareStringForFilesystem($courseTitleText);
        $this->filesystem->rename($this->temporaryDownloadDirPath, $courseDirPath);
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
