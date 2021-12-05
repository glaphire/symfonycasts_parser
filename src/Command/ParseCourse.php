<?php

declare(strict_types=1);

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
    private const RESULT_SUCCESS = 0;
    private const RESULT_FAILURE = 1;

    private ParserService $parserService;
    private LoggerInterface $logger;

    protected static $defaultName = 'app:parse-course';

    public function __construct(ParserService $parserService, LoggerInterface $logger)
    {
        $this->parserService = $parserService;
        $this->logger = $logger;

        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('course_url', InputArgument::REQUIRED, 'URL of course video list')
            ->addArgument('start_lesson_number', InputArgument::OPTIONAL, 'Number of start lesson')
            ->setDescription('Parses all files from course.')
            ->setHelp('Downloads videos and files from course url provided as argument.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $courseUrl = $input->getArgument('course_url');
        $startLessonNumber = $input->getArgument('start_lesson_number') ?? 1;

        try {
            $this->validateLessonNumberType($startLessonNumber);
            $this->parserService->parseCoursePage($courseUrl, (int)$startLessonNumber);
        } catch (\Throwable $e) {
            $this->logger->error($e);
            $this->parserService->shutdownDownloadingProcess();
            $terminationMessage = sprintf(
                'Downloading process terminated due to exception: %s',
                $e->getMessage()
            );
            $output->writeln("<error>{$terminationMessage}</error>");

            return self::RESULT_FAILURE;
        }

        return self::RESULT_SUCCESS;
    }

    /**
     * ad-hoc validation, because we can't convert argument
     * to int on-the-fly without loosing context about it's value
     */
    private function validateLessonNumberType(int $startLessonNumber): bool
    {
        if (!is_numeric($startLessonNumber)) {
            throw new InvalidArgumentException('Lesson number should be an integer');
        }

        return true;
    }
}
