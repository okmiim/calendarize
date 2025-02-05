<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers;

use HDNET\Calendarize\Seo\CalendarizeTitleProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * TitleTagViewHelper.
 *
 * @see https://github.com/georgringer/news/blob/master/Classes/ViewHelpers/TitleTagViewHelper.php
 */
class TitleTagViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Render the title function.
     *
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $content = trim((string)$renderChildrenClosure());
        if (!empty($content)) {
            if (property_exists($GLOBALS['TSFE'], 'altPageTitle')) {
                $GLOBALS['TSFE']->altPageTitle = $content;
            }
            if (property_exists($GLOBALS['TSFE'], 'indexedDocTitle')) {
                $GLOBALS['TSFE']->indexedDocTitle = $content;
            }
        }

        if (!empty($content)) {
            GeneralUtility::makeInstance(CalendarizeTitleProvider::class)->setTitle($content);
        }

        return '';
    }
}
