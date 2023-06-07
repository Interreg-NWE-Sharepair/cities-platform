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
use craft\base\Element;
use craft\elements\Entry;
use craft\elements\User;
use craft\helpers\ElementHelper;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use Exception;
use modules\statik\services\MailService;
use modules\statik\Statik;

/**
 * @author    Statik
 * @package   Statik
 * @since     1.0.0
 */
class StoriesController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected int|bool|array $allowAnonymous = ['save-story', 'save-story-without-author'];

    // Public Methods
    // =========================================================================

    /**
     * @return void|\yii\web\Response
     */
    public function actionSaveStory()
    {
        try {
            $entry = null;
            $request = Craft::$app->getRequest();
            $language = substr($request->sites->getCurrentSite()->language, 0, 2);

            $fields = $request->getBodyParam('fields');
        
            $storySectionId = Craft::$app->getSections()->getSectionByHandle('repairStories')->id;

            if ($request->getParam('draftId') != null) {
                $entry = Entry::find()
                ->draftId($request->getParam('draftId'))
                ->draftCreator(Craft::$app->getUser()->getIdentity())
                ->siteId($request->getParam('siteId'))
                ->anyStatus()
                ->one();
            } else {
                $entry = new Entry();
                $entry->sectionId = $storySectionId;
                $entry->typeId = $storySectionId;
                $entry->siteId = $request->getParam('siteId');
                $entry->authorId = $request->getParam('authorId');
                $entry->enabled = $request->getParam('enabled');
            }
            
            $entry->title = $fields['title'];
            $entry->setFieldValuesFromRequest('fields');

            if ($request->getBodyParam('saveDraft')) {
                if (!$entry->slug) {
                    $entry->slug = ElementHelper::tempSlug();
                }
                $entry->setScenario(Element::SCENARIO_ESSENTIALS);
                Craft::$app->getDrafts()->saveElementAsDraft($entry, Craft::$app->getUser()->getIdentity()->getId());
            }
            
            if ($request->getBodyParam('submitStory')) {
                if ($request->getParam('draftId') != null) {
                    Craft::$app->getDrafts()->publishDraft($entry);
                } else {
                    Craft::$app->elements->saveElement($entry);
                }
                MailService::instance()->sendEmailStory($fields, Craft::$app->getUser()->getIdentity(), $language);
            }

            $redirect = $request->getValidatedBodyParam('redirect');
            if($redirect){
                return $this->redirect(UrlHelper::siteUrl($redirect));
            }
            return $this->redirect(UrlHelper::siteUrl('profiel'));
        } catch (Exception $e) {
            Craft::error("Error while processing story {$entry->title}");
        } catch (\Throwable $th) {
            Craft::error("Error while processing story {$entry->title}");
        }
    }

    public function actionSaveStoryWithoutAuthor()
    {
        try {
            $entry = null;
            $request = Craft::$app->getRequest();

            $language = substr($request->sites->getCurrentSite()->language, 0, 2);
            
            $fields = $request->getBodyParam('fields');
        
            $storySectionId = Craft::$app->getSections()->getSectionByHandle('repairStories')->id;

            $entry = new Entry();
            $entry->sectionId = $storySectionId;
            $entry->typeId = $storySectionId;
            $entry->siteId = $request->getParam('siteId');
            $entry->enabled = $request->getParam('enabled');
            
            $entry->title = $fields['title'];
            $entry->setFieldValuesFromRequest('fields');

            if (!$entry->validate()) {
                Craft::$app->getUrlManager()->setRouteParams([
                    'story' => $entry,
                ]);
                return null;
            }

            Craft::$app->elements->saveElement($entry, false);
            MailService::instance()->sendEmailStoryWithoutAuthor($fields, $language);

            $redirectSlug = null;
            switch ($language){
                case "nl":
                    $redirectSlug = 'bedankt';
                    break;
                case "en":
                    $redirectSlug = 'thanks';
                    break;
                case "fr":
                    $redirectSlug = 'merci';
                    break;
            }
            return $this->redirect(UrlHelper::siteUrl($redirectSlug));
        } catch (Exception $e) {
            Craft::error("Error while processing story {$entry->title}");
        } catch (\Throwable $th) {
            Craft::error("Error while processing story {$entry->title}");
        }
    }
}
