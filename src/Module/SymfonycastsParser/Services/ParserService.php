<?php

namespace App\Module\SymfonycastsParser\Services;

use App\Module\SymfonycastsParser\PageObject\PageFactory;
use App\Module\SymfonycastsParser\Services\Exceptions\ProcessingException;
use Symfony\Component\Filesystem\Filesystem;

class ParserService
{
    private $filesystem;
    private $webdriver;
    private $pageFactory;

    private $temporaryDownloadDirPath;
    private $downloadDirAbsPath;

    public function __construct(
        Filesystem $filesystem,
        WebdriverFacade $webdriver,
        PageFactory $pageFactory,
        string $downloadDirAbsPath
    ) {
        $this->filesystem = $filesystem;
        $this->webdriver = $webdriver;
        $this->pageFactory = $pageFactory;

        $this->downloadDirAbsPath = $downloadDirAbsPath;

        $this->temporaryDownloadDirPath = $this->webdriver->getDownloadDirectoryAbsPath();
        $this->filesystem->mkdir($this->temporaryDownloadDirPath);
    }

    public function parseCoursePage($courseUrl, $startLessonNumber = 1)
    {
        $loginPage = $this->pageFactory->create('login');
        $loginPage->openPage('https://symfonycasts.com/login');
        $loginPage->login();

        $coursePage = $this->pageFactory->create('course');
        $coursePage->openPage($courseUrl);
        $courseTitleText = $coursePage->getCourseName();
        $lessonPageUrls = $coursePage->getLessonsUrls();

        $lessonPage = $this->pageFactory->create('lesson');

        $lessonsAmount = count($lessonPageUrls);

        for ($lessonNumber = $startLessonNumber; $lessonNumber <= $lessonsAmount; $lessonNumber++) {
            $index = $lessonNumber - 1;
            $lessonPage->openPage($lessonPageUrls[$index]);

            if ($lessonNumber == 1) {
                $lessonPage->downloadCourseCodeArchive();
                $lessonPage->downloadCourseScript();
            }

            $lessonPage->downloadVideo();

        }

//        foreach ($lessonPageUrls as $lessonNumber => $lessonPageUrl) {
//            $lessonPage->openPage($lessonPageUrl);
//
//            if ($lessonNumber == 0) {
//                $lessonPage->downloadCourseCodeArchive();
//                $lessonPage->downloadCourseScript();
//            }
//
//            $lessonPage->downloadVideo();
//        }

        $courseDirPath = $this->downloadDirAbsPath . '/' . $this->prepareStringForFilesystem($courseTitleText);
        $this->filesystem->rename($this->temporaryDownloadDirPath, $courseDirPath);
        $this->webdriver->close();
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
