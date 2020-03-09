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

        return $lessonLinks;
    }

    public function parseLessonPage($lessonUrl)
    {
        $crawler = $this->parserClient->request('GET', $lessonUrl);
        $lessonTitle = $crawler->filter('h1')->text();

        return [
            'title' => $lessonTitle,
        ];
    }
}