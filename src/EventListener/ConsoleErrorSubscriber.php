<?php

namespace App\EventListener;

use App\Module\SymfonycastsParser\Webdriver\WebdriverFacadeInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConsoleErrorSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    private WebdriverFacadeInterface $webdriverFacade;

    public function __construct(LoggerInterface $logger, WebdriverFacadeInterface $webdriverFacade)
    {
        $this->logger = $logger;
        $this->webdriverFacade = $webdriverFacade;
    }

    public function onErrorHandler(ConsoleErrorEvent $event): void
    {
        $error = $event->getError();
        $errorLogMessage = sprintf('Unexpected error occured with message: %s', $error->getMessage());
        $this->logger->error($errorLogMessage);
        $this->logger->info('Closing browser');
        $this->webdriverFacade->quit();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::ERROR => 'OnErrorHandler',
        ];
    }
}
