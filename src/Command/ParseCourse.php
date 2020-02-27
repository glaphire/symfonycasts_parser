<?php

namespace App\Command;

use Goutte\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\KernelInterface;

class ParseCourse extends Command
{
    protected static $defaultName = 'app:parse-course';
    private $dir;
    private $parserClient;

    public function __construct(KernelInterface $kernel, string $name = null)
    {
        $this->dir = $kernel->getProjectDir();
        $this->parserClient = new Client(HttpClient::createForBaseUri('https://symfonycasts.com/'));
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->addArgument('course_url', InputArgument::REQUIRED, 'URL of course video list')
            ->setDescription('Parses all files from course.')
            ->setHelp('Downloads videos and files from course url provided as argument.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $courseUrl = $input->getArgument('course_url');
        $lessonLinks = $this->parseCoursePage($courseUrl);
        foreach ($lessonLinks as $link) {
            $i = 1;
            $data = $this->parseLessonPage($link);
            echo "#$i: link: $link, title: {$data['title']}" . PHP_EOL;
            $i++;
        }
        return 0;
    }

    private function parseCoursePage($courseUrl)
    {
        $crawler = $this->parserClient->request('GET', $courseUrl);
        $lessonLinks = $crawler
            ->filter('ul.chapter-list a')
            ->each(function (Crawler $node) {
                return $node->link()->getUri();
            });

        return $lessonLinks;
    }

    private function parseLessonPage($lessonUrl)
    {
        $crawler = $this->parserClient->request('GET', $lessonUrl);
        $lessonTitle = $crawler->filter('h1')->text();

        //var_dump($lessonTitle);
        return [
            'title' => $lessonTitle,
        ];
    }
}