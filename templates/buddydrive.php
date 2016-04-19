<?php
/**
 * Template for BP Theme Compat
 *
 * @deprecated 2.0.0
 */
?>
<div id="buddypress">

	<?php do_action( 'buddydrive_before_directory_content' ); ?>

	<?php do_action( 'template_notices' ); ?>

	<div class="buddydrive" role="main">

		<?php do_action( 'buddydrive_directory_content' ); ?>

	</div><!-- .buddydrive -->

	<?php do_action( 'buddydrive_after_directory_content' ); ?>

</div>
