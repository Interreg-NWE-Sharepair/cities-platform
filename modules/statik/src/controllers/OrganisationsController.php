<?php
/**
 * Statik module for Craft CMS 3.x
 *
 * Paste some cool functions here
 *
 * @link      https://www.statik.be
 * @copyright Copyright (c) 2018 Statik
 */

namespace modules\statik\controllers;

use Craft;
use craft\elements\Entry;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use yii\base\Exception;

/**
 * @author    Statik
 * @package   Statik
 * @since     1.0.0
 */
class OrganisationsController extends Controller
{
    protected int|bool|array $allowAnonymous = ['redirect'];

    public function actionRedirect($uid = null): \yii\web\Response
    {
        $section = Craft::$app->getSections()->getSectionByHandle('organisations');
        if (!$section || ! $uid) {
            return $this->renderTemplate('404');
        }
        $entry = Entry::findOne(["sectionId" => $section->id, "organisationId" => $uid]);
        try {
            return $this->redirect(UrlHelper::siteUrl($entry->uri));
        } catch (Exception $e) {
            Craft::error("Could not redirect to $entry->id: $e", "STATIK_REPCIT");
            return $this->renderTemplate('404');
        }
    }
}