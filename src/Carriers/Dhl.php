<?php

namespace Webkul\DHLShipping\Carriers;

use Webkul\Checkout\Models\CartShippingRate;
use Webkul\Checkout\Facades\Cart;
use Webkul\Shipping\Carriers\AbstractShipping;
use Illuminate\Support\Str;

/**
 * Class Rate.
 *
 */
class Dhl extends AbstractShipping
{

    /**
     * DHLShipping helper
     *
     * @var string
     */
    protected $dhlShipping;

    /**
     * DHLShipping gateway url
     *
     * @var string
     */
    protected $gatewayUrl;

    /**
     * Payment method code
     *
     * @var string
     */
    protected $code  = 'dhl';

    /**
     * Returns rate for DHL Shipping
     *
     * @return array
     */
    public function calculate()
    {
        
        if (! $this->isAvailable()) {
            return false;
        }

        $cart = Cart::getCart();

        $allowedCountries = explode(',', core()->getConfigData('sales.carriers.dhl.allowed_country'));

        if (! in_array($cart->shipping_address->country, $allowedCountries)) {

            return false;
        }

        $shippingpricedetail =  $this->collectRates();

        if ($shippingpricedetail == false) {

            return false;
        }

        if($shippingpricedetail['errormsg'] != ''){

            return false;
            session()->flash('error', 'something went wrong');

		} else {

			$totalPriceArr=$shippingpricedetail['handlingfee']['totalprice'];
			$serviceCodeToActualNameMap=$shippingpricedetail['handlingfee']['servicecodetoactualnamemap'];
            $costArr=$shippingpricedetail['handlingfee']['costarr'];

            $shippingMethods = [];

            foreach ($totalPriceArr as $method=>$price) {

                $result = new CartShippingRate;
                $result->carrier = $method;
                $result->carrier_title = $serviceCodeToActualNameMap[$method];
                $result->method = 'dhl';
                $result->method_title = $this->getConfigData('title');
                $result->method_description = $serviceCodeToActualNameMap[$method];
                $result->price = core()->convertPrice($price);
                $result->is_calculate_tax = $this->getConfigData('is_calculate_tax');
                $result->base_price = $price;
                $shippingMethods[] = $result;

            }
            
            return $shippingMethods;
        }

    }

    /**
     * Returns DHL shipment methods (depending on package content type, if necessary)
     *
     * @param string $doc Package content type (doc/non-doc) see DHL_CONTENT_TYPE_* constants
     * @return array
     */
    public function getDhlProducts($doc)
    {
        $docType = [
            '2' => __('Easy shop'),
            '5' => __('Sprintline'),
            '6' => __('Secureline'),
            '7' => __('Express easy'),
            '9' => __('Europack'),
            'B' => __('Break bulk express'),
            'C' => __('Medical express'),
            'D' => __('Express worldwide'),
            'U' => __('Express worldwide'),
            'K' => __('Express 9:00'),
            'L' => __('Express 10:30'),
            'G' => __('Domestic economy select'),
            'W' => __('Economy select'),
            'I' => __('Domestic express 9:00'),
            'N' => __('Domestic express'),
            'O' => __('Others'),
            'R' => __('Globalmail business'),
            'S' => __('Same day'),
            'T' => __('Express 12:00'),
            'X' => __('Express envelope'),
        ];

        $nonDocType = [
            '1' => __('Domestic express 12:00'),
            '3' => __('Easy shop'),
            '4' => __('Jetline'),
            '8' => __('Express easy'),
            'P' => __('Express worldwide'),
            'Q' => __('Medical express'),
            'E' => __('Express 9:00'),
            'F' => __('Freight worldwide'),
            'H' => __('Economy select'),
            'J' => __('Jumbo box'),
            'M' => __('Express 10:30'),
            'V' => __('Europack'),
            'Y' => __('Express 12:00'),
        ];

        if ($this->_isDomestic) {
            return $docType + $nonDocType;
        }
        if ($doc == DhlCarrier::DHL_CONTENT_TYPE_DOC) {
            // Documents shipping
            return $docType;
        } else {
            // Services for shipping non-documents cargo
            return $nonDocType;
        }
    }

