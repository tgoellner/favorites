<?php

namespace SimpleFavorites\Entities\Favlist;

use SimpleFavorites\Entities\User\UserRepository;
use SimpleFavorites\Config\SettingsRepository;

class FavlistEditButton
{

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

	public function __construct($site_id, $list_id = null)
	{
		$this->user = new UserRepository;
		$this->settings_repo = new SettingsRepository;
		$this->site_id = $site_id;

		if(empty($list_id))
		{
			$list_id = $this->user->getCurrentFavlistId();
		}

		$this->list_id = $list_id;
	}

	/**
	* Diplay the Edit Button
	* @param boolean loading - whether to include loading class
	* @return html
	*/
	public function display($loading = true)
	{
        if ( !is_user_logged_in() )
        {
            return null;
        }
		$out = '<button class="simplefavorite-favlist__button is--listedit';

		if ( $this->settings_repo->includeLoadingIndicator() && $this->settings_repo->includeLoadingIndicatorPreload() && $loading ) $out .= ' loading';

		$out.= '" data-siteid="' . $this->site_id . '" data-listid="' . $this->list_id . '"
			data-favlistaction="editlist"
			title="' . esc_attr(__('Edit list', 'simplefavorites' )) . '"><span class="text">' . (__('Edit list', 'simplefavorites' )) . '</span></button>';

		return $out;
	}

}
