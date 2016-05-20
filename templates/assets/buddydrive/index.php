<?php
/**
 * BuddyDrive JS Templates
 *
 * @since 2.0.0
 */
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div id="buddydrive-main"></div>

<script type="text/html" id="tmpl-buddydrive-nav-item">
	<# if ( data.current ) { #>
		{{{data.text}}}
	<# } else { #>
		<a href="#" data-crumb="{{data.id}}" class="buddydrive-crumb">{{{data.text}}}</a>
	<# } #>
</script>

<script type="text/html" id="tmpl-buddydrive-manage-toolbar">
	<a href="#" title="{{data.text}}" data-action-id="{{data.id}}"><span class="dashicons dashicons-{{data.dashicon}}"></span></a>
</script>

<script type="text/html" id="tmpl-buddydrive-file">
	<# if ( data.uploading ) { #>
		<div id="{{data.id}}" class="buddydrive-uploading">
			<div class="buddydrive-progress">
				<div class="buddydrive-bar"></div>
			</div>
		</div>
	<# } else { #>
		<div class="buddydrive-icon">
			<# if ( 'folder' === data.type ) { #>
				<a href="#view/{{data.id}}">
			<# } else { #>
				<a href="{{data.link}}">
			<# } #><img src="{{data.icon}}" width="{{data.icon_width}}"></a>
		</div>
		<div class="buddydrive-content">
			<div class="buddydrive-title">
				<span class="buddydrive-name">
					<# if ( 'folder' === data.type ) { #>
						<a href="#view/{{data.id}}">
					<# } else { #>
						<a href="{{data.link}}">
					<# } #>{{data.title}}
						</a>
					</span>
				<span class="icon-privacy {{data.check_for}}"></span>
			</div>
			<# if ( data.content ) { #>
				<div class="buddydrive-description">
					<p class="buddydrive-description">{{data.content}}</p>
				</div>
			<# } #>

			<?php do_action( 'buddydrive_js_template_before_actions' ); ?>

			<div class="buddydrive-actions">
				<# if ( 'undefined' !== typeof data.user_avatar ) { #>
					<a class="buddydrive-owner" href="{{data.user_link}}" data-user-id="{{data.user_id}}">{{{data.user_avatar}}}</a>
				<# } #>

				<# if ( data.can_edit ) { #>
					<a class="buddydrive-edit" href="#edit/{{data.id}}"><span class="screen-reader-text bp-screen-reader-text"><?php esc_html_e( 'Edit', 'buddydrive' );?></span></a>
				<# } #>

				<?php do_action( 'buddydrive_js_template_file_actions' ); ?>

				<# if ( data.can_share ) { #>
					<a class="buddydrive-share" href="#"><span class="screen-reader-text bp-screen-reader-text"><?php esc_html_e( 'Share', 'buddydrive' );?></span></a>
				<# } #>
			</div>
		</div>
		<# if ( data.can_share ) { #>
			<div class="buddydrive-share-dialog buddydrive-hide">
				<div class="buddydrive-share-content">
					<div class="buddydrive-share-input">
						<p class="description"><?php esc_html_e( 'Use the following link to share an embed code on this site.', 'buddydrive' ); ?></p>
						<input type="text" class="buddydrive-share-url" value="{{data.link}}" tabindex="0" readonly>
					</div>
				</div>
				<button type="button" class="buddydrive-share-dialog-close" aria-label="<?php esc_attr_e( 'Close sharing dialog', 'buddydrive' ); ?>">
					<span class="dashicons dashicons-no"></span>
				</button>
			</div>
		<# } #>
	<# } #>
</script>

<script type="text/html" id="tmpl-buddydrive-edit-header">
	<div class="buddydrive-icon">
		<a href="{{data.link}}"><img src="{{data.icon}}"></a>
	</div>
	<h2 class="buddydrive-title"><?php esc_html_e( 'Editing:', 'buddydrive' ); ?> <a href="{{data.link}}">{{data.title}}</a></h2>
</script>

