<?php

namespace modules\statik\console\controllers;

use craft\elements\Entry;
use modules\statik\jobs\OrganisationJob;

use Craft;
use craft\console\Controller;
use craft\helpers\Json;
use GuzzleHttp\Client;

class OrganisationsController extends Controller
{
    public function actionGetOrganisations(): bool
    {
        $client = new Client(["base_uri" => "https://www.repairconnects.org/api/"]);
        $page = 1;
        $result = true;
        while ($result) {
            $response = $client->request('GET', "v1/locations?per_page=100&page={$page}&limit=100");

            $data = JSON::decodeIfJson($response->getBody()->getContents(), true);
            if ($data['meta']['last_page'] === $page) {
                $result = false;
            }
            foreach ($data['data'] as $organisation) {
                Craft::$app->getQueue()->push(new OrganisationJob(['organisation' => $organisation]));
            }
            $page++;
        }
        return true;
    }

    public function actionDeleteAllOrganisations()
    {
        $sites = Craft::$app->sites->getAllSites();
        $ids = array_map(fn($s): int => $s->id, $sites);

        $orgs = Entry::findAll(["sectionId" => 36, "siteId" => $ids]);
        foreach ($orgs as $org) {
            Craft::$app->elements->deleteElement($org);
        }
    }
}
