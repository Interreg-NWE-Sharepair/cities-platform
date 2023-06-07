<?php

namespace modules\statik\console\controllers;

use craft\console\Controller;
use craft\helpers\Json;
use GuzzleHttp\Client;
use modules\statik\Statik;

class ProductcategoryController extends Controller
{
    public function actionSaveOrUpdateProductCategories(){
        $client = new Client(["base_uri" => "https://www.repairconnects.org/api/"]);
        $response = $client->request('GET', "v1/device_types");

        $data = JSON::decodeIfJson($response->getBody()->getContents(), true);

        Statik::getInstance()->productCategory->setProductCategories($data['data']);
    }

    public function actionTranslateProductCategories() {
        Statik::getInstance()->productCategory->translateProductCategories();
    }
}