<?php

namespace SimpleFavorites\Entities\Post;

/**
* Returns the total number of favorites for a post
*/
class FavlistCount
{
	/**
	* Get the favorite count for a post
	*/
	public function getCount($post_id, $site_id = null)
	{
		if ( !is_user_logged_in() )
        {
            return 0;
        }
		$user_id = get_current_user_id();

		if ( (is_multisite()) && (isset($site_id)) && ($site_id !== "") ) switch_to_blog(intval($site_id));

		// check DB on how many lists the post is collected on
		global $wpdb;
		$query = "
		SELECT
			COUNT(*) AS `count`
		FROM
			`$wpdb->postmeta` `m`
		LEFT JOIN
			`$wpdb->posts` `p` ON `m`.`post_id` = `p`.`ID`
		WHERE
			`p`.`post_author` = " . $user_id . "
		AND
			`m`.`meta_value` LIKE CONCAT('%i:', '" . $post_id . "', ';%')
		AND
			`m`.`meta_key` = 'simplefavorite_favlist_posts'
		";

		return (int) $wpdb->get_var( $query );

		if ( (is_multisite()) && (isset($site_id) && ($site_id !== "")) ) restore_current_blog();
		return intval($count);
	}

}
