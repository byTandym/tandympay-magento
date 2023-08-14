<?php
namespace Tandym\Tandympay\Api\Countryregion;

 

interface CountryregionInterface

{

 

  /**

  * Get country options list.

  *

  * @return array

  */

   public function getAllCountry();

 

   /**

  * Returns json data after processing to Countryregion

  *

  * @api

  * @return bool json data after processing to Countryregion.

  */

 public function getAllRegion();

 

}