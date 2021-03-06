<?php

namespace SimpleFavorites\Entities\Favlist;

use SimpleFavorites\Config\SettingsRepository;

class Favlist
{
	/**
	* Settings Repository
	*/
	private $settings_repo;

	/**
	* List ID
	*/
	private $list_id;

	/**
	* Site ID
	*/
	private $site_id;

	/**
	* User ID
	*/
	private $user_id;


    private $title;

    private $content;

    private $status;

	/**
	* Postdata
	*/
	private $postdata;
    private $meta_key = 'simplefavorite_favlist_posts';

	/**
	* Posts
	*/
	private $posts;
    private $post_ids;



    private $updates;

	public function __construct($list_id = null, $site_id = null)
	{
		$this->settings_repo = new SettingsRepository;

        $this->list_id = $list_id;
        $this->user_id = get_current_user_id();

        global $blog_id;
        $this->site_id = ( is_multisite() && is_null($site_id) ) ? $blog_id : $site_id;
        if ( !is_multisite() ) $this->site_id = 1;

        $this->init();
	}

    private function init()
    {
        $this->title = null;
        $this->content = null;
        $this->posts = null;
        $this->post_ids = null;
        $this->updates = null;
        $this->postdata = null;

        if($this->list_id)
        {
            if($this->postdata = get_post($this->list_id))
            {
                if($this->postdata->ID && $this->postdata->post_type == 'favlist' && ($this->postdata->post_status == 'publish' || $this->postdata->post_author == $this->user_id))
                {
                    $this->title = $this->postdata->post_title;
                    $this->content = $this->postdata->post_content;
                    $this->post_ids = get_post_meta( $this->postdata->ID, $this->meta_key );
                    $this->status = $this->postdata->post_status;

                    if(empty($this->post_ids))
                    {
                        $this->post_ids = [];
                    }
                    else
                    {
						$this->post_ids = $this->post_ids[0];

						if(is_string($this->post_ids))
						{
							$this->post_ids = explode(',', $this->post_ids);
							$this->post_ids = array_filter($this->post_ids);
							$this->post_ids = array_unique($this->post_ids);
						}
						else
						{
							$this->post_ids = [];
						}
                    }
                }
                else
                {
					$this->postdata = null;
                }
            }

			// no post (favlist) found, reset to a new one
			if(empty($this->postdata))
			{
				$this->list_id = null;
				$this->init();
			}
        }
        else
        {
            $this->updates = true;
        }
    }

    public function save()
    {
        if(!isset($this->postdata))
        {
            $this->create();
        }
        else
        {
            $this->postdata->post_title = $this->title;
            $this->postdata->post_content = $this->content;
            $this->postdata->post_status = $this->status;

            $post_id = wp_update_post( $this->postdata, true );
            if (is_wp_error($post_id)) {
            	return false;
            }
            else
            {
                if(empty($this->post_ids))
                {
                    delete_post_meta($this->list_id, $this->meta_key);
                }
                else
                {
                    update_post_meta($this->list_id, $this->meta_key, join(',', $this->post_ids) );
                }
            }
        }
        $this->init();

		return true;
    }

    private function create()
    {
        if(empty($this->title))
        {
            return new \WP_Error( 'simprefavorites_error', __( 'No title given for new favlist', 'simplefavorites' ) );
        }

        $args = [
            'post_title' => trim($this->title),
            'post_content' => $this->content ? $this->content : '',
            'post_status' => $this->settings_repo->getDefaultFavlistStatus(),
            'post_type' => 'favlist',
            'comment_status' => $this->settings_repo->getDefaultCommentStatus(),
            'ping_status' => 'closed',
            'post_author' => $this->user_id
        ];

        $result = wp_insert_post( $args, true );
        if($result && !($result instanceof \WP_Error))
        {
            $this->list_id = $result;

            // store post_ids into metadata
            if(!empty($this->post_ids))
            {
                update_post_meta($this->list_id, $this->meta_key, join(',', $this->post_ids) );
            }

			$this->init();
            return true;
        }

        return $result;
    }

	public function delete()
	{
		if(isset($this->postdata->ID))
		{
			if(wp_delete_post( $this->postdata->ID, true ))
			{
				$this->list_id = null;
				$this->init();
				$this->updates = false;
				return true;
			}
		}
		return false;
	}

    public function setTitle($title)
    {
        $title = trim($title);
        if($title !== $this->title)
        {
            $this->title = trim($title);
            $this->updates = true;
        }
    }

