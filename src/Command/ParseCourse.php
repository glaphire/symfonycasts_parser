<?php

namespace App\Command;

use App\Module\SymfonycastsParser\Services\ParserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCourse extends Command
{
    /**
     * @var ParserService
     */
    protected $parserService;

    protected static $defaultName = 'app:parse-course';

    public function __construct(ParserService $parserService)
    {
        $this->parserService = $parserService;
        parent::__construct(self::$defaultName);
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
        $this->parserService->parseCoursePage($courseUrl);

        return 0;
    }
}