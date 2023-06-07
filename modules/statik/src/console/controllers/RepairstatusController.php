<?php

namespace modules\statik\console\controllers;

use craft\console\Controller;
use craft\helpers\Json;
use GuzzleHttp\Client;
use modules\statik\Statik;

class RepairstatusController extends Controller
{
    public function actionSaveOrUpdateRepairStatuses(){
        $client = new Client(["base_uri" => "https://www.repairconnects.org/api/"]);
        $response = $client->request('GET', "v1/repair_statuses");

        $data = JSON::decodeIfJson($response->getBody()->getContents(), true);
        Statik::getInstance()->repairStatus->setRepairStatus($data['data']);
    }

    public function actionTranslateRepairStatuses() {
        Statik::getInstance()->repairStatus->translateRepairStatus();
    }
}