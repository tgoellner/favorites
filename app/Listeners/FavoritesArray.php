<?php

namespace SimpleFavorites\Listeners;

use SimpleFavorites\Entities\User\UserRepository;

/**
* Return an array of user's favorited posts
*/
class FavoritesArray extends AJAXListenerBase
{
	/**
	* User Repository
	*/
	private $user;

	/**
	* User Favorites
	* @var array
	*/
	private $favorites;

	public function __construct()
	{
		$this->user = new UserRepository;
		$this->setFavorites();
		$this->setFavlists();

		$this->response(array(
			'status'=>'success',
			'favorites' => $this->favorites,
			'favlists' => $this->favlists
		));
	}

	/**
	* Get the Favorites
	*/
	private function setFavorites()
	{
		$favorites = $this->user->formattedFavorites();
		$this->favorites = $favorites;
	}

	/**
	* Get the Favlist posts
	*/
	private function setFavlists()
	{
		$favlists = $this->user->formattedFavlists();
		$this->favlists = $favlists;
	}
}
