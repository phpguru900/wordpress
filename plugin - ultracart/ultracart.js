var merchantId = ultra_cart_vars.merchant_id; //'PROB';
var usingProxy = true;
var proxyPath = ultra_cart_vars.plugin_path+ultra_cart_vars.proxy_path; //'rest_proxy.php';
var restUrl = usingProxy ? (proxyPath + "?_url=/rest") : "/rest";
var myUltraCart = new ultracart.Cart(merchantId, restUrl);
var first_req=true;
var thiss, this_img, t_basket;

////////---Amazon functions
var amazonMerchantId = uc_payments.amazon_merchant_id; //'A6LTHOSJNP8G2';
var amazonIsReady = false; // set when the amazon widgets have loaded (if they're enabled).
var loggedIntoAmazon = false;

////////
var t_payment_method='Credit Card'; var i_active_card_id='i_paymethod_credit';

jQuery.ajaxSetup({ cache: false });

var alertFallback = true;
if (typeof console === "undefined" || typeof console.log === "undefined") {
    console = {};
    if (alertFallback) {
        console.log = function(msg) {
            alert(msg);
        };
    } else {
        console.log = function() {};
    }
}
////////
accounting.settings = {
  currency: {
    symbol: "$",   // default currency symbol is '$'
    format: "%s%v", // controls output: %s = symbol, %v = value/number (can be object: see below)
    decimal: ".",  // decimal point separator
    thousand: ",",  // thousands separator
    precision: 2   // decimal places
  },
  number: {
    precision: 0,  // default precision on numbers is 0
    thousand: ",",
    decimal: "."
  }
};
/////
console.gx = function(items){ $('#gx_console').html(JSON.stringify(items));};
/////
var finalizing = false;
var cart_coupon=ultra_cart_theme.cart_coupon;
var cart_coupons=[];

