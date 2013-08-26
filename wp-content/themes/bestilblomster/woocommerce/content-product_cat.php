<?php
/**
 * The template for displaying product category thumbnails within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-product_cat.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */
/**
 * Addition: display cat description
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce_loop;

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) )
	$woocommerce_loop['loop'] = 0;

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) )
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );

// Increase loop count
$woocommerce_loop['loop']++;
?>
<li class="product product-category<?php
	if ( $woocommerce_loop['loop'] % $woocommerce_loop['columns'] == 0 )
		echo ' last';
	elseif ( ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] == 0 )
		echo ' first';
	?>">

	<?php do_action( 'woocommerce_before_subcategory', $category ); ?>

	<a href="<?php echo get_term_link( $category->slug, 'product_cat' ); ?>">

		<?php
			/**
			 * woocommerce_before_subcategory_title hook
			 *
			 * @hooked woocommerce_subcategory_thumbnail - 10
			 */
			do_action( 'woocommerce_before_subcategory_title', $category );
		?>

		<h3>
			<?php echo $category->name; ?>
			<?php if ( $category->count > 0 ) : ?>
				<mark class="count">(<?php echo $category->count; ?>)</mark>
			<?php endif; ?>
		</h3>

		<?php
			if ( is_home() ) {
				echo '<span class="view-more">' .__('View products', 'woothemes') . '</span>';
			}
		?>

		<?php
			/**
			 * woocommerce_after_subcategory_title hook
			 */
			do_action( 'woocommerce_after_subcategory_title', $category );
		?>

		<?php
			if ( is_home() && $category->description !== '' ) {
				echo '<div class="description">' . superstore_truncate($category->description,20) . '</div>';
			}
		?>

	</a>

	<?php do_action( 'woocommerce_after_subcategory', $category ); ?>

</li>