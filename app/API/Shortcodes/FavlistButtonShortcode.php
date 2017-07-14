<?php

namespace SimpleFavorites\API\Shortcodes;

class FavlistButtonShortcode
{

	/**
	* Shortcode Options
	* @var array
	*/
	private $options;

	public function __construct()
	{
		add_shortcode('favlist_button', array($this, 'renderView'));
	}

	/**
	* Shortcode Options
	*/
	private function setOptions($options)
	{
		$this->options = shortcode_atts(array(
			'post_id' => null,
			'site_id' => null,
			'list_id' => null,
			'type' => 'add'
		), $options);
	}

	/**
	* Render the Button
	* @param $options, array of shortcode options
	*/
	public function renderView($options)
	{
		$this->setOptions($options);
		return get_favlists_button($this->options['post_id'], $this->options['site_id'], $this->options['list_id'], $this->options['type']);
	}

}
