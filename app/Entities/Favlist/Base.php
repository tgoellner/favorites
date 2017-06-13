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
		add_filter('post_type_link', array($this, 'post_type_link_filter_function'), 1, 3);

		add_action('template_redirect', [ $this, 'template_redirect'] );

		add_action( 'load-post.php', [ $this, 'post_meta_boxes_setup' ] );

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

    public static function template_redirect()
    {
        global $wp_query, $wpdb, $wb;
		if (!is_404())
        {
			$private = $wpdb->get_row($wp_query->request);

	        if( 'private' == $private->post_status && 'favlist' == $private->post_type )
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
				'slug' => '/favlists/%author%'
			]
        ];

        register_post_type('favlist', $args);
	}

	function post_type_link_filter_function($post_link, $id = 0, $leavename = FALSE) {
		if (strpos('%author%', $post_link) === FALSE)
		{
			$post = &get_post($id);
			$author = get_userdata($post->post_author);
			return str_replace('%author%', $author->user_login, $post_link);
		}
	}

	function post_meta_boxes_setup()
	{
		/* Add meta boxes on the 'add_meta_boxes' hook. */
		add_action( 'add_meta_boxes', [ $this, 'add_post_meta_boxes' ] );
	}

	function add_post_meta_boxes() {

		add_meta_box(
			'favlist-items',      // Unique ID
			esc_html__( 'Items on this favlist', 'simplefavorites' ),    // Title
			[$this, 'all_favlist_items_in_post_view'],   // Callback function
			'favlist'
		);
	}

	function all_favlist_items_in_post_view( $post )
	{
		$favlist = new Favlist($post->ID);
		if($favlist->getId())
		{
			$items = $favlist->getPosts();

			if(count($items))
			{ ?><table class="wp-list-table widefat fixed striped posts">
	<thead>
		<tr>
			<th scope="col" class="column-title">
				<span><?php echo esc_attr(__('Title')); ?></span>
			</th>
			<th scope="col" class="column-title">&nbsp;</th>
		</tr>
	</thead>

	<tbody id="the-list">
		<?php

				foreach($favlist->getPosts() as $item)
				{
					$item_format = get_post_format($item->ID);
					$item_type = get_post_type($item->ID);

					?><tr id="post-<?php echo $item->ID; ?>" class="iedit author-other level-0 post-<?php echo $item->ID; ?> type-<?php echo $item_type; ?> format-<?php echo $item_format; ?> hentry post_format-post-format-<?php echo $item_format; ?>">
		    <td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
				<strong>
					<a class="row-title" href="http://www.headtalks.dev/wp-admin/post.php?post=<?php echo $item->ID; ?>&amp;action=edit" aria-label="<?php echo esc_attr($item->post_title); ?>">
						<?php echo esc_attr($item->post_title); ?>
					</a>
				</strong>
			</td>
			<td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
				<a class="row-title" href="<?php echo get_permalink($item->ID); ?>" target="_blank" aria-label="<?php echo esc_attr(__('View post', 'simplefavorites')); ?>">
					<?php echo esc_attr(__('View post', 'simplefavorites')); ?>
				</a>
			</td>
		</tr><?php
				}
?>

	</tbody>
</table><?php
			}
		}
	}
}
