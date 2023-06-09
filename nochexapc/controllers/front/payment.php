<?php

class NochexApcPaymentModuleFrontController extends ModuleFrontController
{

    public function initContent()
    {
        parent::initContent();
		
	 include_once(_PS_MODULE_DIR_.'/nochexapc/nochexapc.php');
        $nochexapc = new nochexapc();
        if ($nochexapc->module_key == $this->context->controller->module->module_key) {
		
		  $cart = $this->context->cart;
            $contextLink = $this->context->link;
            $customer = new Customer((int)$cart->id_customer);
                $currency = new Currency((int)$cart->id_currency);
			
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
				if ($test_mode == 1) {
                    $testMode = "true";
                } else {
                    $testMode = "false";
                }
				
					$productDetails = $cart->getProducts();
                    $prodDet = ""; 
                    foreach ($productDetails as $details_product) {
                        
						$filterName = preg_replace('/[^\dA-Za-z0-9 ]/i', '',$details_product['name']);
						
						/*$filterName = filter_var(
                            $filterName,
                            FILTER_SANITIZE_STRING,
                            FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW
                        ); */
						$prodDet .= " " . $filterName . " " . $details_product['quantity']  . " x " . number_format( $details_product["total_wt"], 2, '.', '' ) . " ".$currency->iso_code.". ";
                    }
                    $prodDet .= " ";
					
					$apc_email = Configuration::get('NOCHEX_APC_VAL_EMAIL'); 
					$api_key = Configuration::get('NOCHEX_APC_VAL_APIKEY');  
					
					$optional2 = 'callback';
					
					$todaysDate = date("jS F Y");
					
					if ($currency->iso_code <> "GBP") {
					
					$cancel_url = $contextLink->getPageLink(
                        'order',
                        true,
                        null,
                        array(
                            'step' => '3'
                        )
                    );
					
					Tools::redirect( $cancel_url );
					
					} else {
					
					 $this->context->smarty->assign(array( 
                    'todaysDate' =>  $todaysDate,
                    'currency_code' =>  $currency->iso_code,
                    'merchant_id' => $apc_email,
                    'api_key' => $api_key,
                    'amount' =>  number_format(round($cart->getOrderTotal(true, 3), 2), 2, '.', ''),
                    'order_id' => (int)$cart->id,
                    'description' => $prodDet,
                    'billing_fullname' => $bill_add_fields['firstname'].', '.$bill_add_fields['lastname'],
                    'billing_address' => $bill_add_fields['address1'],
                    'billing_city' => $bill_add_fields['city'],
                    'billing_country' => $billing_address->country->name[1],
                    'billing_postcode' => $bill_add_fields['postcode'],
                    'delivery_fullname' => $del_add_fields['firstname'] . ', '. $del_add_fields['lastname'],
                    'delivery_address' => $del_add_fields['address1'],
                    'delivery_city' => $del_add_fields['city'],
                    'delivery_country' => $delivery_address->country->name[1],
                    'delivery_postcode' => $del_add_fields['postcode'],
                    'customer_phone_number' => $customer_phone,
                    'email_address' => $customer->email,
                    'optional_1' => $cart->secure_key,
                    'optional_2' => $optional2,
                    'successurl' => $contextLink->getPageLink(
                        'order-confirmation',
                        true,
                        null,
                        array(
                            'id_cart' => (int)$cart->id,
                            'key' => $cart->secure_key,
                            'id_module' => $this->module->id)
                    ),
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
	
        $this->setTemplate('module:'.$this->module->name.'/views/templates/front/payment_page.tpl');
		
		}
	}
}
}

?>