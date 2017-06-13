<?php
	settings_fields( 'simple-favorites-favlist' );

	$Favlist = new SimpleFavorites\Entities\Favlist\Base;
?>
<tr valign="top">
	<th scope="row"><?php _e('Favlists may support', 'simplefavorites'); ?></th>
	<td>
		<div><?php
		foreach ( $Favlist->getAvailableSupportTypes() as $type => $label ) :
			$display = $this->settings_repo->FavlistSupports($type);
		?>

			<label style="display:block;margin-bottom:5px;">
				<input type="checkbox" name="simplefavorites_favlist[supports][<?php echo $type; ?>]" value="true" <?php if ( $display ) echo ' checked'; ?> /> <?php echo esc_attr($label); ?>
			</label>
		<?php endforeach; ?></div>
	</td>
</tr>
<tr valign="top">
	<th scope="row"><?php _e('Favlist UX options', 'simplefavorites'); ?></th>
	<td>
		<div class="simple-favorites-posttype">
			<label style="display:block;margin-bottom:5px;">
				<input type="checkbox" name="simplefavorites_favlist[easy_add_when_single_favlist]" value="true" <?php if ( $this->settings_repo->easyAddWhenSingleFavlist() ) echo ' checked'; ?> /> <?php _e('When only one list exists items will be added to the single list (instead of showing the popup)', 'simplefavorites') ?>
			</label>
		</div>
	</td>
</tr>
<tr valign="top">
	<th scope="row"><?php _e('Enabled Post Types', 'simplefavorites'); ?></th>
	<td>
		<?php
		foreach ( $this->post_type_repo->getAllPostTypes() as $posttype ) :
			if($posttype === 'favlist') continue;
			$display = $this->settings_repo->displayFavlistInPostType($posttype);
		?>
		<div class="simple-favorites-posttype">
			<label style="display:block;margin-bottom:5px;">
				<input type="checkbox" name="simplefavorites_favlist[posttypes][<?php echo $posttype; ?>][display]" value="true" <?php if ( $display ) echo ' checked'; ?> data-sf-posttype /> <?php echo $posttype; ?>
			</label>
			<div class="simple-favorites-posttype-locations" <?php if ( $display ) echo ' style="display:block;"'; ?>>
				<label>
					<input type="checkbox" name="simplefavorites_favlist[posttypes][<?php echo $posttype; ?>][before_content]" value="true" <?php if ( isset($display['before_content']) ) echo ' checked'; ?>/> <?php _e('Insert Before Content', 'simplefavorites') ?>
				</label>
				<label>
					<input type="checkbox" name="simplefavorites_favlist[posttypes][<?php echo $posttype; ?>][after_content]" value="true" <?php if ( isset($display['after_content']) ) echo ' checked'; ?>/> <?php _e('Insert After Content', 'simplefavorites') ?>
				</label>
			</div>
		</div>
		<?php endforeach; ?>
	</td>
</tr>
<tr valign="top">
	<th scope="row"><?php _e('Insert favlist output automatically into favlist post', 'simplefavorites'); ?></th>
	<td>
		<div class="simple-favorites-posttype">
			<label style="display:block;margin-bottom:5px;">
				<input type="checkbox" name="simplefavorites_favlist[favlist_content][before_content]" value="true" <?php if ( $this->settings_repo->displayFavlistContent('before_content') ) echo ' checked'; ?> /> <?php _e('Insert Before Content', 'simplefavorites') ?>
			</label>
			<label style="display:block;margin-bottom:5px;">
				<input type="checkbox" name="simplefavorites_favlist[favlist_content][after_content]" value="true" <?php if ( $this->settings_repo->displayFavlistContent('after_content') ) echo ' checked'; ?> /> <?php _e('Insert After Content', 'simplefavorites') ?>
			</label>
		</div>
	</td>
</tr>
