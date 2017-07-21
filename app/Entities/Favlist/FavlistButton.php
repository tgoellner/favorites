<?php

namespace SimpleFavorites\Entities\Favlist;

use SimpleFavorites\Entities\User\UserRepository;
use SimpleFavorites\Entities\Post\FavlistCount;
use SimpleFavorites\Config\SettingsRepository;
use SimpleFavorites\Entities\Template\View;

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
		if ( !$this->settings_repo->displayFavlistInPostType(get_post_type($this->post_id)) ) return false;

		$count = new FavlistCount();
		$count = $count->getPostCountInAllLists($this->post_id, $this->site_id);

		$favorited = $count > 0;

		$text = ( $favorited )
			? html_entity_decode($this->settings_repo->buttonTextFavorited())
			: html_entity_decode($this->settings_repo->buttonText());

		$template_vars = [
			'button_css' => ' is--add',
			'favlistaction' => 'add',
			'button_title' => __('Add to list(s)', 'simplefavorites' ),
			'post_id' => $this->post_id,
			'site_id' => $this->site_id,
			'list_id' => $this->list_id,
			'count' => $count,
			'favorited' => $favorited
		];

		if($favorited)
		{
			$template_vars['button_css'] .= ' has--lists';
			$template_vars['favlistaction'] = 'remove';
			$template_vars['button_title']  = __('Remove from list(s)', 'simplefavorites' );
		}

		if ( $this->settings_repo->includeLoadingIndicator() && $this->settings_repo->includeLoadingIndicatorPreload() && $loading )
		{
			$template_vars['button_css'] .= ' loading';
		}

		$button = new View('favlist/button', $template_vars);
		
		unset($template_vars);

		return $button->get();
	}

}
