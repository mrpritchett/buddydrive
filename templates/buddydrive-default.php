<?php
/**
 * Template for BP-Default
 *
 * @deprecated 2.0.0
 */
get_header( 'buddypress' ); ?>

	<?php do_action( 'buddydrive_before_directory_page' ); ?>

	<div id="content">
		<div class="padder">

			<?php do_action( 'buddydrive_before_directory_content' ); ?>

			<h3><?php buddydrive_name();?></h3>


		<?php do_action( 'template_notices' ); ?>


		<div class="buddydrive" role="main">

			<?php do_action( 'buddydrive_directory_content' ); ?>

		</div><!-- .buddydrive -->

		<?php do_action( 'buddydrive_after_directory_content' ); ?>


		</div><!-- .padder -->
	</div><!-- #content -->

<?php get_sidebar( 'buddypress' ); ?>
<?php get_footer( 'buddypress' ); ?>
