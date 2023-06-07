<?php

namespace modules\statik\jobs;

use Craft;
use craft\elements\Entry;
use craft\queue\BaseJob;

class RepairedDevicesJob extends BaseJob
{
    public $dataLeuven;
    public $dataApeldoorn;
    public $dataRoeselare;
    public $dataLouvainLaNeuve;

    public function init(): void
    {
        $this->description = "Syncing repaired devices";
    }

    public function execute($queue): void
    {
        $siteIdLeuven = Craft::$app->sites->getSiteByHandle("leuvenNl")->id;
        $siteIdApeldoorn = Craft::$app->sites->getSiteByHandle("apeldoornNl")->id;
        $siteIdRoeselare = Craft::$app->sites->getSiteByHandle("roeselareNl")->id;
        $siteIdLouvainLaNeuve = Craft::$app->sites->getSiteByHandle("louvainLaNeuveFr")->id;

        $sectionId = Craft::$app->getSections()->getSectionByHandle('home')->id;

        $entryLeuven = Entry::findOne(['siteId' => $siteIdLeuven,'sectionId' => $sectionId]);
        $entryApeldoorn = Entry::findOne(['siteId' => $siteIdApeldoorn,'sectionId' => $sectionId]);
        $entryRoeselare = Entry::findOne(['siteId' => $siteIdRoeselare,'sectionId' => $sectionId]);
        $entryLouvainLaNeuve = Entry::findOne(['siteId' => $siteIdLouvainLaNeuve,'sectionId' => $sectionId]);

        $entryLeuven->setFieldValue('numberCafe', $this->dataLeuven['data']['repaired_devices_count']);
        $entryApeldoorn->setFieldValue('numberCafe', $this->dataApeldoorn['data']['repaired_devices_count']);
        $entryRoeselare->setFieldValue('numberCafe', $this->dataRoeselare['data']['repaired_devices_count']);
        $entryLouvainLaNeuve->setFieldValue('numberCafe', $this->dataLouvainLaNeuve['data']['repaired_devices_count']);

        Craft::$app->elements->saveElement($entryLeuven);
        Craft::$app->elements->saveElement($entryApeldoorn);
        Craft::$app->elements->saveElement($entryRoeselare);
        Craft::$app->elements->saveElement($entryLouvainLaNeuve);
    }
}
