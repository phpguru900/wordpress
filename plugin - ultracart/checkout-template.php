<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme and one of the
 * two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * For example, it puts together the home page when no home.php file exists.
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */

get_header(); ?>

<script type="text/javascript" xmlns="http://www.w3.org/1999/html">
ajaxurl=ajaxurl.replace('http','https');
	function monthName(i){
		switch(i){
			case 0 : return '01-January';
			case 1 : return '02-February';
			case 2 : return '03-March';
			case 3 : return '04-April';
			case 4 : return '05-May';
			case 5 : return '06-June';
			case 6 : return '07-July';
			case 7 : return '08-August';
			case 8 : return '09-September';
			case 9 : return '10-October';
			case 10 : return '11-November';
			case 11 : return '12-December';
			default: return 'Invalid Month';
		}
	}

	var d = new Date(); //d.getMonth()

	$(document).ready(function(){
		$('#ultracart').hide();
		for(var i=0;i<12;i++) {
//           if(i<d.getMonth()) {
//              opt_dbl=' disabled class="i_disabled" '; 
//           } else {
                opt_dbl=' '; 
//           }
            if(i==d.getMonth())opt_dbl+=' selected="selected" ';
			$('#creditCardExpirationMonth').append('<option value="'+(i+1)+'" '+opt_dbl+'>'+monthName(i)+'</option>');
		}
		for(var i=d.getFullYear();i<d.getFullYear()+20;i++){
			$('#creditCardExpirationYear').append('<option value="'+i+'">'+i+'</option>');
		}
        //GX start
        $('.i_payment_changer').change(i_payment_changer);
        //$('.i_payment_changer[value="'+t_payment_method+'"]').click();
        $('.i_goto_pay').click(i_goto_pay);
        //GX end
		updateCartOnCheckout();

        $('#creditCardExpirationYear').change(function(){
            //
            if( $(this).val()>d.getFullYear() ){
                $('option.i_disabled').removeAttr('disabled');
            } else {
                $('option.i_disabled').attr('disabled','disabled');
            }
        });

		$('#bill-check').click(function(){
			if(!this.checked){
				$('#billingInfo [data-toggle="popover"]').popover('hide');
			}

			if(!myUltraCart.readCookie('UltraCartCheckoutInfo')){
				checkout_info = {}; alert(1);
				checkout_info['bill-different-ship'] = this.checked;
				myUltraCart.createCookie('UltraCartCheckoutInfo',JSON.stringify(checkout_info),14);
			}else{ //console.log(this.checked);
				checkout_info = JSON.parse(myUltraCart.readCookie('UltraCartCheckoutInfo'));
				checkout_info['bill-different-ship'] = this.checked;
                //if(!this.checked)checkout_info['bill-check']='';
				myUltraCart.createCookie('UltraCartCheckoutInfo',JSON.stringify(checkout_info),14);
			}

			$('#billingInfo').toggle(this.checked);
		});

		$('#subscribe-check').click(function(){
			if($(this).is(':checked')){
                myUltraCart.cart.mailingListOptIn = true;
            } else {
                myUltraCart.cart.mailingListOptIn = false;
            }
		});

        $('#country, #bcountry').live('change',function(){
            location.reload();
            var t_prefix; t_prefix=$(this).attr("id");
            if(t_prefix=='country'){ t_prefix=''; } else { t_prefix='b'; }
            $('#'+t_prefix+'zip-code').val(''); $('#'+t_prefix+'city').val(''); $('#'+t_prefix+'state').val('');
	    
        });

        $('#zip-code, #bzip-code').live('change', get_citystate_by_zip);
        function get_citystate_by_zip(){
            var the_zip='', zip_infos=[], t_prefix;
            the_zip=$(this).val(); t_prefix=$(this).attr("id");
            if(t_prefix=='zip-code'){ t_prefix=''; } else { t_prefix='b'; }
            zip_infos = myUltraCart.getCityStateByZip({
                postalCode: the_zip,
                success: function(data){
                    zip_infos=data;
                    if($('#'+t_prefix+'country').val()=='United States'){
                        $('#'+t_prefix+'city').val(zip_infos.city);
                        $('#'+t_prefix+'state').val(zip_infos.state);
                    } else {
                        $('#'+t_prefix+'city').val();
                        $('#'+t_prefix+'state').val();
                    }
                }
            });
        }

		$('#placeOrder').click(function(){
			$('[data-toggle="popover"]').popover('hide');

			myUltraCart.cart.paymentMethod = t_payment_method; //myUltraCart.cart.paymentMethod = 'Credit Card';
			myUltraCart.cart.shipToFirstName = $('#firstname').val();
			myUltraCart.cart.shipToLastName = $('#lastname').val();
			myUltraCart.cart.shipToPhone = $('#phone').val();
			myUltraCart.cart.email = $('#shippingInfo #email').val();
			myUltraCart.cart.shipToAddress1 = $('#address1').val();
			myUltraCart.cart.shipToAddress2 = $('#address2').val();
			myUltraCart.cart.shipToCountry = $('#country').val();
			myUltraCart.cart.shipToCity = $('#city').val();
			myUltraCart.cart.shipToPostalCode = $('#zip-code').val();
			myUltraCart.cart.shipToState = $('#state').val();
			myUltraCart.cart.shipToCompany = $('#company').val();
			if($('#bill-check:checked').length == 0){
				myUltraCart.cart.billToFirstName = $('#firstname').val();
				myUltraCart.cart.billToLastName = $('#lastname').val();
				myUltraCart.cart.billToDayPhone = $('#dphone').val();
				myUltraCart.cart.billToEveningPhone = $('#nphone').val();
				myUltraCart.cart.email = $('#shippingInfo #email').val();
				myUltraCart.cart.billToAddress1 = $('#address1').val();
				myUltraCart.cart.billToAddress2 = $('#address2').val();
				myUltraCart.cart.billToCountry = $('#country').val();
				myUltraCart.cart.billToCity = $('#city').val();
				myUltraCart.cart.billToPostalCode = $('#zip-code').val();
				myUltraCart.cart.billToState = $('#state').val();
				myUltraCart.cart.billToCompany = $('#company').val();
			}else{
				myUltraCart.cart.billToFirstName = $('#bfirstname').val();
				myUltraCart.cart.billToLastName = $('#blastname').val();
				myUltraCart.cart.billToDayPhone = $('#bdphone').val();
				myUltraCart.cart.billToEveningPhone = $('#bnphone').val();
				myUltraCart.cart.billToAddress1 = $('#baddress1').val();
				myUltraCart.cart.billToAddress2 = $('#baddress2').val();
				myUltraCart.cart.billToCountry = $('#bcountry').val();
				myUltraCart.cart.billToCity = $('#bcity').val();
				myUltraCart.cart.billToPostalCode = $('#bzip-code').val();
				myUltraCart.cart.billToState = $('#bstate').val();
				myUltraCart.cart.billToCompany = $('#bcompany').val();
			}
			myUltraCart.cart.shippingMethod = $('[name="shippingMethod"]:checked').val();
			
			 myUltraCart.cart.creditCardType = $('#creditCardTypes').val();
			 myUltraCart.cart.creditCardTypes22 = $('#creditCardTypes').val();
			myUltraCart.cart.creditCardNumber = $('#creditCardNumber').val();
			myUltraCart.cart.creditCardExpirationMonth = $('#creditCardExpirationMonth').val();
			myUltraCart.cart.creditCardExpirationYear = $('#creditCardExpirationYear').val();
			myUltraCart.cart.creditCardVerificationNumber = $('#creditCardVerificationNumber').val();

            //GX s
            //myUltraCart.cart.email
            if(ultra_subscription.uc_sendy == 1 && $('#subscribe-check').is(':checked')){
                myUltraCart.cart.mailingListOptIn = this.checked;
                if(ultra_subscription.uc_uc == '1'){
                    myUltraCart.cart.mailingListOptIn = true;
                } else {
                    myUltraCart.cart.mailingListOptIn = false;
                }
                $.post( ajaxurl , {
                        action: "i_sendy_subscriber",
                        i_name: myUltraCart.cart.shipToFirstName,
                        i_email: myUltraCart.cart.email
                    }, function (data){

                    }
                );
            }

            //GX e
			myUltraCart.checkout({
				success: function(res){
					if(res.errors.length > 0){
						alert_msg = '<button type="button" class="close" data-dismiss="modal" aria-hidden="true" style = "margin-right: -15px;margin-top: -20px;">x</button>';
                        // $('html, body').animate({scrollTop:($('[data-content="'+res.errors[i]+'"]').offset().top-40)}, 'slow'); 
						for(var i=0;i<res.errors.length; i++){
							//if($('[data-content="' + $.trim( res.errors[i] ) + '"]').length < 1)
								alert_msg += res.errors[i] + ' \n <br/>';
							$('[data-content="'+res.errors[i]+'"]').popover('show');
						}
						$('#smallModal .modal-body').html(alert_msg);
						$('#smallModal').modal('show');
					}else{
						window.location = res.redirectToUrl;
                        //console.log(res.redirectToUrl);
					}
				}
			});
		});

		/*$("input#creditCardNumber").change(function(){
			
				
		})*/
		$('input, select').change(function(){
		
			//Detecting card type.
			if($(this).attr('id') == 'creditCardNumber')
			{
			// alert($(this).val());
				switch( $(this).val().charAt(0) )
				{
					case '3':
						$("#creditCardTypes").val("AMEX");
						break;
					case '4':
						$("#creditCardTypes").val("Visa");
						break;
					case '5':
						$("#creditCardTypes").val("MasterCard");
						break;
					case '6':
						$("#creditCardTypes").val("Discover");
						break;
					default:	
						$("#creditCardTypes").val("Visa");
						break;
				}	
			}
			if($(this).attr('id') == 'creditCardNumber'){
				// Extract the card number from the field
				var cardNumber = $(this).val();

				// If they haven't specified 15 digits yet then don't store it.
				if (cardNumber.replace(/[^0-9]/g,"").length < 15) {
				  myUltraCart.cart.creditCardNumber = cardNumber;
				  return;
				}

				// Create a masked version of the card number and update the client field
				var maskedCardNumber = cardNumber;
				checkout_info = JSON.parse(myUltraCart.readCookie('UltraCartCheckoutInfo'));
				if( checkout_info['creditCardTypes'] == "AMEX" ) {
					for (var i = 0; i < 11; i++) {
					  maskedCardNumber = maskedCardNumber.replace(/[0-9]/, 'X');
					}
				} else {
					for (var i = 0; i < 12; i++) {
					  maskedCardNumber = maskedCardNumber.replace(/[0-9]/, 'X');
					}
				}	
				// Store the masked one on the cart object to make sure a full card number doesn't go up.
				myUltraCart.cart.creditCardNumber = maskedCardNumber;
				// Update the form as well
				jQuery("#creditCardNumber").val(maskedCardNumber);

				// Perform the JSONP request to store it (asynchronous by nature)
				jQuery.getJSON('https://token.ultracart.com/cgi-bin/UCCheckoutAPICardStore?callback=?',
				  {
					merchantId: myUltraCart.cart.merchantId,
					shoppingCartId: myUltraCart.cart.cartId,
					cardNumber: cardNumber
				  }
				).done(function(data) {
				  if (data.success) {
					myUltraCart.cart.creditCardNumber = data.maskedCardNumber;
					//$("#creditCardNumber").val(myUltraCart.cart.creditCardNumber);
				  }
				});
			}

			if(!myUltraCart.readCookie('UltraCartCheckoutInfo')){
			
				checkout_info = {};
				checkout_info[$(this).attr('id')] = $(this).val();
				myUltraCart.createCookie('UltraCartCheckoutInfo',JSON.stringify(checkout_info),14);
			}else{
			
				checkout_info = JSON.parse(myUltraCart.readCookie('UltraCartCheckoutInfo'));
				
				if( checkout_info['creditCardTypes'] == "AMEX" ) {
					$("input#creditCardNumber").attr("maxlength", "15");
				} else {
					$("input#creditCardNumber").attr("maxlength", "16");
				}
				checkout_info[$(this).attr('id')] = $(this).val();
				$("input#email").val(checkout_info['email']);
				myUltraCart.createCookie('UltraCartCheckoutInfo',JSON.stringify(checkout_info),14);
			}
		});
        i_fill_inputs();

		//Setting credit card length according to type.
		if( $("#creditCardTypes").val() == "AMEX" ) {
			$("input#creditCardNumber").attr("maxlength", "15");
		} else {
			$("input#creditCardNumber").attr("maxlength", "16");
		}
		$("#creditCardTypes").change(function(){
			if( $("#creditCardTypes").val() == "AMEX" ) {
				$("input#creditCardNumber").attr("maxlength", "15");
			} else {
				$("input#creditCardNumber").attr("maxlength", "16");
			}	
		});
	});

