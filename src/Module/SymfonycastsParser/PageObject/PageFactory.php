<?php

declare(strict_types=1);

namespace App\Module\SymfonycastsParser\PageObject;

use App\Module\SymfonycastsParser\Service\Exception\ProcessingException;
use App\Module\SymfonycastsParser\Webdriver\WebdriverFacadeInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PageFactory
{
    public const TYPE_LOGIN = 'login';
    public const TYPE_COURSE = 'course';
    public const TYPE_LESSON = 'lesson';

    private WebdriverFacadeInterface $webdriver;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(WebdriverFacadeInterface $webdriver, ParameterBagInterface $parameterBag)
    {
        $this->webdriver = $webdriver;
        $this->parameterBag = $parameterBag;
    }

    private function allowedTypes(): array
    {
        return [
            self::TYPE_LESSON,
            self::TYPE_LOGIN,
            self::TYPE_COURSE,
        ];
    }

    /**
     * @throws ProcessingException
     */
    public function create(string $pageType): AbstractPageObject
    {
        switch ($pageType) {
            case self::TYPE_LOGIN:
                $page = new LoginPage(
                    $this->webdriver,
                    $this->parameterBag->get('smfCastsLogin'),
                    $this->parameterBag->get('smfCastsPassword')
                );

                break;
            case self::TYPE_COURSE:
                $page = new CoursePage($this->webdriver);

                break;
            case self::TYPE_LESSON:
                $page = new LessonPage($this->webdriver);

                break;
            default:
                $errorMessage = sprintf(
                    'Page with type "%s" was not found. Allowed types: %s',
                    $pageType,
                    implode(', ', $this->allowedTypes())
                );

                throw new ProcessingException($errorMessage);
        }

        return $page;
    }
}