function updateCartOnCheckout(){

	myUltraCart.loader.showPleaseWait();
	myUltraCart.getCart({
		success: function(data){
			
			myUltraCart.getAllowedCountries({
				success: function(res){
					checkout_info = JSON.parse(myUltraCart.readCookie('UltraCartCheckoutInfo'));

                    if(myUltraCart.cart.items.length < 1) {
                        checkout_info['creditCardNumber']='';
                        checkout_info['creditCardExpirationMonth']='';
                        checkout_info['creditCardExpirationYear']='';
                        checkout_info['creditCardVerificationNumber']='';
                        myUltraCart.createCookie('UltraCartCheckoutInfo',JSON.stringify(checkout_info),14);
                        i_fill_inputs();
                    }

					for(var i=0;i<res.length;i++){
						$('#country').append('<option value="'+res[i]+'">'+res[i]+'</option>');
						$('#bcountry').append('<option value="'+res[i]+'">'+res[i]+'</option>');
					}
					if(checkout_info && checkout_info.country){
						myUltraCart.cart.shipToCountry = checkout_info.country;
						$('#country').val(checkout_info.country);
					}else{
                        myUltraCart.cart.shipToCountry = 'United States';
						$('#country').val('United States');
					}
					if(checkout_info && checkout_info.bcountry){
						//myUltraCart.cart.shipToCountry = checkout_info.country;
                        //myUltraCart.cart.billToCountry = checkout_info.country; //GX
						$('#bcountry').val(checkout_info.bcountry);
					}else{
						$('#bcountry').val('United States');
					}

                    //$('#country, #bcountry').change();
                    $('#zip-code').change();  $('#bzip-code').change();
					updateShippingOptionsOnCheckout();
				}
			});
			for(var i=0;i<myUltraCart.cart.creditCardTypes.length;i++){
				$('#creditCardTypes').append('<option value="'+myUltraCart.cart.creditCardTypes[i]+'">'+myUltraCart.cart.creditCardTypes[i]+'</option>');
			}
			
			checkout_info = JSON.parse(myUltraCart.readCookie('UltraCartCheckoutInfo'));
			if(checkout_info && checkout_info.creditCardTypes){
				myUltraCart.cart.shipToCountry = checkout_info.creditCardTypes;
				$('#creditCardTypes').val(checkout_info.creditCardTypes);
			}
			
			if(data.items.length > 0){
				$('table#shopping-cart, .i_summary_content').empty();
				$('table#shopping-cart').append('<tr>\
											<th width="80"></th>\
											<th width="150">Product Name</th>\
											<th>Unit Price</th>\
											<th>Qty</th>\
											<th>Subtotal</th>\
											<th>Remove</th>\
										</tr>');
                /*'<tr><td colspan="5" style="
                text-align: right;
                "><img src="https://www.probacto.com.com/images/SSL_Tag.png" alt="Safe &amp; Secure Encrypted Transactions. 128 Bit Military Grade SSL Certified" style="
                    "></td></tr>';*/
				for(var i=0;i<data.items.length;i++){
					item = data.items[i];
					image = '';
					if(item.defaultThumbnailUrl)
						image = '<img width="80 " src="'+item.defaultImageUrl.replace('http','https')+'" />';
					if($('table#shopping-cart tr[data-itemId="'+item.itemId+'"]').length == 0){
						$('table#shopping-cart').append('<tr data-itemId="'+item.itemId+'">\
															<td>'+image+'</td>\
															<td>'+item.description+'</td>\
															<td class="cell-text-center">'+accounting.formatMoney(item.unitCost)+'</td>\
															<td class="cell-text-center item-qty"><input type="text" name="'+item.itemId+'[qty]" value="1"/> <button class="btn btn-primary update-qty"  disabled><i class="glyphicon glyphicon-refresh"></i></button></td>\
															<td class="cell-text-center item-subtotal">'+accounting.formatMoney(item.unitCost)+' </td>\
															<td class="cell-text-center item-remove"> <button class="btn btn-default btn-danger btn-xs remove-item"><span class="glyphicon glyphicon-remove"></span></button></td>\
														</tr>');
					}else{
						qty = parseInt($('table#shopping-cart tr[data-itemId="'+item.itemId+'"] .item-qty input').val());
						$('table#shopping-cart tr[data-itemId="'+item.itemId+'"] .item-qty input').val(qty+1);
						qty = parseInt($('table#shopping-cart tr[data-itemId="'+item.itemId+'"] .item-qty input').val());
						$('table#shopping-cart tr[data-itemId="'+item.itemId+'"] .item-subtotal').html( accounting.formatMoney(qty*item.unitCost) ); //+' <button class="btn btn-default btn-danger btn-xs remove-item"><span class="glyphicon glyphicon-remove"></span></button>');
					}
				}

				$('.i_summary_content').append('<tr>\
													<td class="checkout-totals" colspan="5">Sub Total:</td>\
													<td class="talign-right" id="sub-total">'+accounting.formatMoney(data.total)+'</td>\
												</tr>');
				$('.i_summary_content').append('<tr id="shipping-row-total">\
													<td id="shipping-label" class="checkout-totals" colspan="5">Shipping & Handling</td>\
													<td class="talign-right" id="shipping-cost"></td>\
												</tr>');
                if(cart_coupon.status==1){
                    $('.i_summary_content').append('<tr id="coupon_tr">\
                                                        <td class="checkout-subtotal-discount" colspan="5">Sub Total Discount:</td>\
                                                        <td class="talign-right" id="subtotal-discount">'+accounting.formatMoney(data.subtotalDiscount)+'</td>\
                                                    </tr>');
                }
				$('.i_summary_content').append('<tr>\
													<td class="checkout-totals" colspan="5">Grand Total:</td>\
													<td class="talign-right" id="grand-total">'+accounting.formatMoney(data.total)+'</td>\
												</tr>');
				
				
				$('table#shopping-cart .item-qty input').keyup(function(){
					itemId = $(this).parents('tr').attr('data-itemId');
					new_value = $(this).val();
					old_value = 0;
					for(var i=0;i<myUltraCart.cart.items.length;i++){
						if(myUltraCart.cart.items[i].itemId == itemId)
							old_value++;
					}
					if(parseInt(new_value)){
						if(new_value != old_value)
							$(this).parent().find('button').removeAttr('disabled');
						else
							$(this).parent().find('button').attr('disabled','disabled');
					}
				});

				$('table#shopping-cart .item-qty .update-qty').click(function(){

					button = $(this);
					$('#shopping-cart tr[data-itemId]').each(function(idx){
						itemId = $(this).attr('data-itemId');
						qty = $(this).find('.item-qty input').val();
						updateQty(itemId,qty);
					});
					myUltraCart.updateCart({
						success: function(data){
                            //console.gx(data);
							updateCartOnCheckout();
						}
					});
				});
				$('table#shopping-cart .remove-item').click(function(){
                    $(this).attr('disabled','disabled');
					itemId = $(this).parents('tr').attr('data-itemId');
					item_tr = $(this).parents('tr');
					updateQty(itemId,0);
					myUltraCart.updateCart({
						success: function(){
							item_tr.remove();
                            updateCartOnCheckout();
						}
					});
				});
                // ------------------------------------------------------------
                // PayPal
                // unbind here and rebind to avoid stacking event handlers
                var payPalLink = $('.the_paypal_method');
                // if we're using amazon, don't show PayPal.
                if (myUltraCart.cart != null) {

                    if (myUltraCart.cart.hasPayPal && myUltraCart.cart.amazonOrderReferenceId == null && !loggedIntoAmazon) {
                        payPalLink.show();
                        showConjunction = true;
                    } else {
                        payPalLink.hide();
                    }

                    var paypalImage = $('.the_paypal_method > img');
                    if (myUltraCart.cart.payPalButtonUrl && (paypalImage.attr('src') != myUltraCart.cart.payPalButtonUrl)) {
                        paypalImage.attr('src', myUltraCart.cart.payPalButtonUrl);
                        paypalImage.attr('alt', myUltraCart.cart.payPalButtonAltText);
                    }
                }

                // ------------------------------------------------------------
                ////Amazon
                if (myUltraCart.cart != null) {

                    // biz rules can override the cart settings to hide paypal checkout,
                    // but it cannot show if the cart says no.  end of story.
                    if (myUltraCart.cart.amazonOrderReferenceId == null && !loggedIntoAmazon) {
                        //if(myUltraCart.cart.hasPayPal) $('.the_paypal_method').show();
                        $('.for_standart_payment').show();
                        showConjunction = true;
                    } else {
                        //if(!myUltraCart.cart.hasPayPal) $('.the_paypal_method').hide();
                        $('.for_standart_payment').hide();
                    }

                }
                if (myUltraCart.cart != null) {
                    if (myUltraCart.cart.hasAmazon && amazonIsReady) {
                        //console.log('Amazon ready');
                        if (loggedIntoAmazon) {
                            jQuery('#AmazonNote').html("<button class='btn btn-default' onclick='stopUsingPayWithAmazon()'>Stop Using Pay with Amazon</button>");
                        } else {
                            showAmazonButton(myUltraCart.cart.amazonButtonUrl);
                            jQuery("#AmazonPayButton").show();
                            if (myUltraCart.cart.amazonOrderReferenceId) {
                                //jQuery('#AmazonNote').html("<em>Please login to Amazon again to continue.</em>");
                                jQuery('#AmazonNote').html("<em>Please login to Amazon again to continue.</em><br><button class='btn btn-default' onclick='stopUsingPayWithAmazon()'>Stop Using Pay with Amazon</button>");
                            }
                        }
                    } else {
                        jQuery("#AmazonPayButton,#AmazonNote").hide();
                    }
                }
                ////-Amazon
			} else {
			//location.href=ultra_cart_pages.products_page;
			}
			myUltraCart.loader.hidePleaseWait();
		}
	});
}
/////////////////
$('#country, #city, #state, #zip-code').change(function(){
    myUltraCart.cart.shipToAddress1 = $('#address1').val();
    myUltraCart.cart.shipToAddress2 = $('#address2').val();
    myUltraCart.cart.shipToCountry = $('#country').val();
    myUltraCart.cart.shipToCity = $('#city').val();
    myUltraCart.cart.shipToPostalCode = $('#zip-code').val();
    myUltraCart.cart.shipToState = $('#state').val();
    myUltraCart.cart.shipToCompany = $('#company').val();

    updateShippingOptionsOnCheckout();

    if(($(this).attr('id')=='zip-code' || $(this).attr('id')=='country') && myUltraCart.cart.shipToCountry == 'United States'){
        if($(this).attr('id')=='zip-code')
            myUltraCart.cart.shipToPostalCode = $(this).val();
        myUltraCart.loader.showPleaseWait();
        myUltraCart.getCityStateByZip({
            postalCode: $('#zip-code').val(),
            success: function(response){
                if(response.validZip){
                    $('#city').val(response.city);
                    $('#state').val(response.state);
                }
                myUltraCart.loader.hidePleaseWait();
            }
        });
    }
});

