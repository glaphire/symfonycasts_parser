<?php

namespace App\Module\SymfonycastsParser\PageObject;

use App\Module\SymfonycastsParser\Services\ChromeWebdriverFacade;

class CoursePage extends AbstractPageObject
{
    public const COURSE_HEADER_NAME = 'h1';

    public function __construct(ChromeWebdriverFacade $webdriver)
    {
        $this->webdriver = $webdriver;
    }

    public function getCourseName()
    {
        return $this->webdriver->findOne(self::COURSE_HEADER_NAME)->getText();
    }

    public function getLessons()
    {
        return $this->webdriver->findAll('ul.chapter-list a');
    }

    public function getLessonsUrls()
    {
        $lessonPageUrls = [];
        $lessonElements = $this->getLessons();

        foreach ($lessonElements as $lessonElement) {
            $lessonPageUrls[] = $lessonElement->getAttribute('href');
        }

        return $lessonPageUrls;
    }
}
