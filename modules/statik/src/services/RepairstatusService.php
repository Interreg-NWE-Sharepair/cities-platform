<?php

namespace modules\statik\services;

use Craft;
use craft\elements\Entry;
use craft\elements\Category;
use craft\errors\ElementNotFoundException;
use craft\helpers\Json;
use craft\models\Section;
use Exception;
use craft\base\Component;
use craft\records\EntryType;
use modules\statik\models\Organisation;
use yii\base\BaseObject;

class RepairstatusService extends Component
{
    public string $statusHandle = 'repairStatus';

    public function setRepairStatus($statuses): ?array
    {
        $result = [];
        foreach ($statuses as $status) {
            $title = $status['name']['en'];
            if (!$title) {
                return null;
            }

            $statusCategoryGroup = Craft::$app->getCategories()->getGroupByHandle($this->statusHandle);
            if (!$statusCategoryGroup) {
                return null;
            }

            $statusCategory = Category::findOne(['groupId' => $statusCategoryGroup->id, 'categoryId' => $status['id']]);

            //Create a new category if the category doesn't exist
            if (!$statusCategory) {
                $statusCategory = new Category();
                $statusCategory->title = $title;
            }
            $statusCategory->groupId = $statusCategoryGroup->id;
            $statusCategory->categoryId = $status['id'];
            $statusCategory->setFieldValue('titleNl', $status['name']['nl']);
            $statusCategory->setFieldValue('titleEn', $status['name']['en']);
            $statusCategory->setFieldValue('titleFr', $status['name']['fr']);
            $statusCategory->setFieldValue('titleDe', $status['name']['de']);
            try {
                Craft::$app->elements->saveElement($statusCategory);
            } catch (ElementNotFoundException | \yii\base\Exception | \Throwable $e) {
                Craft::error("Error while saving productCategory category with id: {$status['id']}", "REPCIT_STATIK");
            }

            $result[] = $statusCategory->id;
        }
        return $result;
    }

    public function translateRepairStatus(){
        $sites = Craft::$app->sites->getAllSites();

        foreach ($sites as $site) {
            if ($site->handle === "platform") {
                continue;
            }
            $statusCategoryGroup = Craft::$app->getCategories()->getGroupByHandle($this->statusHandle);
            if (!$statusCategoryGroup) {
                return null;
            }

            $categories = Category::findAll(['groupId' => $statusCategoryGroup->id, 'siteId' => $site->id]);
            foreach ($categories as $category) {
                switch ($site->language) {
                    case "nl-BE":
                        $category->title = $category->titleNl;
                        break;
                    case "fr-BE":
                        $category->title = $category->titleFr;
                        break;
                    case "en-BE":
                        $category->title = $category->titleEn;
                        break;
                }
                Craft::$app->elements->saveElement($category);
            }
        }
    }
}