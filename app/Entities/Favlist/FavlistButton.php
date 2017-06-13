<?php

namespace SimpleFavorites\Entities\Favlist;

use SimpleFavorites\Entities\User\UserRepository;
use SimpleFavorites\Entities\Post\FavlistCount;
use SimpleFavorites\Config\SettingsRepository;

class FavlistButton
{

	/**
	* The Post ID
	*/
	private $post_id;

	/**
	* List ID
	*/
	private $list_id;

	/**
	* Site ID
	*/
	private $site_id;

	/**
	* User Respository
	*/
	private $user;

	/**
	* Settings Repository
	*/
	private $settings_repo;

	public function __construct($post_id, $site_id, $list_id = null)
	{
		$this->user = new UserRepository;
		$this->settings_repo = new SettingsRepository;
		$this->post_id = $post_id;
		$this->site_id = $site_id;

		if(empty($list_id))
		{
			$list_id = $this->user->getCurrentFavlistId();
		}

		$this->list_id = $list_id;
	}

	/**
	* Diplay the Button
	* @param boolean loading - whether to include loading class
	* @return html
	*/
	public function display($loading = true)
	{
        if ( !is_user_logged_in() )
        {
            return null;
        }

		$count = new FavlistCount();
		$count = $count->getPostCountInAllLists($this->post_id, $this->site_id);

		$favorited = $count > 0;

		$text = ( $favorited )
			? html_entity_decode($this->settings_repo->buttonTextFavorited())
			: html_entity_decode($this->settings_repo->buttonText());

		$out = '<button class="simplefavorite-favlist__button is--add' . ($favorited ? ' has--lists' : '') . '"
			data-siteid="' . $this->site_id . '"
			data-postid="' . $this->post_id . '"
			data-favlistaction="' . ($favorited ? 'remove' : 'add') . '">' . esc_attr(__($favorited ? 'Remove from list(s)' : 'Add to list(s)', 'simplefavorites' )) . '</button>';

		return $out;
	}

}
