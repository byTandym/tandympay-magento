<?php

declare(strict_types=1);

namespace Tandym\Tandympay\Model\Countryregion;

 

use Magento\Framework\App\RequestInterface;

use Magento\Store\Model\StoreManagerInterface;

use Magento\Framework\App\ObjectManager;

use Magento\Directory\Model\Country as country;

use Magento\Directory\Model\CountryFactory;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;

use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;

 

use  Tandym\Tandympay\Api\Countryregion\CountryregionInterface;

 

class Countryregion implements CountryregionInterface

 

{

   protected $country;

   protected $countryFactory;

 

   protected $storeManager;

   /**

   *

   * @param StoreManagerInterface $storeManager

   */

 

   protected $request;

   /**

    *

    * @param RequestInterface $request

    */

 

   protected $countryCollectionFactory;

   /**

    *

    * @param countryCollectionFactory $countryCollectionFactory

    */

   protected $regionCollectionFactory;

   /**

    *

    * @param regionCollectionFactory $regionCollectionFactory

    */

  

   public function __construct(

       RequestInterface $request,

       CountryCollectionFactory $countryCollectionFactory,

       RegionCollectionFactory $regionCollectionFactory,

       StoreManagerInterface $storeManager = null,

       country $country,

       countryFactory $countryFactory

   ) {

       $this->request = $request;

       $this->country = $country;

       $this->countryCollectionFactory = $countryCollectionFactory;

       $this->regionCollectionFactory = $regionCollectionFactory;

       $this->storeManager = $storeManager;

       $this->countryFactory = $countryFactory;

   }

    /**

    * {@inheritdoc}

    */

   public function getAllCountry()

   {

     $collection = $this->countryCollectionFactory->create()->loadByStore();

     $counties =  $collection->getData();

     $res = array();

     $count = 0;

     foreach($counties as $country) {

       $res[$count]['country_id'] = $country['country_id'];

       $countryy = $this->countryFactory->create()->loadByCode($country['country_id']);

       $res[$count]['country'] = $countryy->getName();

       $count++;

     }

     return $res;

  }

   /**

  * Returns json data after processing to Countryregion

  *

  * @api

  * @return bool json data after processing to Countryregion.

  */

 public function getAllRegion(){

   $countryCode  = $this->request->getParam('country_id');

   $regionCollection = $this->country->loadByCode($countryCode)->getRegions();

   $regions = $regionCollection->loadData()->toOptionArray(false);

   return $regions;

 }

}