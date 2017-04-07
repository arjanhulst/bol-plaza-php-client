<?php

namespace Arjanhulst\BolPlazaClient\Entities;

/**
 * Class BolPlazaOfferCreate
 * @package Picqer\BolPlazaClient\Entities
 *
 */
class BolPlazaDeleteBulkRequest extends BaseModel {

    protected $xmlEntityName = 'DeleteBulkRequest';

    protected $nestedEntities = [
        'RetailOfferIdentifier' => 'BolPlazaRetailOfferIdentifier'

    ];
}