$('#bcountry, #bcity, #bstate, #bzip-code').change(function(){
    myUltraCart.cart.billToAddress1 = $('#baddress1').val();
    myUltraCart.cart.billToAddress2 = $('#baddress2').val();
    myUltraCart.cart.billToCountry = $('#bcountry').val();
    myUltraCart.cart.billToCity = $('#bcity').val();
    myUltraCart.cart.billToPostalCode = $('#bzip-code').val();
    myUltraCart.cart.billToState = $('#bstate').val();
    myUltraCart.cart.billToCompany = $('#bcompany').val();
    updateShippingOptionsOnCheckout();

    if(($(this).attr('id')=='bzip-code' || $(this).attr('id')=='bcountry') && myUltraCart.cart.billToCountry == 'United States'){
        if($(this).attr('id')=='bzip-code')
            myUltraCart.cart.billToPostalCode = $(this).val();
        myUltraCart.loader.showPleaseWait();
        myUltraCart.getCityStateByZip({
            postalCode: $('#bzip-code').val(),
            success: function(response){
                if(response.validZip){
                    $('#bcity').val(response.city);
                    $('#bstate').val(response.state);
                }
                myUltraCart.loader.hidePleaseWait();
            }
        });
    }
});
////////////////
function updateShippingOptionsOnCheckout(){ //return false;
    update_cart_coupon();
	myUltraCart.updateCart({
		success: function(data){
			myUltraCart.estimateShipping({
				success: function(res){ //console.log(res);
					$('#shippingMethod #options').empty();
					for(var i=0;i<res.length;i++){
						checked = '';
						if(i==0){
							checked = 'checked';
							
							cost = res[i].cost;
							label = res[i].name;
							$('.i_summary_content #shipping-row-total').show();
                            $('.i_summary_content #sub-total').html(accounting.formatMoney(data.subtotal));
							$('.i_summary_content #shipping-cost').html(accounting.formatMoney(cost));
							$('.i_summary_content #grand-total').html(accounting.formatMoney(myUltraCart.cart.total + cost));
                            $('.i_summary_content #subtotal-discount').html(accounting.formatMoney(accounting.formatMoney(data.subtotalDiscount)));
						}
						$('#shippingMethod #options').append('<div class="radio">\
														  <label>\
															<input type="radio" name="shippingMethod" data-cost="'+res[i].cost+'" value="'+res[i].name+'" '+checked+'>\
															'+res[i].displayName+' '+accounting.formatMoney(res[i].cost)+'\
														  </label>\
														</div>');
					}
					$('#shippingMethod #options input').change(function(){
						cost = Number($(this).attr('data-cost'));
						label = $(this).val();
						$('.i_summary_content #shipping-cost').html(accounting.formatMoney(cost));
						$('.i_summary_content #grand-total').html(accounting.formatMoney(myUltraCart.cart.total + cost));
					});
				}
			});
		}
	});
}
function addItem(){
    /*var qty=$('input[name="'+$(this).attr('data-item-id')+'[qty]"]:checked').val();
    if(qty=='' || typeof qty === 'undefined')qty=1;*/
    thiss=$(this);
    var qty=1; var t_item_id=$(this).attr('data-item-id');
    for(var i=0;i<qty;i++){
        myUltraCart.cart.items.push({'itemId': t_item_id,'quantity':1});
    }

	myUltraCart.cart.items = myUltraCart.cart.items.sort(function(a, b){
														 var nameA=a.itemId.toLowerCase(), nameB=b.itemId.toLowerCase()
														 if (nameA < nameB) //sort string ascending
														  return -1
														 if (nameA > nameB)
														  return 1
														 return 0 //default return value (no sorting)
														});
    if(ultra_cart_theme.cart_flying_effect=='1'){
        this_img=$('.product_image img'); t_basket=$('#ultracart');
        if(thiss.hasClass('to_fly')){ flyToElement(this_img, t_basket); }
    }
	myUltraCart.updateCart({
		success: function(cart){
            //console.log(t_item_id.toLowerCase());
            var t_pos=arrayObjectIndexOf(cart.items,t_item_id,"itemId");
			$('#addedToCartModal .product-detail #product-image').attr('src',cart.items[t_pos].defaultThumbnailUrl);
			$('#addedToCartModal .product-detail #product-description').html(cart.items[t_pos].description);
			singplur = 'item';
			if(cart.items.length > 1)
				singplur = 'items';
			$('#addedToCartModal .product-detail #nItemsInCart').html(cart.items.length + ' ' + singplur + ' in cart.');
			$('#addedToCartModal .product-detail #subtotal span').html(accounting.formatMoney(cart.subtotal));
			$('#addedToCartModal').modal('show');
            //$(window).resize();
		}
	});
}

