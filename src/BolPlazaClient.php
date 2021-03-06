<?php

namespace Arjanhulst\BolPlazaClient;

use Arjanhulst\BolPlazaClient\Entities\BolPlazaReturnItem;
use Arjanhulst\BolPlazaClient\Entities\BolPlazaReturnItemStatusUpdate;
use Arjanhulst\BolPlazaClient\Entities\BolPlazaProcessStatus;
use Arjanhulst\BolPlazaClient\Entities\BolPlazaOrderItem;
use Arjanhulst\BolPlazaClient\Entities\BolPlazaCancellation;
use Arjanhulst\BolPlazaClient\Entities\BolPlazaOfferFile;
use Arjanhulst\BolPlazaClient\Entities\BolPlazaShipment;
use Arjanhulst\BolPlazaClient\Entities\BolPlazaChangeTransportRequest;
use Arjanhulst\BolPlazaClient\Entities\BolPlazaOfferUpsert;
use Arjanhulst\BolPlazaClient\Entities\BolPlazaDeleteBulkRequest;
use Arjanhulst\BolPlazaClient\Entities\BolPlazaShipmentRequest;
use Arjanhulst\BolPlazaClient\Entities\BolPlazaStockUpdate;
use Arjanhulst\BolPlazaClient\Exceptions\BolPlazaClientException;
use Arjanhulst\BolPlazaClient\Exceptions\BolPlazaClientRateLimitException;

class BolPlazaClient
{
    const URL_LIVE = 'https://plazaapi.bol.com';
    const URL_TEST = 'https://test-plazaapi.bol.com';
    const API_VERSION = 'v2';

    private $testMode = false;
    private $skipSslVerification = false;

    private $publicKey;
    private $privateKey;

    /**
     * BolPlazaClient constructor.
     * @param $publicKey
     * @param $privateKey
     */
    public function __construct($publicKey, $privateKey)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    /**
     * Enable or disable testmode (default disabled)
     * @param $mode boolean
     */
    public function setTestMode($mode)
    {
        $this->testMode = $mode;
    }

    /**
     * Skip SSL verification in communication with server, only use in test cases
     * @param bool|true $mode
     */
    public function setSkipSslVerification($mode = true)
    {
        $this->skipSslVerification = $mode;
    }

    /**
     * Get list of orders
     * @return array
     */
    public function getOrders()
    {
        $url = '/services/rest/orders/' . self::API_VERSION;

        $apiResult = $this->makeRequest('GET', $url);
        $orders = BolPlazaDataParser::createCollectionFromResponse('BolPlazaOrder', $apiResult);

        return $orders;
    }

    /**
     * Get list of shipments
     * @param int $page The page of the set of shipments
     * @return array
     */
    public function getShipments($page = 1)
    {
        $url = '/services/rest/shipments/' . self::API_VERSION;
        $apiResult = $this->makeRequest('GET', $url, array("page" => $page));
        $shipments = BolPlazaDataParser::createCollectionFromResponse('BolPlazaShipment', $apiResult);
        return $shipments;
    }

    /**
     * Get list of BolPlazaReturnItem entities
     * @return array
     */
    public function getReturnItems()
    {
        $url = '/services/rest/return-items/' . self::API_VERSION . '/unhandled';
        $apiResult = $this->makeRequest('GET', $url);
        $returnItems = BolPlazaDataParser::createCollectionFromResponse('BolPlazaReturnItem', $apiResult);
        return $returnItems;
    }

    /**
     * Get list of BolPlazaPayment entities
     * @return array
     */
    public function getPayments($period)
    {
        $url = '/services/rest/payments/' . self::API_VERSION . '/' . $period;
        $apiResult = $this->makeRequest('GET', $url);
        $payments = BolPlazaDataParser::createCollectionFromResponse('BolPlazaPayment', $apiResult);
        return $payments;
    }

    /**
     * Handle a BolPlazaReturnItem
     * @param BolPlazaReturnItem $returnItem
     * @param BolPlazaReturnItemStatusUpdate $status
     * @return BolPlazaProcessStatus
     */
    public function handleReturnItem(Entities\BolPlazaReturnItem $returnItem, Entities\BolPlazaReturnItemStatusUpdate $status)
    {
        $url = '/services/rest/return-items/' . self::API_VERSION . '/' . $returnItem->ReturnNumber . '/handle';
        $xmlData = BolPlazaDataParser::createXmlFromEntity($status);
        $apiResult = $this->makeRequest('PUT', $url, $xmlData);
        $result = BolPlazaDataParser::createEntityFromResponse('BolPlazaProcessStatus', $apiResult);
        return $result;
    }

