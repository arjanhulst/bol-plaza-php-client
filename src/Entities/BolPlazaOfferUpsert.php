<?php

namespace Arjanhulst\BolPlazaClient\Entities;

/**
 * Class BolPlazaOfferUpsert
 * @package Picqer\BolPlazaClient\Entities
 *
 * @property string $RetailerOffer
 */
class BolPlazaOfferUpsert extends BaseModel {

    protected $xmlEntityName = 'UpsertRequest';

    protected $nestedEntities = [
        'RetailerOffer' => 'BolPlazaRetailerOffer'
    ];
}
