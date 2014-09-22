<?php

?>
<div class="wrap">
    <h2>Ultracart Pages</h2>
    <form method="post" action="options.php">
        <?php settings_fields( 'ultracart_pages_group' ); ?>
        <?php do_settings_sections( 'ultracart_pages_group' ); ?>
        <?php
            $cart_page=get_option('ultracart_cart_page');
            $checkout_page=get_option('ultracart_checkout_page');
            $products_page=get_option('ultracart_products_page');
        ?>
        <?php $pages = get_pages();?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="cart_page"><?php _e('Cart Page'); ?></label></th>
                <td>
                    <select name="ultracart_cart_page" id="cart_page">
                        <option value="" ></option>
                        <?php
                        foreach ( $pages as $page ) {
                            $option = '<option value="' . $page->ID . '" ';
                            if ($cart_page == $page->ID) {$option .= 'selected="selected"';}
                            $option .= ' >'.$page->post_title;
                            $option .= '</option>';
                            echo $option;
                        }
                        ?>
                    </select>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="checkout_page"><?php _e('Checkout Page'); ?></label></th>
                <td>
                    <select name="ultracart_checkout_page" id="checkout_page">
                        <option value="" ></option>
                        <?php
                        foreach ( $pages as $page ) {
                            $option = '<option value="' . $page->ID . '" ';
                            if ($checkout_page == $page->ID) {$option .= 'selected="selected"';}
                            $option .= ' >'.$page->post_title;
                            $option .= '</option>';
                            echo $option;
                        }
                        ?>
                    </select>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="products_page"><?php _e('Products Page'); ?></label></th>
                <td>
                    <select name="ultracart_products_page" id="products_page">
                        <option value="" ></option>
                        <?php
                        foreach ( $pages as $page ) {
                            $option = '<option value="' . $page->ID . '" ';
                            if ($products_page == $page->ID) {$option .= 'selected="selected"';}
                            $option .= ' >'.$page->post_title;
                            $option .= '</option>';
                            echo $option;
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>