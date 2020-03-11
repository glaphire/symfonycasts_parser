<?php

namespace App\Module\SymfonycastsParser\Services;

use App\Module\SymfonycastsParser\Services\Exceptions\ProcessingException;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;

class ParserService
{
    private $filesystem;
    private $downloadDirAbsPath;

    public function __construct(Filesystem $filesystem, string $downloadDirAbsPath)
    {
        $this->filesystem = $filesystem;
        $this->downloadDirAbsPath = $downloadDirAbsPath;
        $this->parserClient = new Client(HttpClient::createForBaseUri('https://symfonycasts.com/'));
    }

    public function parseCoursePage($courseUrl)
    {
        $crawler = $this->parserClient->request('GET', $courseUrl);
        $courseTitle = $crawler->filter('h1')->text();
        $courseDirName = $this->prepareStringForFilesystem($courseTitle);

        $lessonLinks = $crawler
            ->filter('ul.chapter-list a')
            ->each(function (Crawler $node) {
                return $node->link()->getUri();
            });

        foreach ($lessonLinks as $lessonLink) {
            $this->parseLessonPage($lessonLink);
            //TODO: delete break after writing lesson download
            break;
        }
        return true;
    }

    public function parseLessonPage($lessonUrl)
    {
        $crawler = $this->parserClient->request('GET', $lessonUrl);
        $lessonTitle = $crawler->filter('h1')->text();
        $linkToCodeArchive = $crawler->filter('.dropdown-menu a[data-download-type=code]')->attr('href');
        $linkToVideo = $crawler->filter('.dropdown-menu a[data-download-type=video]')->attr('href');
        $linkToCourseScript = $crawler->filter('.dropdown-menu a[data-download-type=script]')->attr('href');

        return [
            'title' => $lessonTitle,
            'linkToCodeArchive' => $linkToCodeArchive,
            'linkToVideo' => $linkToVideo,
            'linkToCourseScript' => $linkToCourseScript,
        ];
    }

    public function prepareStringForFilesystem($string)
    {
        if (empty($string)) {
            throw new ProcessingException('String cannot be empty');
        }
        $processedString = str_replace(' ', '_', preg_replace('/[^a-z\d ]+/', '', strtolower($string)));
        return $processedString;
    }
}