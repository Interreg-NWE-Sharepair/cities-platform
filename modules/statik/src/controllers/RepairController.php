<?php

namespace modules\statik\controllers;

use Craft;
use craft\elements\Category;
use craft\elements\Entry;
use craft\errors\ElementNotFoundException;
use craft\helpers\UrlHelper;
use craft\records\EntryType;
use craft\web\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use modules\statik\Statik;
use yii\base\Exception;
use yii\web\Response;

class RepairController extends Controller
{
    public string $productHandle = 'productCategory';
    public string $statusHandle = 'repairStatus';
    public string $professionalHandle = 'professional';

    protected int|bool|array $allowAnonymous = ['save-diy', 'handle-professional-repair'];

    public function actionSaveDiy(): ?Response
    {
        $request = Craft::$app->getRequest();
        try {
            $section = Craft::$app->getSections()->getSectionByHandle("allDiyRepairs");
            $entryType = EntryType::findOne(["handle" => "repair"]);
            return $this->saveAndPostRepair($request, $section, $entryType);
        } catch (ElementNotFoundException | Exception | \Throwable $e) {
            Craft::error("Something went wrong while saving the diy repair: {$e}", "REPCIT_STATIK");
            return null;
        }
    }

    public function actionHandleProfessionalRepair(): ?Response
    {
        $request = Craft::$app->getRequest();
        try {
            $section = Craft::$app->getSections()->getSectionByHandle("allProfessionalRepairs");
            $entryType = EntryType::findOne(["handle" => "professionalRepairSubmission"]);
            return $this->saveAndPostRepair($request, $section, $entryType, true);
        } catch (ElementNotFoundException | Exception | \Throwable $e) {
            Craft::error("Something went wrong while saving the diy repair: {$e}", "REPCIT_STATIK");
            return null;
        }
    }

    private function postRepair($request)
    {
        try{
            $body = $request->getBodyParam('fields');
            $body = $this->parseRepairLog($body);
            $client = new Client(["base_uri" => "https://www.repairconnects.org/api/"]);
            $client->request('POST', "v1/devices/log", ['json' => json_encode($body)]);
        } catch (GuzzleException $e) {
            Craft::error("Something went wrong while posting the professional repair: {$e}", "REPCIT_STATIK");
        }
    }

    private function parseRepairLog($body): array
    {
        $brandName = null;
        $deviceType = null;
        $location = null;
        $manufactureYear = null;
        $repairStatus = null;
        $sparePart = null;
        $date = null;
        if(isset($body['brand'])){
            $brandName = $body['brand'];
        }
        if(isset($body['productCategory'][0])){
            $deviceType = $body['productCategory'][0];
        }
        if(isset($body['professional'][0])){
            $location = $body['professional'][0];
        }
        if(isset($body['age'])){
            $manufactureYear = $body['age'];
        }
        if(isset($body['repairStatus'][0])){
            $repairStatus = $body['repairStatus'][0];
        }
        if(isset($body['sparePart'])){
            switch ($body['sparePart']){
                case 'yes':
                    $sparePart = 1;
                    break;
                case 'no':
                    $sparePart = 0;
                    break;
                default:
                    $sparePart = null;
            }
        }
        if(isset($body['date'])){
            $date = $body['date'];
        }

        $locale = explode("-", Craft::$app->getSites()->getCurrentSite()->language)[0];

        $array = [
            'brand_name' => $brandName, //string required
            'model_name' => null, //string
            'device_description' => null, //string
            'device_type' => $deviceType, //string required
            'issue_description' => null, //string
            'fix_description' => null, //string
            'used_materials' => null, //string
            'location' => $location, //string required
            'first_name' => null, //string
            'last_name' => null, //string
            'email' => null, //string
            'telephone' => null, //string
            'postal_code' => null, //string
            'locale' => $locale, //string required
            'manufacture_year' => $manufactureYear, // year (int)
            'repair_status' => $repairStatus, //string required
            'is_using_spare_parts' => $sparePart, // 0 of 1
            'closed_at' => $date, // date
        ];

        return $array;
    }

    /**
     * @throws ElementNotFoundException
     * @throws Exception
     * @throws \Throwable
     */
    private function saveAndPostRepair($request, $section, $entryType, $postToApi = false): ?Response
    {
        $site = $request->sites->getCurrentSite();
        if (!$section) {
            Craft::error("No section found", "REPCIT_STATIK");
        }

        if (!$entryType) {
            Craft::error("No entryType found", "REPCIT_STATIK");
        }

        if (!$site) {
            Craft::error("No site found", "REPCIT_STATIK");
        }

        $professionalRepair = new Entry();
        $professionalRepair->sectionId = $section->id;
        $professionalRepair->typeId = $entryType->id;
        $professionalRepair->siteId = $site->id;
        $professionalRepair->setFieldValuesFromRequest("fields");

        $productCategory = $this->setProductCategories($request->getBodyParam('productCategory')[0]);
        if ($productCategory !== null) {
            $professionalRepair->setFieldValue($this->productHandle, [$productCategory]);
        }

        if(isset($request->getBodyParam('fields')['professional'])){
            $professional = $this->setProfessional($request->getBodyParam('professional')[0]);
            if ($professional !== null) {
                $professionalRepair->setFieldValue($this->professionalHandle, [$professional]);
            }
        }

        $repairStatus = $this->setRepairStatus($request->getBodyParam('repairStatus')[0]);
        if ($repairStatus !== null) {
            $professionalRepair->setFieldValue($this->statusHandle, [$repairStatus]);
        }

        $professionalRepair->updateTitle();

        if (!$professionalRepair->validate()) {
            Craft::$app->getUrlManager()->setRouteParams(['repair' => $professionalRepair]);
            return null;
        }
        $saved = Craft::$app->elements->saveElement($professionalRepair, false);
        if($saved && $postToApi){
            $this->postRepair($request);
        }
        $redirect = $request->getValidatedBodyParam('redirect');
        return $this->redirect(UrlHelper::siteUrl($redirect));
    }

    private function setProductCategories($productId): ?int
    {
        $productCategoryGroup = Craft::$app->getCategories()->getGroupByHandle($this->productHandle);
        if (!$productCategoryGroup) {
            return null;
        }
        $productCategory = Category::findOne(['groupId' => $productCategoryGroup->id, 'categoryId' => $productId]);
        return $productCategory->id;
    }

    private function setRepairStatus($statusId): ?int
    {
        $repairStatusCategoryGroup = Craft::$app->getCategories()->getGroupByHandle($this->statusHandle);
        if (!$repairStatusCategoryGroup) {
            return null;
        }
        $repairStatus = Category::findOne(['groupId' => $repairStatusCategoryGroup->id, 'categoryId' => $statusId]);
        return $repairStatus->id;
    }

    private function setProfessional($professionalId): ?int
    {
        $organisationSectionId = Craft::$app->getSections()->getSectionByHandle('organisations')->id;
        $organisationTypeId = EntryType::findOne(['handle' => 'organisations'])->id;

        $professional = Entry::findOne([
            'sectionId' => $organisationSectionId,
            'typeId' => $organisationTypeId,
            'organisationId' => $professionalId
        ]);
        return $professional->id;
    }
}