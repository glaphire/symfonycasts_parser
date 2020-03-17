<?php

namespace App\Module\SymfonycastsParser\Services;

use App\Module\SymfonycastsParser\Services\Exceptions\ProcessingException;
use Symfony\Component\Filesystem\Filesystem;

class ParserService
{
    private $filesystem;
    private $downloadDirAbsPath;

    public function __construct(Filesystem $filesystem, string $downloadDirAbsPath)
    {
        $this->filesystem = $filesystem;
        $this->downloadDirAbsPath = $downloadDirAbsPath;
    }

    public function parseCoursePage($courseUrl)
    {
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