function update_cart_coupon(){
    var i=0; var str='';
    if(first_req){cart_coupons=myUltraCart.cart.coupons; first_req=false;} else {myUltraCart.cart.coupons=cart_coupons;}
    if(cart_coupons.length<1){ $('.i_summary_content #coupon_tr').hide(); }else{ $('.i_summary_content #coupon_tr').show(); }
    for(i=0; i<cart_coupons.length; i++){
        str+='<p>'+cart_coupons[i]['couponCode']+' <span class="remove_coupon" id="'+cart_coupons[i]['couponCode']+'">remove</span></p>';
    }
    //myUltraCart.cart.coupons
    $('.coupons_have').html(str);
}

function updateQty(itemId, qty){
	while(removeItem(itemId))
		continue;
	for(var i=0;i<qty;i++){
		myUltraCart.cart.items.push({'itemId':itemId,'quantity':1});
	}
	myUltraCart.cart.items = myUltraCart.cart.items.sort(function(a, b){
														 var nameA=a.itemId.toLowerCase(), nameB=b.itemId.toLowerCase()
														 if (nameA < nameB) //sort string ascending
														  return -1 
														 if (nameA > nameB)
														  return 1
														 return 0 //default return value (no sorting)
														});
    //console.gx(myUltraCart.cart.items);
}

