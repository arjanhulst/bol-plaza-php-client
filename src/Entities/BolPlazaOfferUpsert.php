<?php

namespace Arjanhulst\BolPlazaClient\Entities;

/**
 * Class BolPlazaOfferCreate
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
class BolPlazaOfferUpsert extends BaseModel {

    protected $xmlEntityName = 'UpsertRequest';

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
