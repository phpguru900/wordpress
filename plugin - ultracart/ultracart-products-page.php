<?php
/*
 * Ultra products page template
 */
get_header(); ?>
    <div id="products-content">
        <?php while ( have_posts() ) { the_post(); ?>
            <blockquote id="productqoute">
                <h1><?php the_title(); ?></h1>
                <?php the_content(); ?>
            </blockquote>
        <?php } ?>
        <?php
            $args=array(
                //'post_type'=>'product'
                'post_type'=>'post'
            );
            query_posts( $args );
        ?>
            <div id="item">
                <?php while ( have_posts() ) { the_post();
                    $post_id=get_the_ID(); $prod_item_id=get_post_meta($post_id, 'uc_product_item_id',true);
                ?>
                <div class="items product_items" id="<?php echo $prod_item_id; ?>">
                    <a href="<?php echo get_permalink( $post_id ); ?>"><div class="items-inside">
                            <ul>
                                <li>
                                    <!--<img src="" alt="<?php /*echo get_the_title(); */?>" class="img_<?php /*echo $prod_item_id; */?>" >-->
                                    <?php the_post_thumbnail(); ?>
                                </li>
                                <li>
                                    <p> <?php the_title(); ?> </p>
                                    <p class="prod_desc">
                                        <?php echo get_post_meta($post_id, 'uc_product_description',true); ?>
                                    </p>
                                    Order Now ›
                                </li>
                            </ul>
                            <div class="clear"></div>
                        </div></a>
                </div>
                <?php } ?>
                <div class="clear"></div>
            </div>
    </div><!-- #content -->
<?php get_footer(); ?>

<?php
/*
<button class="addToCart" data-item-id="nettletea-60">Add to Cart</button> 1 Bag Nettle Tea

<button class="addToCart" data-item-id="3bags-nettle-tea">Add to Cart</button> 3 Bags Nettle Tea

<button class="addToCart" data-item-id="6bags-nettle-tea">Add to Cart</button> 6 Bags Nettle Tea

<button class="addToCart" data-item-id="candidaoff3bottles">Add to Cart</button> 3 Bottles of Probacto's CandidaOff

<button class="addToCart" data-item-id="candidaoff6bottles">Add to Cart</button> 6 Bottles of Probacto's CandidaOff

<button class="addToCart" data-item-id="probiotic1bottle">Add to Cart</button> 1 Bottle of Probiotics &amp; 1 Free week supply of Nettle Leaf Tea

<button class="addToCart" data-item-id="Probiotic90">Add to Cart</button> 1 Bottle of Probacto Probiotics

<button class="addToCart" data-item-id="Probiotic3">Add to Cart</button> 3 Bottles of Probacto Probiotics

<button class="addToCart" data-item-id="Probiotic6">Add to Cart</button> 6 Bottles of Probacto Probiotics

<button class="addToCart" data-item-id="probiotic3bottles">Add to Cart</button> Three bottles of probiotics, free week of nettle leaf tea, and free candida treatment book.
*/
?>