function removeItem(itemId){
	for(var i=0;i<myUltraCart.cart.items.length;i++){
		if(myUltraCart.cart.items[i].itemId == itemId){
			myUltraCart.cart.items.splice(i, 1);
			return true;
		}
	}
	myUltraCart.cart.items = myUltraCart.cart.items.sort(function(a, b){
														 var nameA=a.itemId.toLowerCase(), nameB=b.itemId.toLowerCase()
														 if (nameA < nameB) //sort string ascending
														  return -1 
														 if (nameA > nameB)
														  return 1
														 return 0 //default return value (no sorting)
														});
	return false;
}
function getQty(itemId){
	qty = 0;
	for(var i=0;i<myUltraCart.cart.items.length;i++){
		item = myUltraCart.cart.items[i];
		if(item.itemId == itemId)
			qty++;
	}
	return qty;
}
function gotocart(){
    if(ultra_cart_theme.cart_seperate_page==1){
        document.location = ultra_cart_pages.the_cart_page;
    } else {
        document.location = ultra_cart_pages.the_checkout_page;
    }
	//document.location = ultra_cart_vars.site_url + '/shopping-cart/';

}
function updateShippingOptions(choices){
	$('#checkoutModal #shippingMethods').empty();
	for(var i=0;i<choices.length;i++){
		checked = '';
		if(i==0)
			checked = 'checked';
		$('#checkoutModal #shippingMethods').append('<div><input type="radio" name="shippingMethod" data-uc-field="shippingMethod" class="checkout-form-field" value="'+choices[i].name+'" '+checked+'/> <b>'+choices[i].displayName+'</b> - '+accounting.formatMoney(choices[i].cost)+'</div>');
	}
}
jQuery(document).ready(function(){
    $('body').prepend('<pre id="gx_console"></pre>');
	$('button.addToCart').click(addItem);
	$('body').append('<div id="ultracart"> \
						<div class="title">Loading...</div>\
						<div class="content"></div> \
						<div class="buttons"><button id="uc-goToCart" class="btn btn-primary">Go to Cart</button></div> \
					</div>\
					<div id="smallModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">\
					  <div class="modal-dialog modal-sm">\
						<div class="modal-content">\
						  <div class="modal-body">\
							\
						  </div>\
						</div>\
					  </div>\
					</div>\
					<div class="modal fade" id="addedToCartModal">\
					  <div class="modal-dialog">\
						<div class="modal-content">\
						  <div class="modal-header">\
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>\
							<h4 class="modal-title">Item Added to Cart</h4>\
						  </div>\
						  <div class="modal-body">\
							<div class="product-detail">\
								<img id="product-image" src="" width="100" height="100"/>\
								<div id="product-description"></div>\
								<div class="clearfix"></div>\
								<div id="cart-summary">\
									<div id="nItemsInCart"></div>\
									<div id="subtotal">Subtotal: <span></span></div>\
									<div style="clear:both"></div>\
								</div>\
							</div>\
						  </div>\
						  <div class="modal-footer">\
							<button type="button" class="btn btn-default" data-dismiss="modal">Continue Shopping</button>\
							<button type="button" class="btn btn-primary" onclick="gotocart();">Go to Cart</button>\
						  </div>\
						</div><!-- /.modal-content -->\
					  </div><!-- /.modal-dialog -->\
					</div><!-- /.modal -->\
					');
					
	myUltraCart.getCart({
		success : function(response){
			$('#checkoutModal #creditCardTypes').empty();
			for(var i=0;i<response.creditCardTypes.length;i++){
				$('#checkoutModal #creditCardTypes').append('<option value="'+response.creditCardTypes[i]+'">'+response.creditCardTypes[i]+'</option>');
			}
			for (var prop in response) {
				$('#checkoutModal [data-uc-field="'+prop+'"]').val(response[prop]);
			}
		}
	});
	var d = new Date();
	var y = d.getFullYear();
	
	for(var i=parseInt(y);i<y+10;i++){
		$('#creditCardExpYear').append('<option value="'+i+'">'+i+'</option>');
	}
	for(var i=1;i<=12;i++){
		$('#creditCardExpMonth').append('<option value="'+i+'">'+i+'</option>');
	}
	$('#checkoutModal .checkout-form-field').change(function(){
		myUltraCart.cart[$(this).attr('data-uc-field')] = $(this).val();
		myUltraCart.updateCart();
	});
	
	myUltraCart.getAllowedCountries({
		success: function(response){
			$('#checkoutModal #allowedCountries').empty();
			$('#checkoutModal #allowedBCountries').empty();
			for(var i=0;i<response.length;i++){
				selected = '';
				if(response[i] == 'United States')
					selected = 'selected';
				$('#checkoutModal #allowedCountries').append('<option value="'+response[i]+'"'+selected+'>'+response[i]+'</option>');
				$('#checkoutModal #allowedBCountries').append('<option value="'+response[i]+'"'+selected+'>'+response[i]+'</option>');
			}
		}
	});
	$('#uc-goToCart').click(function(){
        gotocart();
	});
    /////////////
    $('#apply_coupon').click(i_apply_coupon);

    $('body').on('click','.remove_coupon',remove_coupon);

});