    /**
     * Cancel an OrderItem
     * @param BolPlazaOrderItem $orderItem
     * @param BolPlazaCancellation $cancellation
     * @return BolPlazaProcessStatus
     */
    public function cancelOrderItem(Entities\BolPlazaOrderItem $orderItem, Entities\BolPlazaCancellation $cancellation)
    {
        $url = '/services/rest/order-items/' . self::API_VERSION . '/' . $orderItem->OrderItemId . '/cancellation';
        $xmlData = BolPlazaDataParser::createXmlFromEntity($cancellation);
        $apiResult = $this->makeRequest('PUT', $url, $xmlData);
        $result = BolPlazaDataParser::createEntityFromResponse('BolPlazaProcessStatus', $apiResult);
        return $result;
    }

    /**
     * Change Transport
     * @param BolPlazaShipment $shipment
     * @param BolPlazaChangeTransportRequest $changeRequest
     * @return BolPlazaProcessStatus
     */
    public function changeTransport(Entities\BolPlazaShipment $shipment, Entities\BolPlazaChangeTransportRequest $changeRequest)
    {
        $url = '/services/rest/transports/' . self::API_VERSION . '/' . $shipment->Transport->TransportId;
        $xmlData = BolPlazaDataParser::createXmlFromEntity($changeRequest);
        $apiResult = $this->makeRequest('PUT', $url, $xmlData);
        $result = BolPlazaDataParser::createEntityFromResponse('BolPlazaProcessStatus', $apiResult);
        return $result;
    }

    /**
     * Add a shipment
     * @param BolPlazaShipmentRequest $shipmentRequest
     * @return BolPlazaProcessStatus
     */
    public function processShipment(Entities\BolPlazaShipmentRequest $shipmentRequest)
    {
        $url = '/services/rest/shipments/' . self::API_VERSION;
        $xmlData = BolPlazaDataParser::createXmlFromEntity($shipmentRequest);
        $apiResult = $this->makeRequest('POST', $url, $xmlData);
        $result = BolPlazaDataParser::createEntityFromResponse('BolPlazaProcessStatus', $apiResult);
        return $result;
    }

    /**
     * Get the ProcessStatus
     * @param string $processStatusId
     * @return BolPlazaProcessStatus
     */
    public function getProcessStatus($processStatusId)
    {
      $url = '/services/rest/process-status/' . self::API_VERSION . '/' . $processStatusId;
      $apiResult = $this->makeRequest('GET', $url);
      $result = BolPlazaDataParser::createEntityFromResponse('BolPlazaProcessStatus', $apiResult);
      return $result;
    }

    /**
     * Create an offer
     * @param string $ean
     * @param string|bool $condition
     * @return string $apiResult
     */
    public function getOffer($ean, $condition = false)
    {
        $url = '/offers/' . self::API_VERSION . '/' . $ean;
        if($condition) $url .='?condition='.$condition;
        $apiResult = $this->makeRequest('GET', $url);

        $offer = BolPlazaDataParser::createCollectionFromResponse('BolPlazaRetailerOffer', $apiResult,'RetailerOffer');
        return $offer;
    }

    /**
     * Upsert an offer
     * @param BolPlazaOfferUpsert $offerUpsert
     * @return string|bool $apiResult
     */
    public function upsertOffer(Entities\BolPlazaOfferUpsert $offerUpsert)
    {
        $url = '/offers/' . self::API_VERSION;
        $xmlData = BolPlazaDataParser::createOfferXmlFromEntity($offerUpsert);
        $apiResult = $this->makeRequest('PUT', $url, $xmlData);
        return $apiResult;
    }

    /**
     * Update an offer stock
     * @param string $offerId
     * @param BolPlazaStockUpdate $stockUpdate
     * @return string $apiResult
     */
    public function updateOfferStock($offerId, Entities\BolPlazaStockUpdate $stockUpdate)
    {
        $url = '/offers/' . self::API_VERSION . '/' . $offerId . '/stock';
        $xmlData = BolPlazaDataParser::createOfferXmlFromEntity($stockUpdate);
        $apiResult = $this->makeRequest('PUT', $url, $xmlData);
        return $apiResult;
    }

    /**
     * Delete an offer
     * @param BolPlazaDeleteBulkRequest $offerDelete
     * @return string $apiResult
     */
    public function deleteOffer(Entities\BolPlazaDeleteBulkRequest $offerDelete)
    {
        $url = '/offers/' . self::API_VERSION;
        $xmlData = BolPlazaDataParser::createOfferXmlFromEntity($offerDelete);
        $apiResult = $this->makeRequest('DELETE', $url,$xmlData);
        return $apiResult;
    }

