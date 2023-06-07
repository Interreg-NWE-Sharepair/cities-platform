<?php

namespace modules\statik\services;

use Craft;
use craft\elements\Entry;
use craft\elements\Category;
use craft\errors\ElementNotFoundException;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\models\Section;
use craft\models\Site;
use Exception;
use craft\base\Component;
use craft\records\EntryType;
use modules\statik\models\Organisation;
use modules\statik\Statik;
use yii\base\BaseObject;

class OrganisationService extends Component
{
    public string $typeHandle = 'organisationType';
    public string $productHandle = 'productCategory';
    public string $language = "";
    public int $nlSite;
    public int $frSite;
    public int $enSite;
    private ?int $organisationSectionId;
    private int $organisationTypeId;

    public function init() : void
    {
        $this->nlSite = \craft\records\Site::find()
            ->where(Db::parseParam("language", "nl-BE", "="))
//            ->andWhere(Db::parseParam("handle", "platform", 'not'))
            ->one()->id;

        $this->enSite = \craft\records\Site::find()
            ->where(Db::parseParam("language", "en-BE", "="))
            ->one()->id;

        $this->frSite = \craft\records\Site::find()
            ->where(Db::parseParam("language", "fr-BE", "="))
            ->one()->id;

        $this->organisationSectionId = Craft::$app->getSections()->getSectionByHandle('organisations')->id;
        $this->organisationTypeId = EntryType::findOne(['handle' => 'organisations'])->id;
    }

    public function createOrUpdateOrganisations($organisation) : void
    {
        try {
            $o = new Organisation();
            $o->populate($organisation);

            foreach ($o->locale as $locale) {
                $siteId = null;
                switch ($locale) {
                    case 'nl':
                        $siteId = $this->nlSite;
                        $this->language = $locale;
                        break;
                    case 'fr':
                        $siteId = $this->frSite;
                        $this->language = $locale;
                        break;

                    case 'en':
                        $siteId = $this->enSite;
                        $this->language = $locale;
                        break;

                    default:
                        Craft::info("Locale is not supported in the current site: $locale", "REPCIT_STATIK");
                        break;
                }

                if (!isset($siteId)){
                    Craft::error("Could not set site id for organisation with id {$o->organisationId}","REPCIT_STATIK");
                    continue;
                }

                $entry = Entry::findOne(['sectionId' => $this->organisationSectionId,
                    'typeId' => $this->organisationTypeId,
                    'organisationId' => $o->organisationId,
                    'siteId' => $siteId,
                    'status' => null]);

                if (!$entry) {
                    $entry = new Entry();
                    $entry->sectionId = $this->organisationSectionId;
                    $entry->typeId = $this->organisationTypeId;
                    $entry->siteId = $siteId;
                }

                //Set the title of the entry
                if (isset($o->organisationTitle[$this->language])) {
                    $entry->title = $o->organisationTitle[$this->language];
                } elseif (isset($o->organisationTitle['default'])) {
                    $entry->title = $o->organisationTitle['default'];
                }

                //Set the logo url without parameters
                if ($o->logo) {
                    $url = explode('?',$o->logo);
                    $entry->setFieldValue('organisationLogo', $url[0]);
                } else {
                    $entry->setFieldValue('organisationLogo', "");
                }

                //Set the image
                if (!empty($o->images)) {
                    $images = $this->setImages($o->images);
                    $entry->setFieldValue('organisationImages', $images);
                }

                //Set the organisationId
                $entry->setFieldValue('organisationId', $o->organisationId);

                //Set the organisationType
                $organisationTypeCategory = $this->setOrganisationType($o->organisationType);
                if ($organisationTypeCategory !== null) {
                    $entry->setFieldValue($this->typeHandle, [$organisationTypeCategory->id]);
                }

                //Set the intro
                if (isset($o->intro[$this->language])) {
                    $entry->setFieldValue('organisationIntro', $o->intro[$this->language]);
                } elseif (isset($o->intro['default'])) {
                    $entry->setFieldValue('organisationIntro', $o->intro['default']);
                } else {
                    $entry->setFieldValue('organisationIntro', "");
                }
                //Set the productCategory
                $productCategories = $this->setProductCategory($o->productCategory);
                if ($productCategories !== null) {
                    $entry->setFieldValue($this->productHandle, $productCategories);
                }

                //Set the warranty
                if (isset($o->warranty[$this->language])) {
                    $entry->setFieldValue('warranty', strip_tags($o->warranty[$this->language]));
                } elseif (isset($o->warranty['default'])) {
                    $entry->setFieldValue('warranty', strip_tags($o->warranty['default']));
                }

                $entry->setFieldValue('activeRepairers', $o->activeRepairers);
                $entry->setFieldValue('repairedDevices', $o->repairedDevices);

                //Set contact info
                $entry->setFieldValue('street', $o->address->street);
                $entry->setFieldValue('postalCode', $o->address->postalCode);
                $entry->setFieldValue('organisationCity', $o->address->city);
                $entry->setFieldValue('country', $o->address->country);
                if (isset($o->geometry['longitude'])) {
                    $entry->setFieldValue('longitudeOrganisation', $o->geometry['longitude']);
                }
                if (isset($o->geometry['latitude'])) {
                    $entry->setFieldValue('latitudeOrganisation', $o->geometry['latitude']);
                }
                $entry->setFieldValue('phone', $o->phone);
                $entry->setFieldValue('email', $o->email);
                if (str_starts_with($o->organisationUrl, 'http')){
                    $entry->setFieldValue('organisationUrl', $o->organisationUrl);
                }

                Craft::$app->elements->saveElement($entry);
                Craft::info("Saved entry {$entry->title}");
            }
        } catch (\Throwable $e) {
            Craft::error("Could not save entry for organisation with id {$o->organisationId}. $e","REPCIT_STATIK");
        }
    }

    private function setOrganisationType($type):?Category
    {
        $title = $type["name"][$this->language] ?? "";

        if (!$title) {
            return null;
        }

        $organisationTypeGroup = Craft::$app->getCategories()->getGroupByHandle($this->typeHandle);
        if (!$organisationTypeGroup){
            return null;
        }
        $organisationTypeCategory = Category::findOne(['groupId' => $organisationTypeGroup->id, 'categoryId' => $type['id']]);
        if (!$organisationTypeCategory) {
            $organisationTypeCategory = new Category();
            $organisationTypeCategory->groupId = $organisationTypeGroup->id;
            $organisationTypeCategory->categoryId = $type['id'];
            $organisationTypeCategory->title = $title;
            try {
                Craft::$app->elements->saveElement($organisationTypeCategory);
            } catch (ElementNotFoundException | \yii\base\Exception | \Throwable $e) {
                Craft::error("Error while saving organisationType category with id: {$type['id']}", "REPCIT_STATIK");
            }
        }
        return $organisationTypeCategory;
    }

    private function setProductCategory($products) : ?array
    {
        return Statik::getInstance()->productCategory->setProductCategories($products);
    }

    private function setImages($images) : string{
        $imagesArray = [];
        foreach ($images as $image) {
            if (isset($image['url'])){
                $url = explode('?',$image['url']);
                $imagesArray = array_merge($url, $imagesArray);
            }
        }
        return implode(',', $imagesArray);
    }
}
