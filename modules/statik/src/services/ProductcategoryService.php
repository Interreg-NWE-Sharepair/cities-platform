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

class ProductcategoryService extends Component
{
    public string $productHandle = 'productCategory';

    public function setProductCategories($products): ?array
    {
        $result = [];
        foreach ($products as $product) {
            $title = $product['name']['en'];
            if (!$title) {
                return null;
            }

            $productCategoryGroup = Craft::$app->getCategories()->getGroupByHandle($this->productHandle);
            if (!$productCategoryGroup) {
                return null;
            }

            $productCategory = Category::findOne(['groupId' => $productCategoryGroup->id, 'categoryId' => $product['id']]);

            //Create a new category if the category doesn't exist
            if (!$productCategory) {
                $productCategory = new Category();
                $productCategory->title = $title;
            }
            $productCategory->groupId = $productCategoryGroup->id;
            $productCategory->categoryId = $product['id'];
            $productCategory->setFieldValue('titleNl', $product['name']['nl']);
            $productCategory->setFieldValue('titleEn', $product['name']['en']);
            $productCategory->setFieldValue('titleFr', $product['name']['fr']);
            $productCategory->setFieldValue('titleDe', $product['name']['de']);
            try {
                Craft::$app->elements->saveElement($productCategory);
            } catch (ElementNotFoundException | \yii\base\Exception | \Throwable $e) {
                Craft::error("Error while saving productCategory category with id: {$product['id']}", "REPCIT_STATIK");
            }

            //Set the parent of the category
            if (isset($product['parent_category'])) {
                $id = $product['parent_category']['id'];
                $parent = Category::findOne(['groupId' => $productCategoryGroup->id, 'categoryId' => [$id]]);
                $productCategory->parentId = $parent->id;
                try {
                    Craft::$app->elements->saveElement($productCategory);
                } catch (ElementNotFoundException | \yii\base\Exception | \Throwable $e) {
                    Craft::error("Error while saving productCategory category with id: {$product['id']}", "REPCIT_STATIK");
                }
            }
            $result[] = $productCategory->id;
        }
        return $result;
    }

    public function translateProductCategories(){
        $sites = Craft::$app->sites->getAllSites();

        foreach ($sites as $site) {
            if ($site->handle === "platform") {
                continue;
            }
            $productCategoryGroup = Craft::$app->getCategories()->getGroupByHandle($this->productHandle);
            if (!$productCategoryGroup) {
                return null;
            }

            $categories = Category::findAll(['groupId' => $productCategoryGroup->id, 'siteId' => $site->id]);
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