   /*
    * return the DhL shipping rates
    */
    public function collectRates()
    {

        if(!core()->getConfigData('sales.carriers.dhl.active')){
			 return false;
        }

        $cart = Cart::getCart();

        $shippostaldetail = $cart->shipping_address;

        $shippingdetail = [];

        $validCartItems = $this->getValidCartItems($cart->items()->get());

		foreach($validCartItems as $item) {

            $partner = 0;

            if(count($shippingdetail)==0){
                array_push($shippingdetail,array('seller_id'=>$partner,'items_weight'=>$item->weight,'product_name'=>$item->name,'qty'=>$item->quantity,'item_id'=>$item->id,'price'=>$item->total));
            }else{

                $shipinfoflag=true;
                $index=0;

                foreach($shippingdetail as $itemship){
                    if ( $itemship['seller_id'] == $partner){

                        $itemship['items_weight'] = $itemship['items_weight']+($item->weight *$item->quantity);
                        $itemship['product_name'] = $itemship['product_name'].",".$item->name;
                        $itemship['qty'] = $itemship['qty']+$item->quantity;
                        $itemship['item_id'] = $itemship['item_id'].",".$item->id;
                        $itemship['price'] = $itemship['price']+$item->price;
                        $shippingdetail[$index] = $itemship;
                        $shipinfoflag=false;
                    }
                    $index++;
                }

                if($shipinfoflag==true){
                    array_push($shippingdetail,['seller_id'=>$partner,'items_weight'=>$item->weight,'product_name'=>$item->name,'qty'=>$item->quantity,'item_id'=>$item->id,'price'=>$item->price]);
                }
            }
        }

        $shippingpricedetail = $this->getShippingPricedetail($shippingdetail,$shippostaldetail);

        return $shippingpricedetail;

    }

    public function getValidCartItems($cartItems)
    {
        $adminProducts = [];

        foreach ($cartItems as $item) {
            if ($item->product->type != 'virtual' && $item->product->type != 'downloadable' && $item->product->type != 'booking') {

                array_push($adminProducts, $item);
            }
        }

        return $adminProducts;
    }

    public function getAllowedMethods()
    {
        return core()->getConfigData('sales.carriers.dhl.allowed_methods');
    }

