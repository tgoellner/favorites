<?php

namespace SimpleFavorites\API\Shortcodes;

class UserFavlistsShortcode
{

	/**
	* Shortcode Options
	* @var array
	*/
	private $options;

	/**
	* List Filters
	* @var array
	*/
	private $filters;

	public function __construct()
	{
		add_shortcode('user_favlists', array($this, 'renderView'));
	}

	/**
	* Shortcode Options
	*/
	private function setOptions($options)
	{
		$this->options = shortcode_atts(array(
			'user_id' => '',
			'site_id' => ''
		), $options);
	}

	/**
	* Render the HTML list
	* @param $options, array of shortcode options
	*/
	public function renderView($options)
	{
		$this->setOptions($options);

		if ( $this->options['user_id'] == "" ) $this->options['user_id'] = null;
		if ( $this->options['site_id'] == "" ) $this->options['site_id'] = null;

		return get_user_favlists($this->options['user_id'], $this->options['site_id']);
	}

}
