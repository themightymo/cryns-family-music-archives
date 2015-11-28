<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package AppPresser Theme
 */
?>

	</div><!-- #main -->
	<?php if ( has_nav_menu( 'footer-menu' ) ) : ?>
	<footer id="colophon" class="site-footer" role="contentinfo">

		<nav class="footer-menu" role="navigation">
		    <?php
		    	$args = array(
		    	'theme_location' => 'footer-menu',
		    	'menu_class' => 'nav nav-justified' );
		    	wp_nav_menu($args);
		    ?>
		</nav>

	</footer><!-- #colophon -->
	<?php endif; ?>
</div><!-- #page -->

<?php wp_footer(); ?>
</div><!-- #body-container -->
</body>
</html>