    /**
     * getShippingPricedetail Calculate the rate quote
     * @param  Mixed $shippingdetail
     * @param  Mixed $shippostaldetail
     * @return Mixed  DHL Methods and Rates.
     */
    public function getShippingPricedetail($shippingdetail,$shippostaldetail){

        $shippinginfo = [];
		$submethod = [];
		$totalpric = [];
		$totalPriceArr = [];
		$serviceCodeToActualNameMap = [];
		$costArr = [];
		$errormsg = "";
		$i = 1;
		$check= false;
 		$flag = false;
        /***/

		foreach($shippingdetail as $shipdetail){
			$priceArr =  array();

            $data = $this->createRequest($shipdetail, $shippostaldetail);

            $tuCurl = curl_init();
            curl_setopt($tuCurl, CURLOPT_URL, $this->gatewayUrl);
            curl_setopt($tuCurl, CURLOPT_PORT , 443);
            curl_setopt($tuCurl, CURLOPT_VERBOSE, 0);
            curl_setopt($tuCurl, CURLOPT_HEADER, 0);
            curl_setopt($tuCurl, CURLOPT_POST, 1);
            curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($tuCurl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($tuCurl, CURLOPT_HTTPHEADER, array("Content-Type: text/xml","SOAPAction: \"/soap/action/query\"", "Content-length: ".strlen($data)));

            $tuData = curl_exec($tuCurl);
            curl_close($tuCurl);
            $simple = $tuData;
            $xml = simplexml_load_string($tuData);

			if(is_object($xml)) {
				$allowedMethods = $this->getAllowedMethods();
                $rateFlag = false;
				 if (isset($xml->GetQuoteResponse->BkgDetails->QtdShp)) {

					foreach ($xml->GetQuoteResponse->BkgDetails->QtdShp as $quotedShipment) {
						$dhlServiceCode  = (string)$quotedShipment->GlobalProductCode;
                        $totalEstimate   = (float)(string)$quotedShipment->ShippingCharge;

						$dhlServiceName   = $this->getDhlProductTitle($dhlServiceCode);
						$serviceCodeToActualNameMap[$dhlServiceCode] = $dhlServiceName;
						$currencyCode     = (string)$quotedShipment->CurrencyCode;
						$baseCurrencyCode = core()->getBaseCurrencyCode();
						if ($currencyCode != $baseCurrencyCode) {
			               $totalEstimate = $this->get_currency($currencyCode,$baseCurrencyCode,$totalEstimate);
			            }
						if ($dhlServiceName && Str::contains($allowedMethods ,$dhlServiceName))
						{
								$rateFlag = true;
								$costArr[$dhlServiceCode] = $totalEstimate;
								$priceArr[$dhlServiceCode] = $totalEstimate;

						}
					}
					asort($priceArr);
				}else{
					$errormsg = 'Error';
				}
			}
			if($rateFlag == false){

				return false;
			}

			//calculate item wise dhl shipping
			$items = explode(',', $shipdetail['item_id']);
			$newShipderails = [];
			$newShipderails['seller_id'] = $shipdetail['seller_id'];
            $itemPriceDetails = [];

			foreach ($items as $itemId) {

				$newShipderails['items_weight'] = $shipdetail['items_weight'];
				$newShipderails['price'] = $shipdetail['price'] * $shipdetail['qty'];

                $data =  $this->createRequest($newShipderails, $shippostaldetail);

                $tuCurl = curl_init();
                curl_setopt($tuCurl, CURLOPT_URL, $this->gatewayUrl);
                curl_setopt($tuCurl, CURLOPT_PORT , 443);
                curl_setopt($tuCurl, CURLOPT_VERBOSE, 0);
                curl_setopt($tuCurl, CURLOPT_HEADER, 0);
                curl_setopt($tuCurl, CURLOPT_POST, 1);
                curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($tuCurl, CURLOPT_POSTFIELDS, $data);
                curl_setopt($tuCurl, CURLOPT_HTTPHEADER, ["Content-Type: text/xml","SOAPAction: \"/soap/action/query\"", "Content-length: ".strlen($data)]);
                $tuData = curl_exec($tuCurl);
                curl_close($tuCurl);

                $simple = $tuData;
				$itemPriceDetails[$itemId] = $this->parseResponseObject($simple);
            }

            /*
            *Calculation for common DHL Methods
            */
			if (count($totalPriceArr) > 0) {
                  foreach ($priceArr as $method => $price) {
                      if (array_key_exists($method, $totalPriceArr)) {
                          $check = true;
                          $totalPriceArr[$method] = $totalPriceArr[$method]+$priceArr[$method];
                      } else {
                          unset($priceArr[$method]);
                          $flag = $check==false?false:true;
                      }
                }
            } else {
                $totalPriceArr = $priceArr;
            } if (count($priceArr) > 0) {

                foreach ($totalPriceArr as $method => $price) {
                    if(!array_key_exists($method, $priceArr)){
                    unset($totalPriceArr[$method]);
                    }
                }

            } else {
                $totalPriceArr = array();
                $flag = true;
            } if ($flag) {
                return false;
            }
            /* End Calculation for common DHL Methods*/

            $submethod = [];

            foreach ($priceArr as $index=>$price) {
				$method=$serviceCodeToActualNameMap[$index];
				$submethod[$index] = ['method'=>$method,'cost'=>$price];
            }

			if (!isset($shipdetail['item_id_details'])) {
				$shipdetail['item_id_details'] = [];
			}
			if (!isset($shipdetail['item_name_details'])) {
				$shipdetail['item_name_details'] = [];
			}
			if (!isset($shipdetail['item_qty_details'])) {
				$shipdetail['item_qty_details'] = [];
            }

			 array_push($shippinginfo,['seller_id'=>$shipdetail['seller_id'],'methodcode'=>$this->code,'shipping_ammount'=>$price,'product_name'=>$shipdetail['product_name'],'submethod'=>$submethod,'item_ids'=>$shipdetail['item_id'], 'item_price_details'=>$itemPriceDetails, 'item_id_details'=>$shipdetail['item_id_details'], 'item_name_details'=>$shipdetail['item_name_details'], 'item_qty_details'=>$shipdetail['item_qty_details']]);
			 $i++;
		}

        $totalpric = ['totalprice'=>$totalPriceArr,'servicecodetoactualnamemap'=>$serviceCodeToActualNameMap,'costarr'=>$costArr];

		return ['handlingfee'=>$totalpric,'shippinginfo'=>$shippinginfo,'errormsg'=>$errormsg];
    }

    /**
     *
     * @param array $shipdtail
     * @param array $shippostaldetail
     * @return xml object for the DHL Shipping rates
     */

     protected function createRequest($shipdetail, $shippostaldetail) {

        $originpostcodes = core()->getConfigData('sales.shipping.origin.zipcode');
        $this->origincountrycode = core()->getConfigData('sales.shipping.origin.country');
        $originCity = core()->getConfigData('sales.shipping.origin.city');
        $accessId = core()->getConfigData('sales.carriers.dhl.access_id');
        $accountNumber = core()->getConfigData('sales.carriers.dhl.account_number');
        $password = core()->getConfigData('sales.carriers.dhl.password');

		if ($this->origincountrycode == $shippostaldetail['countrycode']) {
			$this->_isDomestic = true;
		}
		if (core()->getConfigData('sales.carriers.dhl.sandbox_mode') == 1) {
			$this->gatewayUrl = 'https://xmlpitest-ea.dhl.com/XMLShippingServlet';
		} else {
			$this->gatewayUrl =  'https://xmlpi-ea.dhl.com/XMLShippingServlet';
        }

        // Call to the Rate adapter
        $xmlStr = '<?xml version = "1.0" encoding = "UTF-8"?>'
            . '<p:DCTRequest xmlns:p="http://www.dhl.com" xmlns:p1="http://www.dhl.com/datatypes" '
            . 'xmlns:p2="http://www.dhl.com/DCTRequestdatatypes" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
            . 'xsi:schemaLocation="http://www.dhl.com DCT-req.xsd "/>';
        $xml = new \SimpleXMLElement($xmlStr);
        $nodeGetQuote = $xml->addChild('GetQuote', '', '');
        $nodeRequest = $nodeGetQuote->addChild('Request');

        $nodeServiceHeader = $nodeRequest->addChild('ServiceHeader');
        $nodeServiceHeader->addChild('SiteID', $accessId);
        $nodeServiceHeader->addChild('Password', $password);

        $nodeFrom = $nodeGetQuote->addChild('From');
        $nodeFrom->addChild('CountryCode', $this->origincountrycode);
        $nodeFrom->addChild('Postalcode', $originpostcodes);
        $nodeFrom->addChild('City', $originCity);

        $nodeBkgDetails = $nodeGetQuote->addChild('BkgDetails');
        $nodeBkgDetails->addChild('PaymentCountryCode',$shippostaldetail['country']);
        $nodeBkgDetails->addChild('Date', date("Y-m-d"));
        $nodeBkgDetails->addChild('ReadyTime', 'PT' . (int)(string)core()->getConfigData('sales.carriers.dhl.ready_time') . 'H00M');
        $nodeBkgDetails->addChild('DimensionUnit', 'CM');
        $nodeBkgDetails->addChild('WeightUnit','KG');
        //$this->_makePieces($nodeBkgDetails);

        $nodePieces = $nodeBkgDetails->addChild('Pieces', '', '');
        $nodePiece = $nodePieces->addChild('Piece', '', '');
        $nodePiece->addChild('PieceID', 1);
        $nodePiece->addChild('Weight', round($shipdetail['items_weight'], 3));

        $nodeBkgDetails->addChild('PaymentAccountNumber', $accountNumber);

        $nodeTo = $nodeGetQuote->addChild('To');
        $nodeTo->addChild('CountryCode', $shippostaldetail['country']);
        $nodeTo->addChild('Postalcode',  $shippostaldetail['postcode']);
        $nodeTo->addChild('City', $shippostaldetail['city']);

        if (core()->getConfigData('sales.carriers.dhl.content_type') == 'non documents'
            && $this->origincountrycode != $shippostaldetail['countrycode']) {

            // IsDutiable flag and Dutiable node indicates that cargo is not a documentation
            $nodeBkgDetails->addChild('IsDutiable', 'Y');
            $nodeDutiable = $nodeGetQuote->addChild('Dutiable');
            $baseCurrencyCode = core()->getBaseCurrency()->code;
            $nodeDutiable->addChild('DeclaredCurrency', 'EUR');
            $nodeDutiable->addChild('DeclaredValue', '90');
        }

        return $xml->asXML();
    }

    public function getDhlProductTitle($code)
    {
        $products = [
            '2' => 'Easy shop',
            '5' => 'Sprintline',
            '6' => 'Secureline',
            '7' => 'Express easy',
            '9' => 'Europack',
            'B' => 'Break bulk express',
            'C' => 'Medical express',
            'D' => 'Express worldwide',
            'U' => 'Express worldwide',
            'K' => 'Express 9:00',
            'L' => 'Express 10:30',
            'G' => 'Domestic economy select',
            'W' => 'Economy select',
            'I' => 'Domestic express 9:00',
            'N' => 'Domestic express',
            'O' => 'Others',
            'R' => 'Globalmail business',
            'S' => 'Same day',
            'T' => 'Express 12:00',
            'X' => 'Express envelope',
            '1' => 'Domestic express 12:00',
            '3' => 'Easy shop',
            '4' => 'Jetline',
            '8' => 'Express easy',
            'P' => 'Express worldwide',
            'Q' => 'Medical express',
            'E' => 'Express 9:00',
            'F' => 'Freight worldwide',
            'H' => 'Economy select',
            'J' => 'Jumbo box',
            'M' => 'Express 10:30',
            'V' => 'Europack',
            'Y' => 'Express 12:00',
        ];

        if (array_key_exists($code, $products)){
            return $products[$code[0]];
        }
    }

    function get_currency($from_Currency, $to_Currency, $amount)
    {
		$amount = urlencode($amount);
		$from_Currency = urlencode($from_Currency);
		$to_Currency = urlencode($to_Currency);

		$url = "http://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency";

		$timeout = 0;
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$rawdata = curl_exec($ch);
		curl_close($ch);
        $data = explode('bld>', $rawdata);

		if (count($data) < 2) {
			$data[1] = '';
		}
		$data = explode($to_Currency, $data[1]);

        return round($data[0], 2);
    }

    protected function parseResponseObject($response) {

        $itemPriceDetails = [];
   	 	$xml = simplexml_load_string($response);

        if (is_object($xml)) {
            $allowedMethods = $this->getAllowedMethods();
			 if (isset($xml->GetQuoteResponse->BkgDetails->QtdShp)) {
                 foreach ($xml->GetQuoteResponse->BkgDetails->QtdShp as $quotedShipment) {

                     $rateFlag = false;
                     $dhlServiceCode  = (string)$quotedShipment->GlobalProductCode;
                     $totalEstimate   = (float)(string)$quotedShipment->ShippingCharge;
                     $dhlServiceName   = $this->getDhlProductTitle($dhlServiceCode);
                     $serviceCodeToActualNameMap[$dhlServiceCode] = $dhlServiceName;
                     $currencyCode     = (string)$quotedShipment->CurrencyCode;
                     $baseCurrencyCode = core()->getBaseCurrencyCode();

                     if ($currencyCode != $baseCurrencyCode) {
		               $totalEstimate = $this->get_currency($currencyCode,$baseCurrencyCode,$totalEstimate);
		            }
					if ($quotedShipment->GlobalProductCode)
					{
						if($totalEstimate){
							$rateFlag = true;
							$itemPriceDetails[$dhlServiceCode] = $totalEstimate;
						}
					}
				}
			} else {
				$errormsg = 'Error';
			}
		}
		if($rateFlag == false){
			return false;
		}
		return $itemPriceDetails;
    }
}