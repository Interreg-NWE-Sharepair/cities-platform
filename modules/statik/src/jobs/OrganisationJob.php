<?php

namespace modules\statik\jobs;

use craft\queue\BaseJob;
use modules\statik\Statik;

class OrganisationJob extends BaseJob
{
    public array $organisation;

    public function init(): void
    {
        $this->description = "Creating organisation '{$this->organisation['id']}'";
    }

    public function execute($queue): void
    {
        Statik::$instance->organisation->createOrUpdateOrganisations($this->organisation);
    }
}