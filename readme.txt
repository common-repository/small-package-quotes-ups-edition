=== Small Package Quotes - UPS Edition ===
Contributors: enituretechnology
Tags: eniture,UPS,parcel rates, parcel quotes, shipping estimates
Requires at least: 6.4
Tested up to: 6.6.2
Stable tag: 4.5.11
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Real-time UPS quotes from UPS. Fifteen day free trial.

== Description ==

UPS is headquartered in Sandy Springs, Georgia and is the world’s largest package delivery company. UPS delivers more than 15 million packages per day to more than 7.9 million customers in more than 220 countries and territories around the world. If you don’t have a UPS account number, contact them at 800-742-5877, or register online[https://www.ups.com/one-to-one/login].

**Key Features**

* Includes negotiated shipping rates in the shopping cart and on the checkout page.
* Ability to control which UPS small package services to display
* Support for variable products.
* Option to include residential delivery surcharge
* Option to mark up shipping rates by a set dollar amount or by a percentage.

**Requirements**

* WooCommerce 6.4 or newer.
* A UPS account number.
* Your username and password to ups.com.
* Your UPS web services authentication key.
* An API Key from Eniture Technology.

== Installation ==

**Installation Overview**

Before installing this plugin you should have the following information handy:

* Your UPS account number.
* Your username and password to ups.com.
* Your UPS web services authentication key.

If you need assistance obtaining any of the above information, contact your local UPS office
or call the [UPS](http://ups.com) corporate headquarters at 1-800-333-7400.

A more comprehensive and graphically illustrated set of instructions can be found on the *Documentation* tab at
[eniture.com](https://eniture.com/woocommerce-ups-small-package/).

**1. Install and activate the plugin**
In your WordPress dashboard, go to Plugins => Add New. Search for "Small Package Quotes - UPS Edition", and click Install Now.
After the installation process completes, click the Activate Plugin link to activate the plugin.

**2. Get an API key from Eniture Technology**
Go to [Eniture Technology](https://eniture.com/woocommerce-ups-small-package/) and pick a
subscription package. When you complete the registration process you will receive an email containing your API key and
your login to eniture.com. Save your login information in a safe place. You will need it to access your customer dashboard
where you can manage your licenses and subscriptions. A credit card is not required for the free trial. If you opt for the free
trial you will need to login to your [Eniture Technology](http://eniture.com) dashboard before the trial period expires to purchase
a subscription to the license. Without a paid subscription, the plugin will stop working once the trial period expires.

**3. Establish the connection**
Go to WooCommerce => Settings => UPS. Use the *Connection* link to create a connection to your UPS account.

**5. Select the plugin settings**
Go to WooCommerce => Settings => UPS. Use the *Quote Settings* link to enter the required information and choose
the optional settings.

**6. Enable the plugin**
Go to WooCommerce => Settings => Shipping. Click edit for shipping zone you want to add UPS. Click Add Shipping Method. Select UPS from dropdown. Click "Add Shipping Method" button. 

**Third-Party Services
This plugin relies on the following third-party services to provide some functionalities:

Eniture Technology APIs (https://eniture.com): Used for functionalities like retrieving shipping rates and managing connections.
Terms of Service: link https://eniture.com/eniture-technology-terms-of-use/
Privacy Policy: https://eniture.com/eniture-llc-privacy-policy/

FreightDesk.Online (https://freightdesk.online): Used for functionalities like applying promo code status. Only when a user have an account on freightdesk.online.
Privacy Policy: https://freightdesk.online/privacy-statement

Validate Addresses (https://validate-addresses.com): Used for functionalities like applying and using promo codes, only when user have an acount on validate addresses portal. 
Terms of Service: https://validate-addresses.com/terms-of-use
Privacy Policy: https://validate-addresses.com/privacy-statement


== Frequently Asked Questions ==

= How do I get a UPS account number? =

Contact them at 800-742-5877, or register online.

= Where do I find my UPS.com username and password? =

Contact UPS customer service at 800-742-5877 or the UPS account manager assigned to your account.


= Where do I get my UPS API Access Key? =

Obtaining a UPS API Access Key is done on UPS.com and requires a few steps. Refer to the Documentation tab on this page for detailed instructions.

= Why do I sometimes get a message that a shipping rate estimate couldn’t be provided? =

There are several possibilities:

UPS has restrictions on a shipment’s maximum weight, length and girth which your shipment may have exceeded.

There wasn’t enough information about the weight or dimensions for the products in the shopping cart to retrieve a shipping rate estimate.

The city entered for the shipping address may not be valid for the postal code entered. A valid City+State+Postal Code combination is required to retrieve a rate estimate and Shopify does not perform this level of address validation. Contact us by phone (404-369-0680) or email (support@eniture.com) to inquire about solutions to this problem.

The UPS web service isn’t operational.

Your UPS account has been suspended or cancelled.

Your subscription to the application has expired because payment could not be processed.

There is an issue with the Eniture Technology servers.

There is an issue with the Shopify servers.

= Why were the shipment charges I received on the invoice from UPS different than what was quoted by the plugin? =

Common reasons include one of the shipment parameters (weight, dimensions) is different, or additional services (such as residential 
delivery) were required. Compare the details of the invoice to the shipping settings on the products included in the shipment. 
Consider making changes as needed. Remember that the weight of the packing materials is included in the billable weight for the shipment. 
If you are unable to reconcile the differences call your local UPS office for assistance.

= Why do I sometimes get a message that a shipping rate estimate couldn’t be provided? =

There are several possibilities:

* UPS has restrictions on a shipment’s maximum weight, length and girth which your shipment may have exceeded.
* There wasn’t enough information about the weight or dimensions for the products in the shopping cart to retrieve a shipping rate estimate.
* The UPS web service isn’t operational.
* Your UPS account has been suspended or cancelled.
* Your Eniture Technology API key for this plugin has expired.

== Screenshots ==

1. Quote settings page
2. Warehouses and Drop Ships page
3. Quotes displayed in cart

== Changelog ==

= 4.5.11 =
* Fix: Fixed delivery estimate string 

= 4.5.10 =
* Fix: Fixed conflict with LTL plugins.

= 4.5.9 = 
* Fix: Fixed issues reported by WordPress team

= 4.5.8 = 
* Update: Implements changes to enable free shipping for override rate shipping rules.

= 4.5.7 = 
* Update: Updated connection tab according to WordPress requirements 

= 4.5.6 = 
* Fix: Fixed issues with escape characters in product names.

= 4.5.5 = 
* Update: Fixes rates not showing for Puerto richo 

= 4.5.4 = 
* Update: Compatibility with WordPress version 6.5.2
* Update: Compatibility with PHP version 8.2.0
* Update: Introduced an additional option to packaging method when standard boxes is not in use

= 4.5.3 = 
* Update: Introduced quotes overriding shipping rules.
* Update: Enables merchants to save delivery estimate labels in the plugin settings and display them during checkout.

= 4.5.2 = 
* Update: Resolved Ground Freight Pricing (GFP) API response changed.

= 4.5.1 =
* Update: Resolved a CSS conflict with other Eniture Technology plugins

= 4.5.0 =
* Update: Introduced Shipping Rules feature

= 4.4.2 =
* Update: Revised the number format for length, width, and height to ensure consistency with other Eniture Technology parcel plugins.

= 4.4.1 =
* Update: Suppress parcel(UPS) rates when the weight threshold is met for LTL Freight Quotes.

= 4.4.0 =
* Update: Display “Free Shipping” at checkout when handling fee in the quote settings is -100% .
* Update: Introduced the Shipping Logs feature.
* Fix: Markup fee applied to shipping quotes in the following order; 1) Product-specific Mark Up (Product settings); 2) Location-specific Handling Fee / Mark Up (Warehouse settings) and 3) General Handling Fee / Mark Up (Quote settings).

= 4.3.11 =
* Fix: Fixed the product ID and product title in the metadata required for freightdesk.online

= 4.3.10 =
* Update: Changed required plan from standard to basic for delivery estimate options.

= 4.3.9 =
* Update: Compatibility with WooCommerce HPOS(High-Performance Order Storage)

= 4.3.8 =
* Fix: Compatibility of rounding function with PHP 7.4

= 4.3.7 =
* Fix: Remove rounding of dimensions values.

= 4.3.6 =
* Update: Introduced an option in quote settings for hazardous fee a)hazardous items ship as its own packages b) Apply hazardous fee on packages that have hazardous items.

= 4.3.5 =
* Update: Introduced filters for weight and dimensions. 

= 4.3.4 =
* Update: Apply hazardous fee on packages that have hazardous items instead of the whole shipment.

= 4.3.3 =
* Update: Fixed grammatical mistakes in "Ground transit time restrictions" admin settings.

= 4.3.2 =
* Update: Introduced username and password fields on connection settings field. 

= 4.3.1 =
* Update: Introduced a setting in Quote Setting tab to show negotiated VS List rates on the cart/checkout. 

= 4.3.0 =
* Update: Introduced optimizing space utilization.
* Update: Modified expected delivery message at front-end from “Estimated number of days until delivery” to “Expected delivery by”.
* Fix: Inherent Flat Rate value of parent to variations.

= 4.2.1 =
* Fix: Right selection of the API for quotes.

= 4.2.0 =
* Update: Introduced UPS new API AOuth process.

= 4.1.3 =
* Update: Introduced a settings on product page to Exempt ground Transit Time restrictions.

= 4.1.2 =
* Update: Added compatibility with "Address Type Disclosure" in Residential address detection 

= 4.1.1 =
* Update: Added origin level markup. 

= 4.1.0 =
* Update: Added product level markup. 

= 4.0.9 =
* Fix: Fixed LTL weight threshold set to all the weight units  

= 4.0.8 =
* Update: Added support for the setting “Only show LTL rates if the parcel shipment weight exceeds the weight threshold” found in Eniture Technology’s family of LTL Freight Quotes plugins.

= 4.0.7 =
* Update: Conflict resolved with “Flexible Product Fields” plugin

= 4.0.6 =
* Update: Introduced connectivity from the plugin to FreightDesk.Online using Company ID

= 4.0.5 =
* Update: Compatibility with WordPress version 6.0.
* Update: Included tabs for freightdesk.online and validate-addresses.com

= 4.0.4 =
* Update: Compatibility with PHP version 8.1.
* Update: Compatibility with WordPress version 5.9.


= 4.0.3 =
* Update: Compatibility with preferred origin custom work.
* Fix: Fixes for PHP version 8.0.

= 4.0.2 =
* Update: Introduced debug logs tab.
* Fix: In case of multiply shipment, wil show rates if all shipments will return rates. 

= 4.0.1 =
* Fix: Fixed delivery days options.
* Update: added data analysis.

= 4.0.0 =
* Update: Compatibility with PHP version 8.0
* Update: Compatibility with WordPress version 5.8
* Fix: Corrected product page URL in connection settings tab

= 3.6.1 =
* Update: Updates to the description of UPS contract services supported by the plugin.

= 3.6.0 =
* Update: Added feature "Weight threshold limit".
* Update: Added feature In-store pickup with terminal information.

= 3.5.0 =
* Update: Added images URL for FDO portal.
* Update: CSV columns updated.
* Update: Virtual product details added in order meta data.
* Update: Compatibility with shippable addon.

= 3.4.1 =
* Update: Added compatibility with WP 5.7, compatibility with shippable ad-don, compatibility with account number ad-don fields showing on the checkout page.

= 3.4.0 =
* Update: Compatibility with WordPress 5.6

= 3.3.9 =
* Fix: Fixed In Store and Local delivery as an default selection.

= 3.3.8 =
* Update: Compatibility with WordPress 5.5, Compatibility with shipping solution freightdesk.online and added a link in plugin to get updated plans from eniture.com.

= 3.3.7 =
* Fix: Fixed database query to created warehouse table

= 3.3.6 =
* Fix: Resolved conflict with third party plugin.

= 3.3.5 =
* Fix: Compatibility with Eniture Technology Freight plugins

= 3.3.4 =
* Update: Compatibility with WordPress 5.4

= 3.3.3 =
* Update: Ignore items with given Shipping Class(es).

= 3.3.2 =
* Update: Introduced Features: 1) Multi Packaging. 2) Box Fee. 3) Programming improved for order detail widget.

