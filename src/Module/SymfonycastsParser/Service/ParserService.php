<?php

namespace App\Module\SymfonycastsParser\Service;

use App\Module\SymfonycastsParser\PageObject\PageFactory;
use App\Module\SymfonycastsParser\Service\Exceptions\ProcessingException;
use App\Module\SymfonycastsParser\WebdriverFacade\WebdriverFacadeInterface;
use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;

class ParserService
{
    private const COURSE_BASE_URL = 'https://symfonycasts.com/screencast';

    private $filesystem;
    private $webdriver;
    private $pageFactory;

    private $temporaryDownloadDirPath;
    private $downloadDirAbsPath;

    public function __construct(
        Filesystem $filesystem,
        WebdriverFacadeInterface $webdriver,
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

    public function parseCoursePage(string $courseUrl, int $startLessonNumber = 1)
    {
        $this->validateCourseUrl($courseUrl);
        $this->validateLessonNumber($startLessonNumber);

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

            if (1 == $lessonNumber) {
                $lessonPage->downloadCourseCodeArchive();
                $lessonPage->downloadCourseScript();
            }

            $lessonPage->downloadVideo();
        }

        $courseDirPath = $this->downloadDirAbsPath.'/'.$this->prepareStringForFilesystem($courseTitleText);
        $this->filesystem->rename($this->temporaryDownloadDirPath, $courseDirPath);
        $this->webdriver->quit();

        return true;
    }

    public function shutdownDownloadingProcess()
    {
        $this->webdriver->quit();
    }

    private function prepareStringForFilesystem(string $string)
    {
        if (empty($string)) {
            throw new ProcessingException('String cannot be empty');
        }

        return str_replace(' ', '_', preg_replace('/[^a-z\d ]+/', '', strtolower($string)));
    }

    private function validateCourseUrl(string $courseUrl)
    {
        if (0 !== strpos($courseUrl, self::COURSE_BASE_URL)) {
            throw new InvalidArgumentException('Course url should starts from '.self::COURSE_BASE_URL);
        }

        return true;
    }

    private function validateLessonNumber(int $lessonNumber)
    {
        if ($lessonNumber < 1) {
            throw new InvalidArgumentException('Lesson number should be less or equal 1');
        }

        return true;
    }
}
