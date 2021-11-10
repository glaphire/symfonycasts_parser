<?php

namespace App\Module\SymfonycastsParser\PageObject;

use App\Module\SymfonycastsParser\WebdriverFacade\WebdriverFacadeInterface;

class LessonPage extends AbstractPageObject
{
    public const SLCTR_DOWNLOAD_MENU_BUTTON = '#downloadDropdown';
    public const SLCTR_DOWNLOAD_MENU_LIST = '.dropdown-menu.show';

    public const SLCTR_DOWNLOAD_MENU_LIST_ITEM_VIDEO = '.dropdown-menu a[data-download-type=video]';
    public const SLCTR_DOWNLOAD_MENU_LIST_ITEM_SCRIPT = '.dropdown-menu a[data-download-type=script]';
    public const SLCTR_DOWNLOAD_MENU_LIST_ITEM_CODE_ARCHIVE = '.dropdown-menu a[data-download-type=code]';

    public function __construct(WebdriverFacadeInterface $webdriver)
    {
        $this->webdriver = $webdriver;
    }

    public function parseLessonPage(
        $parseVideoFlag = true,
        $parseScriptFlag = false,
        $parseCodeArchiveFlag = false
    ) {
        if ($parseVideoFlag) {
            $this->clickDropdownOptionAndDownload(self::SLCTR_DOWNLOAD_MENU_LIST_ITEM_VIDEO);
        }
        if ($parseScriptFlag) {
            $this->clickDropdownOptionAndDownload(self::SLCTR_DOWNLOAD_MENU_LIST_ITEM_SCRIPT);
        }
        if ($parseCodeArchiveFlag) {
            $this->clickDropdownOptionAndDownload(self::SLCTR_DOWNLOAD_MENU_LIST_ITEM_CODE_ARCHIVE);
        }

        $this->webdriver->waitFilesToDownload(); //TODO: recheck if this line is neccessary
    }

    public function downloadVideo()
    {
        $this->clickDropdownOptionAndDownload(self::SLCTR_DOWNLOAD_MENU_LIST_ITEM_VIDEO);
        $this->webdriver->waitFilesToDownload();
    }

    public function downloadCourseScript()
    {
        $this->clickDropdownOptionAndDownload(self::SLCTR_DOWNLOAD_MENU_LIST_ITEM_SCRIPT);
        $this->webdriver->waitFilesToDownload();
    }

    public function downloadCourseCodeArchive()
    {
        $this->clickDropdownOptionAndDownload(self::SLCTR_DOWNLOAD_MENU_LIST_ITEM_CODE_ARCHIVE);
        $this->webdriver->waitFilesToDownload();
    }

    protected function clickDropdownOptionAndDownload(string $cssSelector)
    {
        $this->webdriver->waitAndClick(self::SLCTR_DOWNLOAD_MENU_BUTTON);
        $this->webdriver->waitToBeClickable(self::SLCTR_DOWNLOAD_MENU_LIST);
        $this->webdriver->click($cssSelector);
    }
}
