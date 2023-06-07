<?php

namespace modules\statik\widgets;

use Craft;
use craft\base\Widget;
use modules\statik\assetbundles\Statik\StatikAsset;

class ManualWidget extends Widget
{
    public static function displayName(): string
    {
        return Craft::t('app', 'Handleiding');
    }

    protected static function allowMultipleInstances(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return self::displayName();
    }

    /**
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\LoaderError
     */
    public function getBodyHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            'statik/_widget/_widgetManual');
    }
}