//////////////
function i_apply_coupon(){
    if(cart_coupon.status==1){
        if($('#cart_coupon').val()!=''){
            var if_has = $.grep(cart_coupons, function(e){ return e.couponCode==$('#cart_coupon').val(); });
            if(if_has.length == 0){
                cart_coupons.push({couponCode: $('#cart_coupon').val()});
                myUltraCart.cart.coupons=cart_coupons;
                var first_req=true; updateShippingOptionsOnCheckout();
            } else {
                alert('The coupon code already used !');
            }
        }
    }
}
function remove_coupon(){
    var removed_coupon=$(this).attr('id');
    var c_index=arrayObjectIndexOf(cart_coupons, removed_coupon, "couponCode");
    if(c_index>=0){
        cart_coupons.splice(c_index, 1); $(this).parent().remove();
        myUltraCart.cart.coupons=cart_coupons;
        var first_req=true; updateShippingOptionsOnCheckout();
    }
}
function arrayObjectIndexOf(myArray, searchTerm, property) {
    for(var i = 0, len = myArray.length; i < len; i++) {
        if (myArray[i][property] == searchTerm) return i;
    }
    return -1;
}
function flyToElement(flyer, flyingTo, callBack /*callback is optional*/) {
    var $func = $(this); var divider = 6;
    var flyerClone = $(flyer).clone();
    $(flyerClone).css({
        position: 'absolute',
        top: $(flyer).offset().top + "px",
        left: $(flyer).offset().left + "px",
        opacity: 0.9,
        width: 350,
        'z-index': 999999
    });
    $('body').append($(flyerClone)); //console.log( $(flyingTo).offset() );
    var gotoX = $(flyingTo).offset().left + ($(flyingTo).width() / 2) - ($(flyer).width()/divider)/2;
    var gotoY = $(flyingTo).offset().top + ($(flyingTo).height() / 2) - ($(flyer).height()/divider)/2;
    $(flyerClone).animate({
            opacity: 0.4,
            left: gotoX,
            top: gotoY,
            width: $(flyer).width()/divider,
            height: $(flyer).height()/divider
        }, 700,
        function () {
            $(flyingTo).fadeOut('fast', function () {
                $(flyingTo).fadeIn('fast', function () {
                    $(flyerClone).fadeOut('fast', function () {
                        $(flyerClone).remove();
                        if( callBack != null ) {
                            callBack.apply($func);
                        }
                    });
                });
            });
        }
    ); //$('html, body').animate({scrollTop:0}, 'slow');
}


