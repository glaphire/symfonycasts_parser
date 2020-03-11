<?php

namespace App\Module\SymfonycastsParser\Services;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\KernelInterface;

class ParserService
{
    private $dir;

    public function __construct(KernelInterface $kernel)
    {
        $this->dir = $kernel->getProjectDir();
        $this->parserClient = new Client(HttpClient::createForBaseUri('https://symfonycasts.com/'));
    }

    public function parseCoursePage($courseUrl)
    {
        $crawler = $this->parserClient->request('GET', $courseUrl);
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

//        var_dump($linkToCodeArchive);
//        var_dump($linkToVideo);
//        var_dump($linkToCourseScript);

        return [
            'title' => $lessonTitle,
            'linkToCodeArchive' => $linkToCodeArchive,
            'linkToVideo' => $linkToVideo,
            'linkToCourseScript' => $linkToCourseScript,
        ];
    }
}