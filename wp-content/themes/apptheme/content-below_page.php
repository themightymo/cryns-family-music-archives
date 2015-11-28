<?php
/**
 * @package AppPresser Theme
 */
?>

<section class="appp-below-post">

	<div class="btn-group btn-group-justified" role="group" aria-label="Justified button group">
		<?php

		// Display comment modal button if comments are open
		if ( comments_open() ) {
			echo '<div class="btn btn-default"><a href="#commentModal" class="io-modal-open appp-comment-btn"><i class="fa fa-comment"></i> Comment</a></div>';
		}
 
		// If AppShare installed, display sharing link
		if( class_exists('AppShare') ) {
			?>
			<div class="btn btn-default"><a onclick="window.plugins.socialsharing.share( <?php echo "'" .get_the_title() . "'"; ?>, null, null, <?php echo "'" . get_permalink() . "'"; ?> )"><i class="fa fa-share"></i> Share</a></div>
		<?php } ?>

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