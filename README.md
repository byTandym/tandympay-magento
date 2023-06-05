## Tandym Extension for Magento 2


## Introduction
This document will help you in installing `Tandym's Magento 2` extension.


## How to install the extension?


### Manual
* Download the .zip or tar.gz file from `Tandym's`.
* Unzip the file and follow the following instructions.
* Navigate to `Magento` `[Magento]/app/code/` either through `SFTP` or `SSH`.
* Copy `Tandym` directory from unzipped folder to `[Magento]/app/code/`.
* Open the terminal.
* Run the following command to enable `Tandym`:
```php bin/magento module:enable Tandym_Tandympay```
* Run the `Magento` setup upgrade:
```php bin/magento setup:upgrade```
* Run the `Magento` Dependencies Injection Compile:
```php bin/magento setup:di:compile```
* Run the `Magento` Static Content deployment:
```php bin/magento setup:static-content:deploy -f```
* Login to `Magento` Admin and navigate to `System > Cache Management`.
* Flush the cache storage by selecting `Flush Cache Storage`.


## Configure Tandym


### Payment Configuration


* Make sure you have the `API Key` and the `Secret` from the [`Tandym Merchant Dashboard`]
* Navigate to `Stores > Configuration > Sales > Payment Methods > Tandym > Tandym Payment Settings` in your `Magento` admin.
* Set `Enabled` as `Yes` to activate Tandym as a payment option.
* Set the Payment Mode to `Live` for LIVE and set it as `Sandbox` for SANDBOX.
* Set the `Program Name`, `Description`, `Domain`, `Program Logo URL`, `API Key` and `Secret`.
* Set `Payment from Applicable Countries` to `Specific Countries`.
* Set `Payment from Specific Countries` to `United States` Tandym is currently available for US only.
* Set `Sort Order` to manage the position of Tandym in the checkout payment options list.
* Save the configuration and clear the cache.


### Tandym Widget Configuration


* Set `Earn Rate` as per [`Tandym Merchant Dashboard`].
* Set `First Purchase Discount` as per [`Tandym Merchant Dashboard`].
* Set `Enable Tandym Widget in Product Page` to `Yes` for adding widget script in the Product Display Page which will help in enabling `Tandym MApps Widget` Modal in PDP.
* Set `Enable Tandym Widget Cart Page` to `Yes` for adding widget script in the Cart Page which will help in enabling `Tandym MApps Widget` Modal in Cart Page.
* Set `Enable  Tandym Widget in Checkout Page` to `Yes` for adding widget script in the Cart Page which will help in enabling `Tandym MApps Widget` Modal in Checkout Page.
* Save the configuration and clear the cache.


### Congratulations !!! Your store is now ready to accept payments through Tandym.


## Frontend Functionality


* If you have correctly set up `Tandym`, you will see `Program Name` as a payment method in the checkout page.
* Select `Program Name` and move forward.
* Once you click `Place Order`, you will be redirected to `Tandym Origination App` to complete the checkout.
* On successful payment approval, you will be redirected to the order confirmation page.