<script type="text/html" id="tmpl-buddydrive-object">
	<# if ( data.selected ) { #>
		<input type="hidden" value="{{data.id}}" name="buddydrive_object[]">
	<# } #>

	<# if ( data.avatar ) { #>
		{{{data.avatar}}}
	<# } #>

	<span class="buddydrive-object-name">{{data.name}}</span>

	<# if ( data.selected ) { #>
		<a href="#" class="buddydrive-object-remove" data-item_id="{{data.id}}">
			<span class="screen-reader-text bp-screen-reader-text"><?php esc_html_e( 'Remove', 'buddydrive' ); ?></span>
		</a>
	<# } #>
</script>

<script type="text/html" id="tmpl-buddydrive-edit-details">
	<# if ( 'undefined' !== typeof data.user_avatar ) { #>
		<label for="buddydrive-owner-edit"><?php esc_html_e( 'Owner', 'buddydrive' ); ?></label>
		<a class="buddydrive-owner" href="{{data.user_link}}" data-user-id="{{data.user_id}}">{{{data.user_avatar}}}</a>
		<input type="hidden" value="{{data.user_id}}" name="buddydrive-owner-edit" id="buddydrive-owner-edit">
	<# } #>

	<label for="buddydrive-title-edit"><?php esc_html_e( 'Name', 'buddydrive' ); ?></label>
	<input type="text" value="{{data.title}}" name="title" id="buddydrive-title-edit">

	<label for="buddydrive-content-edit"><?php esc_html_e( 'Description', 'buddydrive' ); ?></label>
	<textarea name="content" id="buddydrive-content-edit" maxlength="140">{{{data.content}}}</textarea>

	<# if ( 'undefined' !== typeof data.post_parent_title ) { #>
		<div id="buddydrive-item-folder">
			<label for="buddydrive-folder-edit"><?php esc_html_e( 'Parent folder', 'buddydrive' ); ?></label>
			<span class="icon-privacy {{data.check_for}}"></span> <a class="buddydrive-parent" href="#view/{{data.post_parent}}" data-folder-id="{{data.post_parent}}">{{data.post_parent_title}}</a>
			<a id="buddydrive-remove-parent" href="#" title="<?php esc_attr_e( 'Remove from this folder', 'buddydrive' ); ?>">
				<span class="screen-reader-text bp-screen-reader-text">
					<?php esc_html_e( 'Remove from this folder', 'buddydrive' );?>
				</span>
			</a>
			<input type="hidden" name="privacy" value="folder" id="buddydrive-folder-edit">
			<input type="hidden" name="buddydrive_object[]" value="{{data.post_parent}}" id="buddydrive-folder-edit-value">
			<p class="descrition"><?php esc_html_e( 'Privacy of this item rely on its parent folder.', 'buddydrive' ); ?></p>
		</div>
	<# } #>

	<div id="buddydrive-privacy-edit"></div>

	<div class="buddydrive-clear"></div>

	<?php do_action( 'buddydrive_edit_custom_fields' ); ?>

	<div class="submit">
		<input type="reset" value="<?php esc_attr_e( 'Cancel', 'buddydrive' ); ?>" class="button button-secondary"><input type="submit" value="<?php esc_attr_e( 'Edit', 'buddydrive' ); ?>"  class="button button-primary">
	</div>
</script>

<script type="text/html" id="tmpl-buddydrive-stats">
	<# if ( 'undefined' === typeof data.detail ) { #>
		<div class="buddydrive-stats-loading"></div>
	<# } else { #>

		<p><?php esc_html_e( 'Space used:', 'buddydrive' ); ?> <strong>{{data.used}}%</strong> {{data.total}}</p>

		<# if ( data.detail ) { #>
			<ul>
			<# for ( i in data.detail ) { #>
					<li><span class="icon-privacy {{data.detail[i].type}}"></span>{{data.detail[i].label}} <strong>{{data.detail[i].stat}}</strong></li>
			<# } #>
			</ul>
		<# } #>

	<# } #>
</script>

<?php
/**
 * Load the Uploader JS Template if needed
 */
if ( buddydrive_current_user_can( 'buddydrive_upload' ) ) {
	bp_attachments_get_template_part( 'uploader' );
}
