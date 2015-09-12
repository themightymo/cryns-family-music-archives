<?php
/**
 * @package AppPresser Theme
 */
?>

<section class="appp-below-post">

	<div class="btn-group btn-group-justified" role="group" aria-label="Justified button group">
		<?php

		// Display next/previous links
		$previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
		$next = get_adjacent_post( false, '', false );

		if ( $previous ) {
			previous_post_link( '<div class="btn btn-default">%link</div>', '<i class="fa fa-long-arrow-left"></i> Previous' );
		} 

		// Display comment modal button if comments are open
		if ( comments_open() ) {
			echo '<a href="#commentModal" class="btn btn-default io-modal-open appp-comment-btn"><i class="fa fa-comment"></i> Comment</a>';
		}
 
		// If AppShare installed, display sharing link
		if( class_exists('AppShare') ) {
			?>
			<a onclick="window.plugins.socialsharing.share( <?php echo "'" .get_the_title() . "'"; ?>, null, null, <?php echo "'" . get_permalink() . "'"; ?> )" class="btn btn-default" role="button"><i class="fa fa-share"></i> Share</a>
		<?php } ?>

		<?php 

		if ( $next ) {
			next_post_link( '<div class="btn btn-default">%link</div>', 'Next <i class="fa fa-long-arrow-right"></i>' ); 
		} ?>

    </div>

</section>

<?php
	// If comments are open or we have at least one comment, load up the comment template
	if ( comments_open() || '0' != get_comments_number() ) {
		comments_template();
	}

	// Display comment modal button if comments are open
	if ( comments_open() && '5' <= get_comments_number() ) {
		echo '<a href="#commentModal" class="btn btn-primary io-modal-open appp-comment-btn"><i class="fa fa-comment"></i> Leave a Comment</a>';
	}
?>