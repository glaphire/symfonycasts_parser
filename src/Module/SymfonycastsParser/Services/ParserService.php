<?php

namespace App\Module\SymfonycastsParser\Services;

use App\Module\SymfonycastsParser\Services\Exceptions\ProcessingException;
use Goutte\Client;
use GuzzleHttp\Client as CurlClient;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;

class ParserService
{
    private $curlClient;
    private $downloadDirAbsPath;
    private $filesystem;
    private $parserService;

    public function __construct(Filesystem $filesystem, string $downloadDirAbsPath)
    {
        $this->curlClient = new CurlClient();
        $this->downloadDirAbsPath = $downloadDirAbsPath;
        $this->filesystem = $filesystem;
        $this->parserClient = new Client(HttpClient::createForBaseUri('https://symfonycasts.com/'));
    }

    public function parseCoursePage($courseUrl)
    {
        $crawler = $this->parserClient->request('GET', $courseUrl);
        $courseTitle = $crawler->filter('h1')->text();
        $courseDirName = $this->prepareStringForFilesystem($courseTitle);
        $courseDirAbsPath = $this->downloadDirAbsPath . "/" . $courseDirName;
        $this->filesystem->mkdir($courseDirAbsPath);

        $lessonLinks = $crawler
            ->filter('ul.chapter-list a')
            ->each(function (Crawler $node) {
                return $node->link()->getUri();
            });

        foreach ($lessonLinks as $lessonLink) {
            $this->parseLessonPage($lessonLink, $courseDirAbsPath);

            //TODO: delete break after writing lesson download
            break;
        }
        return true;
    }

    public function parseLessonPage($lessonUrl, $dirPath)
    {
        $crawler = $this->parserClient->request('GET', $lessonUrl);
        $cookiers = $this->parserClient->getCookieJar();

        $lessonTitle = $crawler->filter('h1')->text();

        $linkToCodeArchive = $crawler->filter('.dropdown-menu a[data-download-type=code]')->attr('href');
        $linkToVideo = $crawler->filter('.dropdown-menu a[data-download-type=video]')->attr('href');
        $linkToCourseScript = $crawler->filter('.dropdown-menu a[data-download-type=script]')->attr('href');

        //$this->downloadFile($linkToCodeArchive, $dirPath, 'code_archive', 'zip');
        //$this->downloadFile($linkToCourseScript, $dirPath, 'course_script', 'pdf');
        $this->downloadFile($linkToVideo, $dirPath, $lessonTitle, 'mp4');

        return [
            'title' => $lessonTitle,
            'linkToCodeArchive' => $linkToCodeArchive,
            'linkToVideo' => $linkToVideo,
            'linkToCourseScript' => $linkToCourseScript,
        ];
    }

    public function prepareStringForFilesystem(string $string)
    {
        if (empty($string)) {
            throw new ProcessingException('String cannot be empty');
        }
        $processedString = str_replace(' ', '_', preg_replace('/[^a-z\d ]+/', '', strtolower($string)));
        return $processedString;
    }

    public function downloadFile(string $url, string $destinationPath, string $filename, string $extension) {
        $resource = fopen("$destinationPath/$filename.$extension", 'w');
        //get link from Cloudfare response
        $realDownloadLink = $this->parserClient->request('GET', $url)->getUri();
        $responseCode = $this->curlClient->request('GET', $realDownloadLink, ['sink' => $resource])->getStatusCode();

        if ($responseCode !== 200) {
            throw new ProcessingException("Couldn't download file with url $url: server responded with status $responseCode");
        }
    }
}