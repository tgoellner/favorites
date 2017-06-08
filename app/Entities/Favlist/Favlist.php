<?php

namespace SimpleFavorites\Entities\Favlist;

use SimpleFavorites\Config\SettingsRepository;

class Favlist
{

	/**
	* Settings Repository
	*/
	private $settings_repo;

	public function __construct()
	{
		$this->settings_repo = new SettingsRepository;
		add_filter('init', array($this, 'registerPostType'));
	}

    public function getAvailableSupportTypes()
    {
        $supporttypes = [
            'thumbnail' => __('Thumbnail', 'simplefavorites'),
            'editor' => __('Content', 'simplefavorites'),
            'comments' => __('Comments', 'simplefavorites')
        ];

        return $supporttypes;
    }

	/**
	* Save the Favorite
	*/
	public function registerPostType()
	{
        // supports
        // thumbnail ?
        // editor / content ?
        // comments ?
        // excerpt ?

        $supports = ['title', 'author'];
        foreach($this->getAvailableSupportTypes() as $type => $label)
        {
            if($this->settings_repo->FavlistSupports($type))
            {
                $supports[] = $type;
            }
        }
        unset($type, $label);

        $args = [
            'labels'        => [
                'name'               => __('Favlists', 'simplefavorites'),
                'singular_name'      => __('Favlist', 'simplefavorites'),
                'add_new'            => __('Add New', 'simplefavorites'),
                'add_new_item'       => __('Add New Favlist', 'simplefavorites'),
                'edit_item'          => __('Edit Favlist', 'simplefavorites'),
                'new_item'           => __('New Favlist', 'simplefavorites'),
                'all_items'          => __('All Favlists', 'simplefavorites'),
                'view_item'          => __('View Favlist', 'simplefavorites'),
                'search_items'       => __('Search Favlists', 'simplefavorites'),
                'not_found'          => __('No favlists found', 'simplefavorites'),
                'not_found_in_trash' => __('No favlists found in the Trash', 'simplefavorites'),
                'parent_item_colon'  => '',
                'menu_name'          => __('Favlists', 'simplefavorites')
            ],
            'description'   => __('Users favorites posts', 'simplefavorites'),
            'public'        => true,
            'menu_position' => 10,
            'supports'      => $supports,
            'taxonomies'    => [],

            'has_archive'   => false,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'rewrite'		=> []
        ];

        register_post_type('favlist', $args);
	}

}
