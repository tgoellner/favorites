<?php

namespace SimpleFavorites\Entities\User;

use SimpleFavorites\Config\SettingsRepository;
use SimpleFavorites\Helpers;
use SimpleFavorites\Entities\Favorite\FavoritesArrayFormatter;
use SimpleFavorites\Entities\Favlist\Favlist;
use SimpleFavorites\Entities\Favlist\FavlistArrayFormatter;

class UserRepository
{

	/**
	* Settings Repository
	*/
	private $settings_repo;

	public function __construct()
	{
		$this->settings_repo = new SettingsRepository;
	}

	/**
	* Display button for current user
	* @return boolean
	*/
	public function getsButton()
	{
		if ( is_user_logged_in() ) return true;
		if ( $this->settings_repo->anonymous('display') ) return true;
		return false;
	}

	/**
	* Get All of current user's favorites (includes all sites)
	* @return array (multidimensional)
	*/
	public function getAllFavorites()
	{
		if ( is_user_logged_in() ) return $this->getLoggedInFavorites();
		$saveType = $this->settings_repo->saveType();
		$favorites = ( $saveType == 'cookie' ) ? $this->getCookieFavorites() : $this->getSessionFavorites();
		return $this->favoritesWithSiteID($favorites);
	}

	/**
	* Get User's Favorites by Site ID (includes a single site)
	* @return array (flat)
	*/
	public function getFavorites($user_id = null, $site_id = null)
	{
		if ( is_user_logged_in() || $user_id ) return $this->getLoggedInFavorites($user_id, $site_id);
		$saveType = $this->settings_repo->saveType();
		$favorites = ( $saveType == 'cookie' ) ? $this->getCookieFavorites($site_id) : $this->getSessionFavorites($site_id);
		return $favorites;
	}

	/**
	* Check for Site ID in user's favorites
	* Multisite Compatibility for >1.1
	* 1.2 compatibility with new naming structure
	* @since 1.1
	*/
	private function favoritesWithSiteID($favorites)
	{
		if ( Helpers::keyExists('site_favorites', $favorites) ){
			foreach($favorites as $key => $site_favorites){
				if ( !isset($favorites[$key]['site_favorites']) ) continue;
				$favorites[$key]['posts'] = $favorites[$key]['site_favorites'];
				unset($favorites[$key]['site_favorites']);
				if ( isset($favorites[$key]['total']) ) unset($favorites[$key]['total']);
			}
		}
		if ( Helpers::keyExists('site_id', $favorites) ) return $favorites;
		$new_favorites = array(
			array(
				'site_id' => 1,
				'posts' => $favorites
			)
		);
		return $new_favorites;
	}

	/**
	* Get Logged In User Favorites
	*/
	private function getLoggedInFavorites($user_id = null, $site_id = null)
	{
		$user_id = ( isset($user_id) ) ? $user_id : get_current_user_id();
		$favorites = get_user_meta($user_id, 'simplefavorites');

		if ( empty($favorites) ) return array(array('site_id'=>1, 'posts' => array()));

		$favorites = $this->favoritesWithSiteID($favorites[0]);

		return ( !is_null($site_id) ) ? Helpers::pluckSiteFavorites($site_id, $favorites) : $favorites;
	}

	/**
	* Get Session Favorites
	*/
	private function getSessionFavorites($site_id = null)
	{
		if ( !isset($_SESSION['simplefavorites']) ) $_SESSION['simplefavorites'] = array();
		$favorites = $_SESSION['simplefavorites'];
		$favorites = $this->favoritesWithSiteID($favorites);
		return ( !is_null($site_id) ) ? Helpers::pluckSiteFavorites($site_id, $favorites) : $favorites;
	}

	/**
	* Get Cookie Favorites
	*/
	private function getCookieFavorites($site_id = null)
	{
		if ( !isset($_COOKIE['simplefavorites']) ) $_COOKIE['simplefavorites'] = json_encode(array());
		$favorites = json_decode(stripslashes($_COOKIE['simplefavorites']), true);
		$favorites = $this->favoritesWithSiteID($favorites);
		return ( !is_null($site_id) ) ? Helpers::pluckSiteFavorites($site_id, $favorites) : $favorites;
	}

	/**
	* Has the user favorited a specified post?
	* @param int $post_id
	* @param int $site_id
	*/
	public function isFavorite($post_id, $site_id = 1, $user_id = null)
	{
		$favorites = $this->getFavorites($user_id, $site_id);
		if ( in_array($post_id, $favorites) ) return true;
		return false;
	}

	/**
	* Does the user count in total favorites?
	* @return boolean
	*/
	public function countsInTotal()
	{
		if ( is_user_logged_in() ) return true;
		return $this->settings_repo->anonymous('save');
	}

	/**
	* Format an array of favorites
	* @param $post_id - int, post to add to array (for session/cookie favorites)
	* @param $site_id - int, site id for post_id
	*/
	public function formattedFavorites($post_id = null, $site_id = null, $status = null)
	{
		$favorites = $this->getAllFavorites();
		$formatter = new FavoritesArrayFormatter;
		return $formatter->format($favorites, $post_id, $site_id, $status);
	}

	public function getAllFavlistIds()
	{
		if ( !is_user_logged_in() ) return null;

		$user_id = ( isset($user_id) ) ? $user_id : get_current_user_id();

		global $wpdb;

		// set the meta_key to the appropriate custom field meta key
		$query = $wpdb->prepare("
			SELECT `ID`
				FROM $wpdb->posts
				WHERE `post_author` = %d
				AND `post_type` = 'favlist'
				ORDER BY `post_date` DESC
			",
			$user_id
		);

		$ids = [];
		foreach($wpdb->get_results( $query ) as $row)
		{
			$ids[] = $row->ID;
		}

		unset($query, $row, $user_id);

		return $ids;
	}

	public function getAllFavlists()
	{
		$lists = [];

		if($ids = $this->getAllFavlistIds())
		{
			foreach($this->getAllFavlistIds() as $id)
			{
				$lists[$id] = new Favlist($id);
			}

			unset($id);
		}

		unset($ids);

		return $lists;
	}

	public function formattedFavlists($post_id = null, $list_id = null, $site_id = null, $status = null)
	{
		$favorites = $this->getAllFavlists();
		$formatter = new FavlistArrayFormatter;
		return $formatter->format($favorites);
	}

	public function getCurrentFavlistId()
	{
		// session? usermeta?
		$favlists = $this->getAllFavlists();

		if(count($favlists) === 1)
		{
			$list_ids = array_keys($favlists);
#			return $list_ids[0];
		}

		return null;
	}

}
