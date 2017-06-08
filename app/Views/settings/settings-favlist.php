<?php
	settings_fields( 'simple-favorites-favlist' );

	$Favlist = new SimpleFavorites\Entities\Favlist\Favlist;
?>
<tr valign="top">
	<th scope="row"><?php _e('Favlists may support', 'simplefavorites'); ?></th>
	<td>
		<?php
		foreach ( $Favlist->getAvailableSupportTypes() as $type => $label ) :
			$display = $this->settings_repo->FavlistSupports($type);
		?>
		<div class="simple-favorites-posttype">
			<label style="display:block;margin-bottom:5px;">
				<input type="checkbox" name="simplefavorites_favlist[supports][<?php echo $type; ?>]" value="true" <?php if ( $display ) echo ' checked'; ?> /> <?php echo esc_attr($label); ?>
			</label>
		</div>
		<?php endforeach; ?>
	</td>
</tr>
