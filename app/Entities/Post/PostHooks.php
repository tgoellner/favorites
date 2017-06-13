<?php

namespace SimpleFavorites\Entities\Post;

use SimpleFavorites\Config\SettingsRepository;
use SimpleFavorites\Entities\Favorite\FavoriteButton;
use SimpleFavorites\Entities\Favlist\Favlist;

/**
* Post Actions and Filters
*/
class PostHooks
{

	/**
	* Settings Repository
	*/
	private $settings_repo;

	/**
	* The Content
	*/
	private $content;

	/**
	* The Post Object
	*/
	private $post;

	public function __construct()
	{
		$this->settings_repo = new SettingsRepository;
		add_filter('the_content', array($this, 'filterContent'));

		add_filter( "get_post_metadata", array($this, 'get_post_metadata'), 1, 4);
	}

	public function get_post_metadata($tmp = null, $object_id, $meta_key, $single)
	{
		if($meta_key == '_thumbnail_id')
		{
			if(get_post_type($object_id) === 'favlist')
			{
				$favlist = new Favlist($object_id);
				if($favlist->getId())
				{
					return $favlist->getThumbnailId();
				}
			}
		}
		return null;
	}

	/**
	* Filter the Content
	*/
	public function filterContent($content)
	{
		global $post;
		$this->post = $post;
		$this->content = $content;

		if($this->settings_repo->displayInPostType($post->post_type))
		{
			$content = $this->addFavoriteButton($display);
		}

		if($this->settings_repo->displayFavlistInPostType($post->post_type))
		{
			$content = $this->addFavlistButton($display);
		}

		$content = $this->addFavlistContent();

		return $content;
	}

	/**
	* Add the Favorite Button
	* @todo add favorite button html
	*/
	private function addFavoriteButton($display_in)
	{
		$output = '';

		if ( isset($display_in['before_content']) && $display_in['before_content'] == 'true' ){
			$output .= get_favorites_button();
		}

		$output .= $this->content;

		if ( isset($display_in['after_content']) && $display_in['after_content'] == 'true' ){
			$output .= get_favorites_button();
		}
		return $output;
	}

	/**
	* Add the Favlist Button
	* @todo add favlist button html
	*/
	private function addFavlistButton($display_in)
	{
		$output = '';

		if ( isset($display_in['before_content']) && $display_in['before_content'] == 'true' ){
			$output .= get_favlist_button();
		}

		$output .= $this->content;

		if ( isset($display_in['after_content']) && $display_in['after_content'] == 'true' ){
			$output .= get_favlist_button();
		}
		return $output;
	}

	private function addFavlistContent()
	{
		$output = '';

		if ( $this->settings_repo->displayFavlistContent('before_content') ){
			$output .= get_user_favlist();
		}

		$output .= $this->content;

		if ( $this->settings_repo->displayFavlistContent('after_content') ){
			$output .= get_user_favlist();
		}
		return $output;
	}

}
