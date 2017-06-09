<?php

namespace SimpleFavorites\Entities\Favlist;

use SimpleFavorites\Entities\Favlist\Favlist;
use SimpleFavorites\Entities\Template\View;
use SimpleFavorites\Config\SettingsRepository;

class Base
{

	/**
	* Settings Repository
	*/
	private $settings_repo;

	private $user;

	public function __construct()
	{
		$this->settings_repo = new SettingsRepository;
		add_filter('init', array($this, 'registerPostType'));
		add_filter('the_content', array($this, 'the_content'));
		add_action('template_redirect', [ $this, 'template_redirect'] );
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

	public function the_content($content)
	{

		if(is_single())
		{
			$post_type = get_post_type();
			if($post_type === 'favlist')
			{
				$user_id = is_user_logged_in() ? get_current_user_id() : null;
				if(get_post_status() == 'publish' || get_the_author_meta('ID') == $user_id)
				{
					$favlist = new Favlist(get_the_ID());
					$view = new View('favlist/show-favlist', [
						'list' => new Favlist(get_the_ID())
					]);

					$content = $view->get();
				}
			}
		}

		return $content;
	}

    public static function template_redirect()
    {
        global $wp_query, $wpdb, $wb;
		if (!is_404())
        {
	        $private = $wpdb->get_row($wp_query->request);
	        if( 'private' == $private->post_status  )
	        {
				$user_id = is_user_logged_in() ? get_current_user_id() : null;
				if($private->post_author != $user_id)
				{
					header("HTTP/1.0 404 Not Found");
					$wp_query->set_404(); //This will inform WordPress you have a 404 - not absolutely necessary here, but for reference only.

				}
	        }
		}
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
            'rewrite'		=> [
				'slug' => 'favlists'
			]
        ];

        register_post_type('favlist', $args);
	}

}
