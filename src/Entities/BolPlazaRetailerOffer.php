<?php

namespace Arjanhulst\BolPlazaClient\Entities;

/**
 * Class BolPlazaRetailerOffer
 * @package Picqer\BolPlazaClient\Entities
 *
 * @property string $EAN
 * @property string $Condition
 * @property string $Price
 * @property string $DeliveryCode
 * @property string $QuantityInStock
 * @property string $Publish
 * @property string $ReferenceCode
 * @property string $Description
 */
class BolPlazaRetailerOffer extends BaseModel {

    protected $xmlEntityName = 'RetailerOffer';

    protected $attributes = [
        'EAN',
        'Condition',
        'Price',
        'DeliveryCode',
        'QuantityInStock',
        'Publish',
        'ReferenceCode',
        'Description',
        'FulfillmentMethod',
        'Title'
    ];
}
