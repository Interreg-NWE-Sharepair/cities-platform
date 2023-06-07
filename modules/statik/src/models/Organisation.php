<?php

namespace modules\statik\models;

use yii\base\Model;
use modules\statik\models\Address;

class Organisation extends Model
{
    public $organisationId; // id
    public $organisationTitle; // name
    // slug
    //description
    public $intro; // description
    // product_description
    public $address; // address -> street, number, bus, postal_code, city, country
    public $geometry; // geometry
    // has_warranty
    public $warranty; // warranty_description
    public $organisationType; // organisation_type -> name
    public $productCategory; // product_categories -> name
    // activity_sectors
    public $organisationUrl; // contacts -> website -> value
    public $email; // contacts -> email -> value
    public $phone; // contacts -> phone -> value
    public $locale; // locales
    public $logo; // logo->url
    public $images; // images[i]->url
    public $activeRepairers; // active_repairers_count
    public $repairedDevices; // repaired_devices_count

    public function populate($data)
    {
        if (isset($data['id'])) {
            $this->organisationId = $data['id'];
        }
        if (isset($data['name'])) {
            $this->organisationTitle = $data['name'];
        }
        if (isset($data['description'])) {
            $this->intro = $data['description'];
        }
        if (isset($data['address'])) {
            $this->address = new Address();
            $this->address->getAddress($data['address']);
        }
        if (isset($data['geometry'])){
            $this->geometry = $data['geometry'];
        }
        if (isset($data['has_warranty'])) {
            if (isset($data['warranty_description'])) {
                $this->warranty = $data['warranty_description'];
            }
        }
        if (isset($data['organisation_type'])) {
            $this->organisationType = $data['organisation_type'];
        }
        if (isset($data['product_categories'])) {
            $this->productCategory = $data['product_categories'];
        }
        if (isset($data['contacts']['website'][0]['value'])) {
            $this->organisationUrl = $data['contacts']['website'][0]['value'];
        }
        if (isset($data['contacts']['email'][0]['value'])) {
            $this->email = $data['contacts']['email'][0]['value'];
        }
        if (isset($data['contacts']['phone'][0]['value'])) {
            $this->phone = $data['contacts']['phone'][0]['value'];
        } elseif (isset($data['contacts']['mobile'][0]['value'])) {
            $this->phone = $data['contacts']['mobile'][0]['value'];
        }
        if(isset($data['locales'])){
            $this->locale = $data['locales'];
        }
        if(isset($data['logo']['url'])){
            $this->logo = $data['logo']['url'];
        }
        if(isset($data['images'])){
            $this->images = $data['images'];
        }
        if(isset($data['active_repairers_count'])){
            $this->activeRepairers = $data['active_repairers_count'];
        }
        if(isset($data['repaired_devices_count'])){
            $this->repairedDevices = $data['repaired_devices_count'];
        }
    }
}
