<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package Mansion
 */
?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="wrap site-info">
			<a href="<?php echo esc_url( __( 'http://wordpress.org/', 'ms' ) ); ?>"><?php printf( __( 'Proudly powered by %s', 'ms' ), 'WordPress' ); ?></a>
			<span class="sep"> | </span>
			<?php printf( __( 'Theme: %1$s by %2$s.', 'ms' ), 'Codejam CMS', '<a href="http://newscorp.com" rel="designer">Newscorp</a>' ); ?>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
