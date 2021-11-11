<?php declare(strict_types=1);

namespace App\Module\SymfonycastsParser\Service;

use App\Module\SymfonycastsParser\PageObject\CoursePage;
use App\Module\SymfonycastsParser\PageObject\LessonPage;
use App\Module\SymfonycastsParser\PageObject\LoginPage;
use App\Module\SymfonycastsParser\PageObject\PageFactory;
use App\Module\SymfonycastsParser\Service\Exceptions\ProcessingException;
use App\Module\SymfonycastsParser\Webdriver\WebdriverFacadeInterface;
use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;

class ParserService
{
    private const SFCASTS_COURSE_BASE_URL = 'https://symfonycasts.com/screencast';
    private const SFCASTS_LOGIN_URL = 'https://symfonycasts.com/login';

    private const REGEX_FILENAME_EXCEPT_PATTERN = '/[^a-z\d ]+/';

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

    public function parseCoursePage(string $courseUrl, int $startLessonNumber = 1): bool
    {
        $this->validateCourseUrl($courseUrl);
        $this->validateLessonNumber($startLessonNumber);

        /** @var LoginPage $loginPage */
        $loginPage = $this->pageFactory->create('login');
        $loginPage->openPage(self::SFCASTS_LOGIN_URL);
        $loginPage->login();

        /** @var CoursePage $coursePage */
        $coursePage = $this->pageFactory->create('course');
        $coursePage->openPage($courseUrl);
        $courseTitleText = $coursePage->getCourseName();
        $lessonPageUrls = $coursePage->getLessonsUrls();

        $lessonPage = $this->pageFactory->create('lesson');

        $lessonsAmount = count($lessonPageUrls);

        if ($lessonsAmount < 1) {
            throw new ProcessingException("There are no lessons to parse");
        }

        for ($lessonNumber = $startLessonNumber; $lessonNumber <= $lessonsAmount; $lessonNumber++) {
            $index = $lessonNumber - 1;

            /** @var LessonPage $lessonPage */
            $lessonPage->openPage($lessonPageUrls[$index]);

            if (1 == $lessonNumber) {
                $lessonPage->downloadCourseCodeArchive();
                $lessonPage->downloadCourseScript();
            }

            $lessonPage->downloadVideo();
        }

        $courseDirPath = $this->downloadDirAbsPath.'/'.$this->prepareNameForFilesystem($courseTitleText);
        $this->filesystem->rename($this->temporaryDownloadDirPath, $courseDirPath);
        $this->webdriver->quit();

        return true;
    }

    public function shutdownDownloadingProcess(): void
    {
        $this->webdriver->quit();
    }

    private function prepareNameForFilesystem(string $name): string
    {
        if (empty($name)) {
            throw new ProcessingException('Name for a file cannot be empty');
        }

        return str_replace(' ', '_', preg_replace(self::REGEX_FILENAME_EXCEPT_PATTERN, '', strtolower($name)));
    }

    private function validateCourseUrl(string $courseUrl): bool
    {
        if (0 !== strpos($courseUrl, self::SFCASTS_COURSE_BASE_URL)) {
            throw new InvalidArgumentException('Course url should starts from '.self::SFCASTS_COURSE_BASE_URL);
        }

        return true;
    }

    private function validateLessonNumber(int $lessonNumber): bool
    {
        if ($lessonNumber < 1) {
            throw new InvalidArgumentException('Lesson number should be less or equal 1');
        }

        return true;
    }
}
