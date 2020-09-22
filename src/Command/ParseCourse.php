<?php

namespace App\Command;

use App\Module\SymfonycastsParser\Service\ParserService;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCourse extends Command
{
    protected ParserService $parserService;

    protected $logger;

    protected static $defaultName = 'app:parse-course';

    public function __construct(ParserService $parserService, LoggerInterface $logger)
    {
        $this->parserService = $parserService;
        $this->logger = $logger;

        parent::__construct(self::$defaultName);
    }

    protected function configure()
    {
        $this
            ->addArgument('course_url', InputArgument::REQUIRED, 'URL of course video list')
            ->addArgument('start_lesson_number', InputArgument::OPTIONAL, 'Number of start lesson')
            ->setDescription('Parses all files from course.')
            ->setHelp('Downloads videos and files from course url provided as argument.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $courseUrl = $input->getArgument('course_url');
        $startLessonNumber = $input->getArgument('start_lesson_number') ?? 1;

        try {
            $this->validateLessonNumberType($startLessonNumber);
            $this->parserService->parseCoursePage($courseUrl, $startLessonNumber);
        } catch (Exception $e) {
            $this->logger->error($e);
            $this->parserService->shutdownDownloadingProcess();
            $terminationMessage = sprintf('Downloading process terminated due to exception: %s', $e->getMessage());
            $output->writeln("<error>$terminationMessage</error>");
        }

        return 0;
    }

    private function validateLessonNumberType($startLessonNumber)
    {
        if ((int) $startLessonNumber != $startLessonNumber) {
            throw new InvalidArgumentException('Lesson number should be an integer');
        }

        return true;
    }
}
