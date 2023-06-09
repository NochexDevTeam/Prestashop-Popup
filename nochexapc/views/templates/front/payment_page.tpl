
{extends file='customer/page.tpl'}

{block name='content'} 

<script src="https://code.jquery.com/jquery-3.6.0.js"></script> 
<script src="https://secure.nochex.com/exp/nochex_lib.js"></script> 

            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
				
<h2>Order Summary</h2>

<table class="table table-striped table-bordered table-labeled hidden-sm-down">
<tbody>
<tr>
	<td>Order Date</td>
	<td>{$todaysDate|escape:'htmlall':'UTF-8'}</td>
</tr>
<tr>
	<td>Ordered Products</td>
	<td>{$description|escape:'htmlall':'UTF-8'}</td>
</tr>
<tr>
	<td>Order Total:</td>
	<td>{$amount|escape:'htmlall':'UTF-8'} {$currency_code|escape:'htmlall':'UTF-8'}</td>
</tr>
<tr>
	<td></td>
	<td><input type="button" value="Pay Now" id="ncx-show-checkout" class="btn btn-primary" /></td>
</tr>
</tbody>
</table>
  
<form id="nochexForm" class="ncx-form" name="nochexForm">
						<script id="ncx-config"							
                            NCXFIELD-API_KEY="{$api_key|escape:'htmlall':'UTF-8'}"
                            NCXFIELD-MERCHANT_ID="{$merchant_id|escape:'htmlall':'UTF-8'}"
							NCXFIELD-AMOUNT="{$amount|escape:'htmlall':'UTF-8'}"	
							NCXFIELD-callback_url="{$responderurl|escape:'htmlall':'UTF-8'}"		
                            ncxField-success_url="{$successurl|escape:'htmlall':'UTF-8'}"                         	
							NCXFIELD-TEST_TRANSACTION="{$test_transaction|escape:'htmlall':'UTF-8'}"                           	
							ncxField-autoredirect="true"  
                            NCXFIELD-order_id="{$order_id|escape:'htmlall':'UTF-8'}" 
							NCXFIELD-REQUEST_DELIVERY_DTLS="true"
							NCXFIELD-EMAIL="{$email_address|escape:'htmlall':'UTF-8'}" 
							NCXFIELD-DESCRIPTION="{$description|escape:'htmlall':'UTF-8'}" 
							ncxField-optional_1="{$optional_1|escape:'htmlall':'UTF-8'}" 
							ncxField-optional_2="{$optional_2|escape:'htmlall':'UTF-8'}" 
							ncxField-delivery_fullname="{$delivery_fullname|escape:'htmlall':'UTF-8'}"
							ncxField-delivery_address="{$delivery_address|escape:'htmlall':'UTF-8'}"
                            ncxField-delivery_city="{$delivery_city|escape:'htmlall':'UTF-8'}" 
                            ncxField-delivery_country="{$delivery_country|escape:'htmlall':'UTF-8'}" 
                            ncxField-delivery_postcode="{$delivery_postcode|escape:'htmlall':'UTF-8'}"
							ncxField-fullname="{$billing_fullname|escape:'htmlall':'UTF-8'}"
							ncxField-address="{$billing_address|escape:'htmlall':'UTF-8'}"
							ncxField-city="{$billing_city|escape:'htmlall':'UTF-8'}" 
							ncxField-country="{$billing_country|escape:'htmlall':'UTF-8'}" 
                            ncxField-postcode="{$billing_postcode|escape:'htmlall':'UTF-8'}" 
							ncxField-phone="{$customer_phone_number|escape:'htmlall':'UTF-8'}"
                            ></script>
</form>
 </div>
   </div>
{/block}