    public function setContent($content)
    {
        $content = trim($content);
        if($content !== $this->content)
        {
            $this->content = trim($content);
            $this->updates = true;
        }
    }

    public function setStatus($status)
    {
        if(in_array($status, ['publish', 'private']))
        {
            if($status !== $this->status)
            {
                $this->status = $status;
                $this->updates = true;
            }
        }
    }

	public function getPostData($key = null)
	{
		if(is_string($key))
		{
			return isset($this->postdata->$key) ? $this->postdata->$key : null;
		}

		return $this->postdata;
	}

	public function getSiteId()
	{
		return $this->site_id;
	}

	public function getListId()
	{
		return $this->list_id;
	}

	public function getId()
	{
		return $this->getListId();
	}

	public function getUserId()
	{
		return $this->user_id;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getThumbnail()
	{
		return null;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getPostIds($as_array = false) {
		return (bool) $as_array ? $this->post_ids : join(',', $this->post_ids);
	}

	public function getCount()
	{
		return count($this->getPosts());
	}

    public function getPosts()
    {
        if(!isset($this->posts))
        {
            $this->posts = [];

            if(!empty($this->post_ids))
            {
                $args = [
                    'include' => $this->post_ids,
                    'nopaging' => true
                ];

                $this->posts = get_posts($args);
            }
        }

        return $this->posts;
    }

    public function addPost($post_id)
    {
        if(!isset($this->post_ids))
        {
            $this->post_ids = [];
        }

		$post_id = (int) $post_id;
		if(is_nan($post_id) || $post_id <= 0)
		{
			return false;
		}

        if(!in_array($post_id, $this->post_ids))
        {
            $this->post_ids[] = $post_id;
            $this->updates = true;

            return true;
        }

        return false;
    }

    public function removePost($post_id)
    {
        if(is_array($this->post_ids) && count($this->post_ids))
        {
            if (($key = array_search($post_id, $this->post_ids)) !== false)
            {
                unset($this->post_ids[$key]);
                $this->updates = true;
                return true;
            }
        }

        return false;
    }

	public function hasPost($post_id)
	{
		$post_id = (int) $post_id;
		return in_array($post_id, $this->getPostIds(true));
	}

    public function hasUpdates()
    {
        return !empty($this->updates);
    }

	public function getThumbnailIds()
	{
		$thumbnail_ids = [];

		$favlist_items = null;

		global $wpdb;

		if(!((bool) $exclude_defined))
		{
			$query = "
			SELECT
				`m`.`meta_value` AS `value`
			FROM
				`$wpdb->postmeta` `m`
			LEFT JOIN
				`$wpdb->posts` `p` ON `m`.`post_id` = `p`.`ID`
			WHERE
				`p`.`ID` = $this->list_id
			AND
				`p`.`post_type` = 'favlist'
			AND
				`m`.`meta_key` = '_thumbnail_id'
			";
			if($wpdb->get_var($query))
			{
				$thumbnail_ids[] = $wpdb->get_var($query);
			}
		}

		if($this->getPostIds())
		{
			$query = "
			SELECT
				`m`.`meta_value` AS `value`
			FROM
				`$wpdb->postmeta` `m`
			LEFT JOIN
				`$wpdb->posts` `p` ON `m`.`post_id` = `p`.`ID`
			WHERE
				`p`.`ID` IN (" . $this->getPostIds() . ")
			AND
				`m`.`meta_key` = '_thumbnail_id'
			ORDER BY FIELD(`p`.`ID`, " . $this->getPostIds() . ")
			";

			$ids = $wpdb->get_results( $query );
			if(!empty($ids))
			{
				foreach($ids as &$id)
				{
					$thumbnail_ids[] = $id->value;
				}

				$thumbnail_ids = join(',', $thumbnail_ids);

				$query = "
				SELECT
					`ID`
				FROM
					`$wpdb->posts`
				WHERE
					`ID` IN ($thumbnail_ids)
				ORDER BY FIELD(`ID`, $thumbnail_ids)
				";

				$thumbnail_ids = $wpdb->get_results( $query );
				if(!empty($thumbnail_ids))
				{
					foreach($thumbnail_ids as &$id)
					{
						$id = $id->ID;
					}
				}
			}
		}

		return $thumbnail_ids;
	}

	public function getThumbnailId()
	{
		$ids = $this->getThumbnailIds();

		if($ids)
		{
			return $ids[0];
		}

		return null;
	}
}