////// Amazon functions
function onAmazonLoginReady() {
    amazonIsReady = true;
}

function enableFinalizeButton() {
    $('#finalizeButton').attr('disabled', false).show();
    t_payment_method="Amazon";
    myUltraCart.cart.paymentMethod = t_payment_method;
}

function disableFinalizeButton() {
    $('#finalizeButton').attr('disabled', true).hide();
    t_payment_method="Credit Card";
}

function showAmazonButton(buttonUrl) {
    $('#AmazonPayButton').html(
        '<' + 'img src="' + buttonUrl + '?sellerId=' + amazonMerchantId + '&size=large&color=orange" style="cursor: pointer;"/>'
    );

    //noinspection JSUnusedGlobalSymbols
    new OffAmazonPayments.Widgets.Button({
        sellerId: amazonMerchantId,
        useAmazonAddressBook: true,
        onSignIn: function (orderReference) {
            //console.log(orderReference);
            //console.log(orderReference.getAmazonOrderReferenceId());
            loggedIntoAmazon = true;
            $('#AmazonPayButton').hide();
            $('#AmazonNote').html(''); // do this here so that the refresh doesn't slam the note to the left before hiding it.
            disableFinalizeButton();
            myUltraCart.cart.amazonOrderReferenceId = orderReference.getAmazonOrderReferenceId();
            myUltraCart.cart.paymentMethod = "Amazon";

            showAmazonAddress();
            updateCart(); // this will trigger a refresh which will show the address and wallet.
        },
        onError: function (error) {
            renderErrors([error]);
        }
    }).bind("AmazonPayButton");
}

function updateCart() {
    //noinspection JSUnusedLocalSymbols
    myUltraCart.updateCart({
        success: function (updatedCart) {
            updateCartOnCheckout();
            estimateShipping();
        },
        failure: function (jqXHR, textStatus, errorThrown) {
            var errorMsg = jqXHR.getResponseHeader('UC-REST-ERROR');
            if (errorMsg) {
                renderErrors([errorMsg]);
            }
        }
    });
}

function showAmazonAddress() {
    //noinspection JSUnusedGlobalSymbols
    new OffAmazonPayments.Widgets.AddressBook({
        sellerId: amazonMerchantId,
        amazonOrderReferenceId: myUltraCart.cart.amazonOrderReferenceId,
        onAddressSelect: function (orderReference) {
            estimateShipping();
            showAmazonWallet();
        },
        design: {
            size: {width: '400px', height: '260px'}
        },
        onError: function (error) {
            renderError([error]);
        }
    }).bind("AddressBookWidgetDiv");
}

