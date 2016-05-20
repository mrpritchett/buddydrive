<?php
/**
 * @deprecated 2.0.0
 */
if( empty( $_POST['createdid'] ) ) :?>

	<tr id="item-<?php buddydrive_item_id();?>">

<?php endif;?>

		<td>
			<?php buddydrive_owner_or_cb();?>
		</td>
		<td>
			<div class="buddydrive-file-content"><?php buddydrive_item_icon();?>&nbsp;<a href="<?php buddydrive_action_link();?>" class="<?php buddydrive_action_link_class();?>" title="<?php esc_attr( buddydrive_item_title() );?>"<?php buddydrive_item_attribute();?>><?php buddydrive_item_title();?></a></div>
			<?php buddydrive_row_actions();?>
		</td>
		<td>
			<?php buddydrive_item_privacy();?>
		</td>
		<td>
			<?php buddydrive_item_mime_type();?>
		</td>
		<td>
			<?php buddydrive_item_date();?>
		</td>

<?php if( empty( $_POST['createdid'] ) ) :?>

	</tr>

<?php endif;?>
