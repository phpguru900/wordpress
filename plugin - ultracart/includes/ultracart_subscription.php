<?php
$uc_subscription=get_option('uc_subscription');
$uc_uc=$uc_subscription['uc'];
$uc_sendy=$uc_subscription['sendy'];
?>
<div class="wrap">
    <h2>Subscription</h2>

    <form method="post" action="options.php">
        <?php settings_fields( 'ultracart_subscription_group' ); ?>
        <?php do_settings_sections( 'ultracart_subscription_group' ); ?>
        <hr>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Ultracart Subscription</th>
                <td>
                    <input type="radio" name="uc_subscription[uc][subscription]" value="1" <?php if ($uc_uc['subscription'] == '1') {echo 'checked="checked"';}?> id="uc_uc_subscription_on" />
                    <label for="uc_uc_subscription_on"><?php _e('On'); ?></label>
                    <input type="radio" name="uc_subscription[uc][subscription]" value="0" <?php if ($uc_uc['subscription'] == '0') {echo 'checked="checked"';}?> id="uc_uc_subscription_off" />
                    <label for="uc_uc_subscription_off"><?php _e('Off'); ?></label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Sendy Subscription</th>
                <td>
                    <input type="radio" name="uc_subscription[sendy][subscription]" value="1" <?php if ($uc_sendy['subscription'] == '1') {echo 'checked="checked"';}?> id="uc_sendy_subscription_on" />
                    <label for="uc_sendy_subscription_on"><?php _e('On'); ?></label>
                    <input type="radio" name="uc_subscription[sendy][subscription]" value="0" <?php if ($uc_sendy['subscription'] == '0') {echo 'checked="checked"';}?> id="uc_sendy_subscription_off" />
                    <label for="uc_sendy_subscription_off"><?php _e('Off'); ?></label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="uc_sendy_url">Sendy URL</label></th>
                <td>
                    <input type="text" name="uc_subscription[sendy][url]" value="<?php echo $uc_sendy['url']; ?>" id="uc_sendy_url" class="w300" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="uc_sendy_list">Sendy List</label></th>
                <td>
                    <input type="text" name="uc_subscription[sendy][list]" value="<?php echo $uc_sendy['list']; ?>" id="uc_sendy_list" class="w300" />
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