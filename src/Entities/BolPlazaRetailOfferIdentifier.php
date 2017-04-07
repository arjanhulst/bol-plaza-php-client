<?php

namespace Arjanhulst\BolPlazaClient\Entities;

/**
 * Class BolPlazaOfferCreate
 * @package Picqer\BolPlazaClient\Entities
 *
 * @property string $EAN
 * @property string $Condition
 */
class BolPlazaRetailOfferIdentifier extends BaseModel {
    protected $xmlEntityName = 'RetailerOfferIdentifier';

    protected $attributes = [
        'EAN',
        'Condition'
    ];
}