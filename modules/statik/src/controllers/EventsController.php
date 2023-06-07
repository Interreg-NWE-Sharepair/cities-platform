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
use craft\elements\Category;
use craft\elements\Entry;
use craft\helpers\ElementHelper;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use Exception;
use modules\statik\services\MailService;

/**
 * @author    Statik
 * @package   Statik
 * @since     1.0.0
 */
class EventsController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected int|bool|array $allowAnonymous = ['save-event'];

    // Public Methods
    // =========================================================================

    /**
     * @return void|\yii\web\Response|null
     */
    public function actionSaveEvent()
    {
        try {
            $request = Craft::$app->getRequest();
            $language = substr($request->sites->getCurrentSite()->language, 0, 2);
            $fields = $request->getBodyParam('fields');
            $eventSectionId = Craft::$app->getSections()->getSectionByHandle('events')->id;

            if ($request->getParam('draftId') != null) {
                $entry = Entry::find()
                ->draftId($request->getParam('draftId'))
                ->draftCreator(Craft::$app->getUser()->getIdentity())
                ->siteId($request->getParam('siteId'))
                ->anyStatus()
                ->one();
            } else {
                $entry = new Entry();
                $entry->sectionId = $eventSectionId;
                $entry->typeId = $eventSectionId;
            }

            $entry->siteId = $request->getParam('siteId');
            $entry->authorId = $request->getParam('authorId');
            $entry->enabled = $request->getParam('enabled');

            $entry->title = $fields['title'];
            $entry->setFieldValuesFromRequest('fields');
            
            $eventTypeGroup = Craft::$app->getCategories()->getGroupByHandle('eventType');
            if (!$eventTypeGroup) {
                return null;
            }
            $typeEventCategory = Category::findAll(['groupId' => $eventTypeGroup->id, 'id' => $fields['category']]);
            $typeEventCategoryIds = [];
            foreach($typeEventCategory as $cat){
                $typeEventCategoryIds[] = $cat->id;
            }
            $entry->setFieldValue('category', $typeEventCategoryIds);

            if ($request->getBodyParam('saveDraft')) {
                if (!$entry->slug) {
                    $entry->slug = ElementHelper::tempSlug();
                }
                $entry->setScenario(Element::SCENARIO_ESSENTIALS);
                Craft::$app->getDrafts()->saveElementAsDraft($entry, Craft::$app->getUser()->getIdentity()->getId());
            }

            if ($request->getBodyParam('submitEvent')) {
                if ($request->getParam('draftId') != null) {
                    Craft::$app->getDrafts()->publishDraft($entry);
                } else {
                    Craft::$app->elements->saveElement($entry);
                }
                MailService::instance()->sendEmailEvent($fields, Craft::$app->getUser()->getIdentity(), $language);
            }

            $redirect = $request->getValidatedBodyParam('redirect');
            if($redirect){
                return $this->redirect(UrlHelper::siteUrl($redirect));
            }
            return $this->redirect(UrlHelper::siteUrl('profiel'));
        } catch (\Throwable $th) {
            throw $th;
        } catch (Exception $e) {
            Craft::error("Error while processing story {$entry->title}");
        }
    }
}
