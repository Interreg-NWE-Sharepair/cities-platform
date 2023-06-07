<?php

namespace modules\statik\models;

use yii\base\Model;

class Address extends Model
{
    public $street; // address -> street, number, bus
    public $postalCode; // address -> postal_code
    public $city; // address -> city
    public $country; // address -> country
    

    public function getAddress($data)
    {
        if (isset($data['street'])) {
            $this->street = $data['street'].' '.$data['number'].' '.$data['bus'];
        }
        if(isset($data['postal_code'])){
            $this->postalCode = $data['postal_code'];
        }
        if(isset($data['city'])){
            $this->city = $data['city'];
        }
        if(isset($data['country'])){
            $this->country = $data['country'];
        }
    }
}
