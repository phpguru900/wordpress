<?php
$uc_payments=get_option('uc_payments');
$uc_amazon=get_option('uc_amazon');
//print_r($uc_payments);
?>
<div class="wrap">
    <h2>Payments</h2>

    <form method="post" action="options.php">
        <?php settings_fields( 'ultracart_payments_settings_group' ); ?>
        <?php do_settings_sections( 'ultracart_payments_settings_group' ); ?>
        <hr>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="uc_payments_paypal"><?php _e('PayPal'); ?></label></th>
                <td>
                    <input type="checkbox" name="uc_payments[paypal]" value="1" <?php if ($uc_payments['paypal'] == '1') {echo 'checked="checked"';}?> id="uc_payments_paypal" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="uc_payments_amazon"><?php _e('Amazon'); ?></label></th>
                <td>
                    <input type="checkbox" name="uc_payments[amazon]" value="1" <?php if ($uc_payments['amazon'] == '1') {echo 'checked="checked"';}?> id="uc_payments_amazon" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="uc_amazon_merchantid"><?php _e('Amazon Merchant ID'); ?></label></th>
                <td>
                    <input type="text" name="uc_amazon[merchant_id]" value="<?php if ($uc_amazon['merchant_id']) { echo $uc_amazon['merchant_id']; }?>" id="uc_amazon_merchantid" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="uc_amazon_sandbox"><?php _e('Amazon Sandbox(test) Mode'); ?></label></th>
                <td>
                    <input type="checkbox" name="uc_amazon[sandbox]" value="1" <?php if ($uc_amazon['sandbox'] == '1') {echo 'checked="checked"';}?> id="uc_amazon_sandbox" />
                </td>
            </tr>
        </table>
        <hr>
        <?php submit_button(); ?>
    </form>
</div>
<style type="text/css">
    .cart_coupon_changer_col {
        border: 1px solid;
        padding: 5px 10px;
        display: inline-block;
        margin: 6px 0;
    }
    .w300 {
        width: 300px;
    }
</style>