</script>
<?php
$uc_payments=get_option('uc_payments');
$uc_amazon=get_option('uc_amazon');
$cart_visibility=get_option('cart_visibility');
$cart_position=get_option('cart_position');
$cart_coupon=get_option('cart_coupon');

$uc_subscription=get_option('uc_subscription');
$uc_sendy=$uc_subscription['sendy'];
?>
<div id="formWrap">
<?php
if($cart_visibility=='1' && $cart_position=='top'){
    ?>
    <div id="orderReview" class="big_cart">
        <h2>Shopping Cart</h2>
        <table id="shopping-cart" class="i_summary_content">

        </table>
        <?php if($cart_coupon['status']=='1'){ ?>
            <table class="cart_coupon_div">
                <tr>
                    <td> <label for="cart_coupon" class="cart_coupon_title"><?php echo (trim($cart_coupon['title'])!='') ? $cart_coupon['title'] : 'Enter coupon code'; ?> </label> </td>
                    <td>
                        <div class="input_txt_under">
                            <input type="text" name="cart_coupon" id="cart_coupon" value="">
                            <p class="coupons_have">

                            </p>
                        </div>
                    </td>
                    <td>
                        <button id="apply_coupon" class="btn btn-primary"> <?php echo (trim($cart_coupon['btn_txt'])!='') ? $cart_coupon['btn_txt'] : 'Apply coupon'; ?> </button>
                    </td>
                </tr>
            </table>
        <?php } ?>

        <div class="payment_from_div">
            <h2 class="other_payment_h2"> You may also checkout with </h2>
            <?php if($uc_payments['amazon']==1){ ?>
                <section id="section_amazon" class="add-bottom">
                    <div id="AmazonPayButton"></div>
                    <div id="AmazonNote"></div>
                    <div id="AddressBookWidgetDiv"></div>
                    <div id="AmazonWalletWidgetDiv"></div>

                    <div id="amazonResults"></div>
                    <button id="finalizeButton" class="i_goto_pay btn btn-primary" data-payment-type="Amazon" disabled="disabled">Submit Order</button>

                    <div id="redirectUrl"></div>
                </section>
            <?php } ?>
            <?php if($uc_payments['paypal']==1){ ?>
                <span class="i_goto_pay the_paypal_method" data-payment-type="PayPal">
                    <img src="<?php echo ULTRACART_PLUGIN_URL; ?>/images/icons/paypal_xpress.gif">
                </span>
            <?php } ?>
        </div>

        <div style="text-align: right;">
            <img src="<?php echo ULTRACART_PLUGIN_URL; ?>/images/SSL_Tag.png" alt="Safe &amp; Secure Encrypted Transactions. 128 Bit Military Grade SSL Certified">
        </div>
    </div>
<?php
}
?>
    <div class="floatLeft uc_all_order_info">
        <div id="shippingInfo" class="for_standart_payment">
                <h2>Shipping Address</h2>
                <form class="form-horizontal">
                  <div class="form-group col-xs-12">
                    <label class="col-sm-3 control-label" for="firstname">*First Name</label>
                    <div class="col-sm-9">
                    <input type="text" class="form-control input-sm" id="firstname" placeholder="First Name" data-container="body" data-toggle="popover" data-placement="left" data-content="Please specify your ship to first name.">
                    </div>
                  </div>
                  <div class="form-group col-xs-12">
                    <label class="col-sm-3 control-label" for="lastname">*Last Name</label>
                    <div class="col-sm-9">
                    <input type="text" class="form-control input-sm" id="lastname" placeholder="Last Name" data-container="body" data-toggle="popover" data-placement="right" data-content="Please specify your ship to last name.">
                    </div>
                 </div>
              <div class="form-group col-xs-12">
                <label class="col-sm-3 control-label" for="company">Company</label>
                <div class="col-sm-5">
                <input type="text" class="form-control input-sm" id="company" placeholder="Company">
                </div>
              </div>
              <div class="form-group col-xs-12">
                <label class="col-sm-3 control-label" for="phone">*Telephone</label>
                <div class="col-sm-9">
                <input type="text" class="form-control input-sm" id="phone" placeholder="Phone" data-container="body" data-toggle="popover" data-placement="left" data-content="Please specify your ship to phone.">
                </div>
              </div>
            <div class="form-group col-xs-12">
                <label class="col-sm-3 control-label" for="email">*Email</label>
                <div class="col-sm-9">
                <input type="text" class="form-control input-sm" id="email" placeholder="Email" data-container="body" data-toggle="popover" data-placement="left" data-content="Please specify your email.">
                </div>
            </div>
            <div class="form-group col-xs-12">
                <label class="col-sm-3 control-label" for="address">*Address 1</label>
                <div class="col-sm-9">
                <input type="text" class="form-control input-sm" id="address1" placeholder="Address" data-container="body" data-toggle="popover" data-placement="left" data-content="Please specify your ship to address.">
                </div>
            </div>
            <div class="form-group col-xs-12">
                <label class="col-sm-3 control-label" for="address">Address 2</label>
                <div class="col-sm-9">
                <input type="text" class="form-control input-sm" id="address2">
                </div>
            </div>
            <div class="form-group col-xs-12">
                <label class="col-sm-3 control-label" for="country">*Country</label>
                <div class="col-sm-5">
                <select class="form-control input-sm" id="country" data-container="body" data-toggle="popover" data-placement="left" data-content="Please specify your ship to country.">
                </select>
                </div>
            </div>
            <div class="form-group col-xs-12">
                <label class="col-sm-3 control-label" for="zip-code">*Zip Code</label>
                <div class="col-sm-5">
                <input type="text" class="form-control input-sm" id="zip-code" placeholder="Zip Code" data-container="body" data-toggle="popover" data-placement="left" data-content="Please specify your ship to zip code.">
                </div>
            </div>
            <div class="form-group col-xs-12">
                <label class="col-sm-3 control-label" for="country">*City</label>
                <div class="col-sm-5">
                <input type="text" class="form-control input-sm" id="city" placeholder="City" data-container="body" data-toggle="popover" data-placement="right" data-content="Please specify your ship to city.">
                </div>
            </div>
            <div class="form-group col-xs-12">
                <label class="col-sm-3 control-label" for="state">*State</label>
                <div class="col-sm-5">
                <input type="text" class="form-control input-sm" id="state" placeholder="State" data-container="body" data-toggle="popover" data-placement="right" data-content="Please specify your ship to state.">
                </div>
            </div>
            <div class="col-sm-offset-1 col-sm-12">
            <div class="checkbox col-sm-9" style="margin-left: 15px;">
              <label>
                <input type="checkbox" id="bill-check">
                Check here if billing address is different than the shipping address.
              </label>
            </div>
            </div>
            <?php
            if($uc_sendy['subscription'] == '1'){
            ?>
            <div class="col-sm-offset-1 col-sm-10">
                <div class="checkbox col-sm-11" style="margin-left: 15px;">
                  <label>
                    <input type="checkbox" id="subscribe-check" name="subscribe" value="" checked>
                    Subscribe to our newsletter.
                  </label>
                </div>
            </div>
            <?php } ?>
            <div style="clear:both"></div>
            </form>
        </div>
        <div id="billingInfo">
            <h2>Billing Address</h2>
                  <form class="form-horizontal">
                  <div class="form-group col-xs-12">
                    <label class="col-sm-3 control-label" for="bfirstname">*First Name</label>
                    <div class="col-sm-9">
                    <input type="text" class="form-control input-sm" id="bfirstname" placeholder="First Name" data-container="body" data-toggle="popover" data-placement="left" data-content="Please specify your billing first name.">
                    </div>
                  </div>
                  <div class="form-group col-xs-12">
                    <label class="col-sm-3 control-label" for="blastname">*Last Name</label>
                    <div class="col-sm-9">
                    <input type="text" class="form-control input-sm" id="blastname" placeholder="Last Name" data-container="body" data-toggle="popover" data-placement="right" data-content="Please specify your billing last name.">
                    </div>
                 </div>
            <div class="form-group col-xs-12">
                <label class="col-sm-3 control-label" for="bcompany">Company</label>
                <div class="col-sm-5">
                <input type="text" class="form-control input-sm" id="bcompany" placeholder="Company">
                </div>
            </div>
              <div class="form-group col-xs-12">
                <label class="col-sm-3 control-label" for="bphone">Telephone</label>
                <div class="col-sm-9">
                <input type="text" class="form-control input-sm" id="bphone" placeholder="Phone" data-container="body" data-toggle="popover" data-placement="left" data-content="Please specify your billing phone.">
                </div>
              </div>
            <div class="form-group col-xs-12">
                <label class="col-sm-3 control-label" for="baddress">*Address 1</label>
                <div class="col-sm-9">
                <input type="text" class="form-control input-sm" id="baddress1" data-container="body" data-toggle="popover" data-placement="left" data-content="Please specify your billing address.">
                </div>
            </div>
            <div class="form-group col-xs-12">
                <label class="col-sm-3 control-label" for="baddress">Address 2</label>
                <div class="col-sm-9">
                <input type="text" class="form-control input-sm" id="baddress2">
                </div>
            </div>
            <div class="form-group col-xs-12">
                <label class="col-sm-3 control-label" for="bcountry">*Country</label>
                <div class="col-sm-5">
                <select class="form-control input-sm" id="bcountry" data-container="body" data-toggle="popover" data-placement="left" data-content="Please specify your billing country.">
                </select>
                </div>
            </div>
            <div class="form-group col-xs-12">
                <label class="col-sm-3 control-label" for="bzip-code">Zip Code</label>
                <div class="col-sm-5">
                <input type="text" class="form-control input-sm" id="bzip-code" placeholder="Zip Code" data-container="body" data-toggle="popover" data-placement="left" data-content="Please specify your billing zip code.">
                </div>
            </div>
            <div class="form-group col-xs-12">
                <label class="col-sm-3 control-label" for="bcountry">*City</label>
                <div class="col-sm-5">
                <input type="text" class="form-control input-sm" id="bcity" placeholder="City" data-container="body" data-toggle="popover" data-placement="right" data-content="Please specify your billing city.">
                </div>
            </div>
            <div class="form-group col-xs-12">
                <label class="col-sm-3 control-label" for="bstate">State</label>
                <div class="col-sm-5">
                <input type="text" class="form-control input-sm" id="bstate" placeholder="State" data-container="body" data-toggle="popover" data-placement="right" data-content="Please specify your billing state.">
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
        </form>
    <!--</div>
    <div class="floatLeft">-->
        <div id="shippingMethod">
            <h2>Shipping Method</h2>
            <div id="options"></div>
        </div>
        <div id="paymentMethod"  class="for_standart_payment">
            <h2>Payment Method</h2>

            <?php
            /* if(count($uc_payments)){ ?>
            <div class="i_payment_changer_div">
                <span class="i_payment_changer_wrap">
                    <input type="radio" name="payment_method" value="Credit Card" id="i_paymethod_credit" class="i_payment_changer">
                    <label for="i_paymethod_credit">Credit Card</label>
                </span>

                <?php if($uc_payments['amazon']==1){ ?>
                <span class="i_payment_changer_wrap">
                    <input type="radio" name="payment_method" value="Amazon" id="i_paymethod_amazon" class="i_payment_changer">
                    <label for="i_paymethod_amazon">Amazon</label>
                </span>
                <?php } ?>

                <?php if($uc_payments['paypal']==1){ ?>
                <span class="i_payment_changer_wrap">
                    <input type="radio" name="payment_method" value="PayPal" id="i_paymethod_paypal" class="i_payment_changer">
                    <label for="i_paymethod_paypal">Paypal</label>
                </span>
                <?php } ?>
            </div>
            <?php } */ ?>

            <div id="i_paymethod_credit_div" class="i_paymethod_cont">
                <div class="uc_carts_view">
                    <img src="<?php echo ULTRACART_PLUGIN_URL; ?>images/carts/visa.png" >
                    <img src="<?php echo ULTRACART_PLUGIN_URL; ?>images/carts/mastercard.png" >
                    <img src="<?php echo ULTRACART_PLUGIN_URL; ?>images/carts/amex.png" >
                    <img src="<?php echo ULTRACART_PLUGIN_URL; ?>images/carts/discover.png" >
                </div>
                <!--  <input type="hidden"  id="creditCardTypes" name="creditCardType" value="" /> -->
                <div class="form-group col-xs-6" style="display:none;">
                    <label for="saddress">Credit Card Type</label>
                    <select class="form-control input-sm" id="creditCardTypes" name="creditCardType" >

                    </select>
                </div>
                <div class="form-group col-xs-12">
                    <label for="saddress">Credit Card Number</label>
                    <input type="text" style="width:50%;" class="form-control input-sm" id="creditCardNumber" name="creditCardNumber" value="" data-container="body" data-toggle="popover" data-placement="right" data-content="Please specify your credit card number." maxlength = "16" />
                </div>
                <div class="form-group col-xs-4">
                    <label for="saddress">Expiry Month</label>
                    <select class="form-control input-sm" id="creditCardExpirationMonth" name="creditCardExpirationMonth">

                    </select>
                </div>
                <div class="form-group col-xs-4">
                    <label for="saddress">Expiry Year</label>
                    <select class="form-control input-sm" id="creditCardExpirationYear" name="creditCardExpirationYear">

                    </select>
                </div>
                <div class="form-group col-xs-4">
                    <label for="saddress">CVC / CVV</label>
                    <input type="text" class="form-control input-sm" id="creditCardVerificationNumber" name="creditCardVerificationNumber"  data-container="body" data-toggle="popover" data-placement="right" data-content="Please enter the card verification number."/>
                </div>
                <div style="clear:both"></div>
            </div>
            <?php /*
            <?php if($uc_payments['amazon']==1){ ?>
            <div id="i_paymethod_amazon_div" class="i_paymethod_cont">
                <img src="<?php echo ULTRACART_PLUGIN_URL; ?>/images/icons/amazon.png">
            </div>
            <?php } ?>
            <?php if($uc_payments['paypal']==1){ ?>
            <div id="i_paymethod_paypal_div" class="i_paymethod_cont">
                <img src="<?php echo ULTRACART_PLUGIN_URL; ?>/images/icons/paypal.jpg">
            </div>
            <?php } ?>
            <?php */ ?>

        </div>
        <?php
        /*
        ?>
        <?php
        if($cart_visibility=='1' && $cart_position!='top'){
        ?>
        <div id="orderReview">
            <h2>Shopping Cart</h2>
            <table id="shopping-cart" class="i_summary_content">

            </table>
            <?php if($cart_coupon['status']=='1'){ ?>
                <table class="cart_coupon_div">
                    <tr>
                        <td> <label for="cart_coupon" class="cart_coupon_title"><?php echo (trim($cart_coupon['title'])!='') ? $cart_coupon['title'] : 'Enter coupon code'; ?> </label> </td>
                        <td>
                            <div class="input_txt_under">
                                <input type="text" name="cart_coupon" id="cart_coupon" value="">
                                <p class="coupons_have">

                                </p>
                            </div>
                        </td>
                        <td> <button id="apply_coupon"><?php echo (trim($cart_coupon['btn_txt'])!='') ? $cart_coupon['btn_txt'] : 'Apply coupon'; ?></button> </td>
                    </tr>
                </table>
            <?php } ?>
        </div>
        <?php
        }
        ?>
        <?php
        */
        ?>
        <?php
        if($cart_position=='top' || $cart_visibility=='0'){
        ?>
            <div id="summaryReview">
                <table id="summary_tbl" class="i_summary_content">

                </table>
            </div>
            <!--<button id="back" onclick="window.history.back();" class="floatLeft">Back</button>
            <button id="placeOrder" class="floatRight">Buy Now</button>-->
            <span id="placeOrder" class="uc_checkout_btn">  </span>
            <table class="ssl_tbl">
                <tr>
                    <td>
                        <img src="<?php echo ULTRACART_PLUGIN_URL; ?>/images/100-secure.png" class="100proc_secure_img" >
                    </td>
                    <td style="text-align: right;">
                        <img src="<?php echo ULTRACART_PLUGIN_URL; ?>/images/PositiveSSL_tl_trans.png" class="positive_ssl" >
                    </td>
                </tr>
            </table>
        <?php
        }
        ?>
        <div class="clearfix"></div>
    </div>

    <div class="floatLeft uc_checkout_sidebar">
        <?php
        if($cart_position!='top'){
        ?>
        <img src="<?php echo ULTRACART_PLUGIN_URL; ?>/images/SSL_Tag.png" style="max-width: 230px;" alt="Safe &amp; Secure Encrypted Transactions. 128 Bit Military Grade SSL Certified">
        <?php
        }
        ?>
        <img src="<?php echo ULTRACART_PLUGIN_URL; ?>/images/checkout4.png" >

        <img src="<?php echo ULTRACART_PLUGIN_URL; ?>/images/ass_seen.jpg" >

        <img src="<?php echo ULTRACART_PLUGIN_URL; ?>/images/discreet_shipping.jpg" >
    </div>

    <?php
    //////////////////////////////////////
    if($cart_visibility=='1' && $cart_position!='top'){
    ?>
    <div class="big_cart bottom_cart">
        <div id="orderReview">
            <h2>Shopping Cart</h2>
            <table id="shopping-cart" class="i_summary_content">

            </table>
            <?php if($cart_coupon['status']=='1'){ ?>
                <table class="cart_coupon_div">
                    <tr>
                        <td> <label for="cart_coupon" class="cart_coupon_title"><?php echo (trim($cart_coupon['title'])!='') ? $cart_coupon['title'] : 'Enter coupon code'; ?> </label> </td>
                        <td>
                            <div class="input_txt_under">
                                <input type="text" name="cart_coupon" id="cart_coupon" value="">
                                <p class="coupons_have">

                                </p>
                            </div>
                        </td>
                        <td> <button id="apply_coupon" class="btn btn-primary"><?php echo (trim($cart_coupon['btn_txt'])!='') ? $cart_coupon['btn_txt'] : 'Apply coupon'; ?></button> </td>
                    </tr>
                </table>
            <?php } ?>

            <div class="payment_from_div">
                <h2 class="other_payment_h2"> You may also checkout with </h2>
                <?php if($uc_payments['amazon']==1){ ?>
                    <?php /* <span class="i_goto_pay" data-payment-type="Amazon">
                        <img src="<?php echo ULTRACART_PLUGIN_URL; ?>/images/icons/amazon.png">
                    </span> */ ?>
                    <section id="section_amazon" class="add-bottom">
                        <div id="AmazonPayButton"></div>
                        <div id="AmazonNote"></div>
                        <div id="AddressBookWidgetDiv"></div>
                        <div id="AmazonWalletWidgetDiv"></div>

                        <div id="amazonResults"></div>
                        <button id="finalizeButton" class="i_goto_pay btn btn-primary" data-payment-type="Amazon" disabled="disabled">Submit Order</button>

                        <div id="redirectUrl"></div>
                    </section>
                <?php } ?>
                <?php if($uc_payments['paypal']==1){ ?>
                    <span class="i_goto_pay the_paypal_method" data-payment-type="PayPal">
                    <img src="<?php echo ULTRACART_PLUGIN_URL; ?>/images/icons/paypal_xpress.gif">
                </span>
                <?php } ?>
            </div>
        </div>
        <?php
        if($cart_position=='top' || $cart_visibility=='0'){
        ?>
            <div id="summaryReview">
                <table id="summary_tbl" class="i_summary_content">

                </table>
            </div>
        <?php
        }
        ?>
        <!--<button id="back" onclick="window.history.back();" class="floatLeft">Back</button>
        <button id="placeOrder" class="floatRight">Buy Now</button>-->
        <span id="placeOrder" class="uc_checkout_btn">  </span>
        <img src="<?php echo ULTRACART_PLUGIN_URL; ?>/images/100-secure.png" class="100proc_secure_img" >

        <img src="<?php echo ULTRACART_PLUGIN_URL; ?>/images/PositiveSSL_tl_trans.png" class="positive_ssl" >
        <div class="clearfix"></div>
    </div>
    <?php
    }
    ?>

    <div class="clearfix"></div>
    <br />

</div>
<?php
    if($uc_amazon['sandbox']!='1'){
?>
    <script type='text/javascript' src='https://static-na.payments-amazon.com/OffAmazonPayments/us/js/Widgets.js'></script>
<?php
    } else {
?>
    <script type='text/javascript' src='https://static-na.payments-amazon.com/OffAmazonPayments/us/sandbox/js/Widgets.js'></script>
<?php
    }
?>
<?php
	get_footer();
?>
