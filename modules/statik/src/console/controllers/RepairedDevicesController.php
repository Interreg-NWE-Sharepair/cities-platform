<?php

namespace modules\statik\console\controllers;

use craft\elements\Entry;
use craft\helpers\Db;
use modules\statik\jobs\RepairedDevicesJob;

use Craft;
use craft\console\Controller;
use craft\helpers\Json;
use GuzzleHttp\Client;

class RepairedDevicesController extends Controller
{
    public function actionGetRepairedDevicesCount()
    {
        $client = new Client(["base_uri" => "https://www.repairconnects.org/api/"]);

        $leuvenId = "3c9ec89d-8675-4d64-9099-e9414d8b0260";
        $apeldoornId = "870ed14b-7e92-4d96-98f5-17680e4e1586";
        $roeselareId = "4ea9d822-27a8-42bd-a320-4de4575a9b9d";
        $louvainLaNeuve = "repairstudio";

        $responseLeuven = $client->request('GET', "v1/locations/".$leuvenId);
        $dataLeuven = JSON::decodeIfJson($responseLeuven->getBody()->getContents(), true);

        $responseApeldoorn = $client->request('GET', "v1/locations/".$apeldoornId);
        $dataApeldoorn = JSON::decodeIfJson($responseApeldoorn->getBody()->getContents(), true);

        $responseRoeselare = $client->request('GET', "v1/locations/".$roeselareId);
        $dataRoeselare = JSON::decodeIfJson($responseRoeselare->getBody()->getContents(), true);

        $responseLouvainLaNeuve = $client->request('GET', "v1/locations/".$louvainLaNeuve);
        $dataLouvainLaNeuve = JSON::decodeIfJson($responseLouvainLaNeuve->getBody()->getContents(), true);

        Craft::$app->getQueue()->push(new RepairedDevicesJob(['dataLeuven' => $dataLeuven, 'dataApeldoorn' => $dataApeldoorn, 'dataRoeselare' => $dataRoeselare, 'dataLouvainLaNeuve' => $dataLouvainLaNeuve]));
    }
}