= 3.3.1 =
* Fix: Fixed UI of quote settings tab.

= 3.3.0 =
* Update: Introduce product nesting property feature. 

= 3.2.3 =
* Fix: Fix compatibility issue with Standard Box Sizes Addon. 

= 3.2.2 =
* Update: Relocation of auto residential address detection settings. 

= 3.2.1 =
* Fix: Fixed compatibility issue with Eniture Technology LTL Freight Quotes plugins.

= 3.2.0 = 
* Update: This update introduces: 1 Compatibility for the WooCommerce Measurement Price Calculator plugin; 2. The ability to mark up individual services; 3. Improved support for UPS Contract Services; 4) Customizable error message in the event the plugin is unable to retrieve rates from UPS; 5) An option to disable the sorting of shipping rates by price in ascending order.  The update also fixes a compatibility issue with the Residential Address Detection plugin.

= 3.1.0 = 
* Update: Introduced settings to control quotes sorting on frontend

= 3.0.4 = 
* Update: Introduced settings for frontend message when shipping cannot be calculated 

= 3.0.3 =
* Update: Introduced new feature, shipping rates estimates on manual orders.

= 3.0.2 =
* Update: Compatibility with WordPress 5.1

= 3.0.1 =
* Fix: Identify one warehouse and multiple drop ship locations in basic plan.

= 3.0.0 =
* Update: Introduced new features and Basic, Standard and Advanced plans.

= 2.1.2 =
* Update: Compatibility with WordPress 5.0

= 2.1.1 =
* Update: Compatibility with WooCommerce 3.4.2 and PHP 7.1.

= 2.1.0 =
* Update: Added new subscription options for Residential Address Detection plug-in and Standard Box Sizes plug-in

= 2.0.1 =
* Fix: Corrected headings CSS 

= 2.0.0 =
* Update: Introduction of Standard Box Sizes and Residential Address Detection features which are enabled though the installation of plugin add ons.

= 1.1.3 =
* Fix: Fixed issue with CSS on textarea 

= 1.1.2 =
* Fix: CSV export conflict with LTL plugin 

= 1.1.1 =
* Fix: Fixed issue with new reserved word in PHP 7.1

= 1.1.0 =
* Update: Compatibility with WordPress 4.9

= 1.0 =
* Initial release.

== Upgrade Notice ==
