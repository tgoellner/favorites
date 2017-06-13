<?php

namespace SimpleFavorites\Entities\Favlist;

use SimpleFavorites\Entities\Post\FavlistCount;
use SimpleFavorites\Entities\Favlist\FavlistButton;

/**
* Format the user's favorite array to include additional post data
*/
class FavlistArrayFormatter
{
	/**
	* Formatted favorites array
	*/
	private $formatted_favlists;

	/**
	* Total Favorites Counter
	*/
	private $counter;

	public function __construct()
	{
		$this->counter = new FavlistCount;
	}

	public function format($favlists)
	{
		$this->formatted_favlists = $favlists;
		$this->resetIndexes();
		return $this->formatted_favlists;
	}

	/**
	* Reset the favorite indexes
	*/
	private function resetIndexes()
	{
		$favlists = $this->formatted_favlists;
		$this->formatted_favlists = [];

		foreach ( $favlists as $list_id => $list )
		{
			if(!isset($this->formatted_favlists[$list->getSiteId()]))
			{
				$this->formatted_favlists[$list->getSiteId()] = [
					'posts' => [],
					'site_id' => $list->getSiteId()
				];
			}
			foreach($list->getPosts() as $post)
			{
				if(!isset($this->formatted_favlists[$list->getSiteId()]['posts'][$post->ID]))
				{
					$this->formatted_favlists[$list->getSiteId()]['posts'][$post->ID] = [
						'post_type' => $post->post_type,
						'post_id' => $post->ID,
						'title' => get_the_title($post->ID),
						'permalink' => get_the_permalink($post->ID),
						'total' => $this->counter->getPostCountInAllLists($post->ID, $list->getSiteId()),
						'listids' => []
					];

					$button = new FavlistButton($post->ID, $list->getSiteId(), $list->getId());
					$this->formatted_favlists[$list->getSiteId()]['posts'][$post->ID]['button'] = $button->display(false);
				}
				$this->formatted_favlists[$list->getSiteId()]['posts'][$post->ID]['listids'][] = $list->getId();
			}
		}
		$this->formatted_favlists = array_values($this->formatted_favlists);

		unset($favlists, $post, $list, $list_id);
	}


	private function _resetIndexes()
	{
		$favlists = $this->formatted_favlists;
		$this->formatted_favlists = [];

		foreach ( $favlists as $list_id => $list )
		{
			if(!isset($this->formatted_favlists[$list->getId()]))
			{
				$this->formatted_favlists[$list->getId()] = [
					'site_id' => $list->getSiteId(),
					'title' => $list->getTitle(),
					'count' => $list->getCount(),
					'posts' => []
				];
			}

			foreach($list->getPosts() as $post)
			{
				$this->formatted_favlists[$list->getId()]['posts'][$post->ID] = [
					'post_type' => $post->post_type,
					'title' => get_the_title($post->ID),
					'post_id' => $post->ID,
					'permalink' => get_the_permalink($post->ID)
				];

				$button = new FavlistButton($post->ID, $list->getSiteId(), $list->getId());
				$this->formatted_favlists[$list->getId()]['posts'][$post->ID]['button'] = $button->display(false);
			}
		}

		unset($favlists, $post, $list, $list_id);
	}
}
