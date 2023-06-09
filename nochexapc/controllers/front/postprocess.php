<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Nochex
*  @copyright 2007-2019 Nochex
*  @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  Plugin Name: Nochex Payment Gateway for Prestashop 1.7
*  Description: Accept Nochex Payments, orders are updated using APC.
*  Version: 3.0.4
*  License: GPL2
*
*/

class NochexApcPostProcessModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        include_once(_PS_MODULE_DIR_.'/nochexapc/nochexapc.php');
        $nochexapc = new nochexapc();
        if ($nochexapc->module_key == $this->context->controller->module->module_key) {
            $cart = $this->context->cart;
            $contextLink = $this->context->link;
            $customer = new Customer((int)$cart->id_customer);
            if ($cart->secure_key == $customer->secure_key) {
                $currency = new Currency((int)$cart->id_currency);
                $c_rate = (is_array($currency) ? $currency['conversion_rate'] : $currency->conversion_rate);
				
					if( $currency->iso_code == "USD"){
						$apc_email = Configuration::get('NOCHEX_APC_VAL_EMAILUSD');  
					} else if ($currency->iso_code == "EUR"){
						$apc_email = Configuration::get('NOCHEX_APC_VAL_EMAILEUR'); 
					} else {
						$apc_email = Configuration::get('NOCHEX_APC_VAL_EMAIL'); 
					} 
									
                $billing_address = new Address($cart->id_address_invoice);
                $delivery_address = new Address($cart->id_address_delivery);
                $billing_address->country = new Country($billing_address->id_country);
                $delivery_address->country = new Country($delivery_address->id_country);
                $billing_address->state = new State($billing_address->id_state);
                $delivery_address->state = new State($delivery_address->id_state);
                $bill_add_fields = $billing_address->getFields();
                $del_add_fields = $delivery_address->getFields();
                if ($bill_add_fields['phone_mobile'] == "") {
                    $customer_phone = $bill_add_fields['phone'];
                } else {
                    $customer_phone = $bill_add_fields['phone_mobile'];
                }
                $test_mode = Configuration::get('NOCHEX_APC_VAL_TESTMODE');
                $hide_details = Configuration::get('NOCHEX_APC_VAL_HIDEDETAILS');
                $nochex_xmlcollection = Configuration::get('NOCHEX_APC_VAL_XMLCOLLECTION');
                $nochex_postage = Configuration::get('NOCHEX_APC_VAL_POSTAGE');
                if ($test_mode == 1) {
                    $testMode = "100";
                } else {
                    $testMode = "0";
                }
                if ($hide_details == 1) {
                    $hide_billing_details = 1;
                } else {
                    $hide_billing_details = 0;
                }
                if ($nochex_postage == 1) {
                    $disPostage = $cart->getOrderTotal(true, Cart::ONLY_SHIPPING) / $c_rate;
                    $postAmo = $cart->getOrderTotal(true, 3) - $cart->getOrderTotal(true, Cart::ONLY_SHIPPING)/ $c_rate;
                    $disAmount =  number_format(round($postAmo, 2), 2, '.', '') ;
                } else {
                    $disPostage = "";
					if ($cart->getOrderTotal(true, Cart::ONLY_SHIPPING) == 0){
                    $postAmo = $cart->getOrderTotal(true, 3) - $cart->getOrderTotal(true, Cart::ONLY_SHIPPING)/ $c_rate;
					} else {
					$postAmo = $cart->getOrderTotal(true, 3) / $c_rate;
					}
                    $disAmount =  number_format(round($postAmo, 2), 2, '.', '');
                }
                $optional2 = 'callback';
                
				$item_collection = "";
				$productDetails = $cart->getProducts();
				$prodDet = "";
				foreach ($productDetails as $details_product) {
					 $filterName = filter_var(
						$details_product['name'],
						FILTER_SANITIZE_STRING,
						FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW
					);
					
					$filterName = preg_replace('/[^\da-z ]/i', '',$filterName);
					
					$prodDet .= $filterName . ", " . $details_product['quantity']  . " X " . number_format(
						Tools::convertPriceFull($details_product["total_wt"], $currency, $currency1),
						2,
						'.',
						''
					) . " . ";
				}
				$prodDet .= " ";
                
				
                $this->context->smarty->assign(array(
                    'amount' => $disAmount,
                    'order_id' => (int)$cart->id,
                    'description' => $prodDet,
                    'xml_item_collection' => $item_collection,
                    'hide_billing_details' => $hide_billing_details,
                    'billing_fullname' => $bill_add_fields['firstname'].', '.$bill_add_fields['lastname'],
                    'billing_address' => $bill_add_fields['address1'],
                    'billing_city' => $bill_add_fields['city'],
                    'billing_postcode' => $bill_add_fields['postcode'],
                    'delivery_fullname' => $del_add_fields['firstname'] . ', '. $del_add_fields['lastname'],
                    'delivery_address' => $del_add_fields['address1'],
                    'delivery_city' => $del_add_fields['city'],
                    'delivery_postcode' => $del_add_fields['postcode'],
                    'customer_phone_number' => $customer_phone,
                    'email_address' => $customer->email,
                    'optional_1' => $cart->secure_key,
                    'optional_2' => $optional2,
                    'merchant_id' => $apc_email,
                    'successurl' => $contextLink->getPageLink(
                        'order-confirmation',
                        true,
                        null,
                        array(
                            'id_cart' => (int)$cart->id,
                            'key' => $cart->secure_key,
                            'id_module' => $this->module->id)
                    ),
                    'cancelurl' => $contextLink->getPageLink(
                        'order',
                        true,
                        null,
                        array(
                            'step' => '3'
                        )
                    ),
                    'postage' => $disPostage,
                    'responderurl' => $contextLink->getModuleLink(
                        'nochexapc',
                        'validation',
                        array(
                            'id_cart' => (int)$cart->id,
                            'key' => $cart->secure_key,
                            'curr' => $currency->id,
                        ),
                        true
                    ),
                    'test_transaction' => $testMode,
                ));
                $this->setTemplate('module:nochexapc/views/templates/hook/nochex_checkout_payment.tpl');
            } else {
                PrestaShopLogger::addLog('Secure Keys do not match!', 3, null, 'Cart', $cart->id, true);
                $this->redirectError('ERROR_GENERAL_REDIRECT');
            }
        } else {
            PrestaShopLogger::addLog('Issue with module keys not matching!!', 3, null, 'nochexapc', 0, true);
            $this->redirectError('ERROR_GENERAL_REDIRECT');
        }
    }
}