    /**
     * Get own offers file path
     * @param string $filter
     * @return BolPlazaOfferFile
     */
    public function getOwnOffers($filter = '')
    {
      $url = '/offers/' . self::API_VERSION . '/export';
      $data = [];
      if(!empty($filter)) {
          $data['filter'] = $filter;
      }
      $apiResult = $this->makeRequest('GET', $url, $data);
      $result = BolPlazaDataParser::createEntityFromResponse('BolPlazaOfferFile', $apiResult);
      return $result;
    }

    /**
     * Get the own offers file contents
     * @param string $path
     * @return string
     */
    public function getOwnOffersResult($path = '')
    {
        $path = str_replace(self::URL_TEST, '', $path);
        $path = str_replace(self::URL_LIVE, '', $path);
        $apiResult = $this->makeRequest('GET', $path);
        return $apiResult;
    }

    /**
     * Makes the request to the server and processes errors
     *
     * @param string $method GET
     * @param string $endpoint URI of the resource
     * @param null|string $data POST data
     * @return string XML
     * @throws BolPlazaClientException
     * @throws BolPlazaClientRateLimitException
     */
    protected function makeRequest($method = 'GET', $endpoint, $data = null)
    {
        $date = gmdate('D, d M Y H:i:s T');
        $contentType = 'application/xml';
        $url = $this->getUrlFromEndpoint($endpoint);

        $signature = $this->getSignature($method, $contentType, $date, $endpoint);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Picqer BolPlaza PHP Client (picqer.com)');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-type: ' . $contentType,
            'X-BOL-Date: ' . $date,
            'X-BOL-Authorization: ' . $signature
        ]);

        if (in_array($method, ['POST', 'PUT', 'DELETE']) && ! is_null($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } elseif ($method == 'GET' && !empty($data)) {
            $suffix = "?";
            foreach ($data as $key => $value) {
              $suffix .= $key . '=' . $value;
            }
            curl_setopt($ch, CURLOPT_URL, $url . $suffix);
        }

        if ($this->skipSslVerification) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $result = curl_exec($ch);
        $headerInfo = curl_getinfo($ch);
        $this->checkForErrors($ch, $headerInfo, $result);

        curl_close($ch);

        return $result;
    }

    /**
     * Get URL from endpoint
     *
     * @param string $endpoint
     * @return string
     */
    protected function getUrlFromEndpoint($endpoint)
    {
        if ($this->testMode) {
            return self::URL_TEST . $endpoint;
        } else {
            return self::URL_LIVE . $endpoint;
        }
    }

    /**
     * Calculates signature for request
     *
     * @param string $method HTTP method
     * @param string $contentType Probably only application/xml
     * @param string $date Current time (can only be 15 mins apart from Bol servers)
     * @param string $endpoint Endpoint without url
     * @return string
     */
    protected function getSignature($method, $contentType, $date, $endpoint)
    {
        $signatureBase = $method . "\n\n";
        $signatureBase .= $contentType . "\n";
        $signatureBase .= $date . "\n";
        $signatureBase .= 'x-bol-date:' . $date . "\n";
        $signatureBase .= $endpoint;

        $signature = $this->publicKey . ':' . base64_encode(hash_hmac('SHA256', $signatureBase, $this->privateKey, true));

        return $signature;
    }

    /**
     * Check if the API returned any errors
     *
     * @param resource $ch The CURL resource of the request
     * @param array $headerInfo
     * @param string $result
     * @throws BolPlazaClientException
     * @throws BolPlazaClientRateLimitException
     */
    protected function checkForErrors($ch, $headerInfo, $result)
    {
        if (curl_errno($ch)) {
            throw new BolPlazaClientException(curl_errno($ch));
        }

        if ( ! in_array($headerInfo['http_code'], array('200', '201', '204'))) // API returns error
        {
            if ($headerInfo['http_code'] == '409')
            {
                throw new BolPlazaClientRateLimitException;
            }
            if(!empty($result)) {
                $xmlObject = BolPlazaDataParser::parseXmlResponse($result);
                if (isset($xmlObject->ErrorCode) && !empty($xmlObject->ErrorCode))
                {
                    throw new BolPlazaClientException($xmlObject->ErrorMessage, (int)$xmlObject->ErrorCode);
                }
            }
        }
    }
}
