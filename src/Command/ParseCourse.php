<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCourse extends Command
{
    protected static $defaultName = 'app:parse-course';

    protected function configure()
    {
        $this
            ->addArgument('course_url', InputArgument::REQUIRED, 'URL of course video list')
            ->setDescription('Parses all files from course.')
            ->setHelp('Downloads videos and files from course url provided as argument.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //TODO: write parsing logic

        return 0;
    }

}