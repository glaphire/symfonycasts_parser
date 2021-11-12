<?php

namespace App\Module\SymfonycastsParser\PageObject;

use App\Module\SymfonycastsParser\Webdriver\WebdriverFacadeInterface;
use Facebook\WebDriver\WebDriverElement;

class CoursePage extends AbstractPageObject
{
    private const SFCASTS_BASE_URL = 'https://symfonycasts.com';
    private const CSS_COURSE_HEADER_NAME = 'h1';
    private const CSS_LESSON_NAME = 'ul.chapter-list a';

    public function __construct(WebdriverFacadeInterface $webdriver)
    {
        $this->webdriver = $webdriver;
    }

    public function getCourseName(): string
    {
        return $this
            ->webdriver
            ->findOne(self::CSS_COURSE_HEADER_NAME)
            ->getText()
        ;
    }

    /**
     * @return WebDriverElement[]
     */
    public function getLessons()
    {
        return $this
            ->webdriver
            ->findAll(self::CSS_LESSON_NAME)
        ;
    }

    /**
     * @return string[]
     */
    public function getLessonsUrls(): array
    {
        $lessonPageUrls = [];
        $lessonElements = $this->getLessons();

        foreach ($lessonElements as $lessonElement) {
            $lessonPageUrls[] = self::SFCASTS_BASE_URL.$lessonElement->getAttribute('href');
        }

        return $lessonPageUrls;
    }
}