function showAmazonWallet() {
    //noinspection JSUnusedGlobalSymbols
    new OffAmazonPayments.Widgets.Wallet({
        sellerId: amazonMerchantId,
        amazonOrderReferenceId: myUltraCart.cart.amazonOrderReferenceId,
        design: {
            size: {width: '400px', height: '260px'}
        },
        onPaymentSelect: function (orderReference) {
            enableFinalizeButton();
        },
        onError: function (error) {
            console.log(error);
        }
    }).bind("AmazonWalletWidgetDiv");
}

function stopUsingPayWithAmazon() {
    t_payment_method="Credit Card";
    myUltraCart.cart.paymentMethod = t_payment_method;
    myUltraCart.cart.amazonOrderReferenceId = null;
    loggedIntoAmazon = false;
    jQuery('#AddressBookWidgetDiv,#AmazonWalletWidgetDiv,#AmazonNote').html('');
    disableFinalizeButton();
    updateCart();
}

function estimateShipping() {
    updateShippingOptionsOnCheckout();
}

//////
function i_payment_changer(){
    t_payment_method=$(this).val(); i_active_card_id=$(this).attr('id');
    $('.i_paymethod_cont').hide(); $('#'+i_active_card_id+'_div').show();
    //if(t_payment_method=="Amazon"){ myUltraCart.cart.hasAmazon=true; } else { myUltraCart.cart.hasAmazon=false; }
}
function i_goto_pay(){
    t_payment_method=$(this).data('payment-type');
    myUltraCart.cart.paymentMethod = t_payment_method;
    $('#placeOrder').click();
}
////
function i_fill_inputs(){
    if(myUltraCart.readCookie('UltraCartCheckoutInfo')){
        checkout_info = JSON.parse(myUltraCart.readCookie('UltraCartCheckoutInfo'));

        for(key in checkout_info){
            if(key == 'creditCardTypes'){
                // alert("IT->"+checkout_info[key]);
            }
            if(key == 'bill-different-ship' && checkout_info[key])
                $('#bill-check').click();
            if( /*key!='creditCardNumber' && key!='creditCardVerificationNumber' &&*/ key!='cart_coupon' ) {
                $('#'+key).val(checkout_info[key]);
            }
        }
    }
}

//Autocomplete by cookie
var auto_fields = JSON.parse(myUltraCart.readCookie('UltraCartAutoInfos'));
var auto_key=$('#firstname').val();
if(auto_fields==null)auto_fields={};
var auto_tags = []; var auto_label = '';  var auto_field_data={};
var auto_field_keys=[
    'firstname',
    'lastname',
    'company',
    'phone',
    'email',
    'address1',
    'address2',
    'country',
    'zip-code',
    'city',
    'state'
];

for(var auto_field in auto_fields) {
    auto_label = '';

    for (i = 0; i < auto_field_keys.length; ++i) {
        if(auto_field_keys[i] in auto_fields[auto_field])auto_label+=auto_fields[auto_field][auto_field_keys[i]]+' ';
    }

    auto_field_data = {
        value: auto_field,
        label: auto_label,
        desc: "the write less, do more, JavaScript library"
    };
    auto_tags.push(auto_field_data);
}

//console.log(auto_fields);
function i_change_autocomplete(){
    auto_key=$('#firstname').val();
    if(auto_key=='' || auto_key.length <2)return false;
    if(!(auto_key in auto_fields) ){auto_fields[auto_key]={}; }
    auto_fields[auto_key][$(this).attr('id')] = $(this).val();
    myUltraCart.createCookie('UltraCartAutoInfos',JSON.stringify(auto_fields),14);
};

jQuery(document).ready(function(){
    //$('input, select').change(i_change_autocomplete);

    $( "#firstname" ).autocomplete({
        source: auto_tags,
        select: function( event, ui ) {
            changed_for_autocomplete( ui.item.value );
        }
    });
});
function changed_for_autocomplete(auto_object){
    auto_object = auto_fields[auto_object];
    for(key in auto_object){
        $('#'+key).val(auto_object[key]);
    }
}
//$('#firstname')