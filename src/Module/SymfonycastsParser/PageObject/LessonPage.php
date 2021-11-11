<?php

namespace App\Module\SymfonycastsParser\PageObject;

use App\Module\SymfonycastsParser\Webdriver\WebdriverFacadeInterface;

class LessonPage extends AbstractPageObject
{
    public const CSS_DOWNLOAD_MENU_BUTTON = '#downloadDropdown';
    public const CSS_DOWNLOAD_MENU_LIST = '.dropdown-menu.show';

    public const CSS_DOWNLOAD_MENU_LIST_ITEM_VIDEO = 'div[aria-labelledby="downloadDropdown"] span:nth-child(2) a';
    public const CSS_DOWNLOAD_MENU_LIST_ITEM_SCRIPT = 'div[aria-labelledby="downloadDropdown"] span:nth-child(3) a';
    public const CSS_DOWNLOAD_MENU_LIST_ITEM_CODE_ARCHIVE = 'div[aria-labelledby="downloadDropdown"] span:nth-child(1) a';

    public function __construct(WebdriverFacadeInterface $webdriver)
    {
        $this->webdriver = $webdriver;
    }

    public function downloadVideo()
    {
        $this->clickDropdownOptionAndDownload(self::CSS_DOWNLOAD_MENU_LIST_ITEM_VIDEO);
        $this->webdriver->waitFilesToDownload();
    }

    public function downloadCourseScript()
    {
        $this->clickDropdownOptionAndDownload(self::CSS_DOWNLOAD_MENU_LIST_ITEM_SCRIPT);
        $this->webdriver->waitFilesToDownload();
    }

    public function downloadCourseCodeArchive()
    {
        $this->clickDropdownOptionAndDownload(self::CSS_DOWNLOAD_MENU_LIST_ITEM_CODE_ARCHIVE);
        $this->webdriver->waitFilesToDownload();
    }

    protected function clickDropdownOptionAndDownload(string $cssSelector)
    {
        $this->webdriver->waitAndClick(self::CSS_DOWNLOAD_MENU_BUTTON);
        $this->webdriver->waitToBeClickable(self::CSS_DOWNLOAD_MENU_LIST);
        $this->webdriver->click($cssSelector);
    }
}
