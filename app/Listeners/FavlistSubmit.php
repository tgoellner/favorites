<?php

namespace SimpleFavorites\Listeners;

use SimpleFavorites\Entities\Favlist\Favlist;
use SimpleFavorites\Entities\Post\FavlistCount;
use SimpleFavorites\Entities\User\UserRepository;
use SimpleFavorites\Config\SettingsRepository;
use SimpleFavorites\Entities\Template\View;

class FavlistSubmit extends AJAXListenerBase
{
	/**
	* User Repository
	* @var SimpleFavorites\Entities\User\UserRepository
	*/
	private $user_repo;

	private $userfavlists;
	private $settings_repo;

	private $message = [];
	private $error = [];

	public function __construct()
	{
		parent::__construct();
		$this->user_repo = new UserRepository;
		$this->settings_repo = new SettingsRepository;
		$this->setFormData();
		$this->processRequest();
	}

	/**
	* Set Form Data
	*/
	private function setFormData()
	{
		$this->data['postid'] = isset( $_POST['postid'] ) ? intval(sanitize_text_field($_POST['postid'])) : null;
		$this->data['siteid'] = isset( $_POST['siteid'] ) ? intval(sanitize_text_field($_POST['siteid'])) : null;
		$this->data['listid'] = isset( $_POST['listid'] ) ? intval(sanitize_text_field($_POST['listid'])) : null;
		$this->data['listname'] = isset( $_POST['listname'] ) ? sanitize_text_field($_POST['listname']) : null;
		$this->data['action'] = isset( $_POST['favlistaction'] ) ? sanitize_text_field($_POST['favlistaction']) : null;
		$this->data['within_dialogue'] = isset( $_POST['within_dialogue'] ) ? sanitize_text_field($_POST['within_dialogue']) === 'true' : false;
	}

	private function processRequest()
	{
		if(empty($this->data['listid']))
		{
			if($this->data['action'] === 'create')
			{
				if(empty($this->data['listname']))
				{
					$this->error[] = __('No name given for new favlist.', 'simplefavorites');
				}
				else
				{
					$this->createFavlist();
				}
			}
			else if(in_array($this->data['action'], ['add', 'remove']))
			{
				if(count($this->getUserFavlists()) === 0)
				{
					if($this->data['action'] == 'add')
					{
						$this->data['within_dialogue'] = true;
						$this->message[] = __('Please select a favlist', 'simplefavorites');
					}
					else
					{
						$this->error[] = true;
					}
				}
				elseif(count($this->getUserFavlists()) === 1 && $this->settings_repo->easyAddWhenSingleFavlist())
				{
					$list_id = array_keys($this->getUserFavlists());
					$this->data['listid'] = (int) $list_id[0];

					if($this->data['action'] == 'remove')
					{
						$this->data['action'] = null;
						$this->data['within_dialogue'] = true;
					}

					$this->processRequest();
				}
				else
				{
					$this->data['within_dialogue'] = true;
					$this->message[] = __('Please select a favlist', 'simplefavorites');
				}
			}
		}
		else
		{
			$this->updateFavlist();
		}

		$this->sendResponse();
	}

	private function getUserFavlists($reload = false)
	{
		if(!isset($this->userfavlists) || (bool) $reload)
		{
			$this->userfavlists = $this->user_repo->getAllFavlists();
		}
		return $this->userfavlists;
	}

	private function getFavlistData()
	{
		return [
			'post_id' => $this->data['postid'],
			'site_id' => $this->data['siteid'],
			'list_id' => $this->data['listid'],
			'favlists' => $this->user_repo->formattedFavlists($this->data['listid'])
		];
	}

	private function updateFavlist()
	{
		$this->beforeUpdateAction();

		$favlist = new Favlist($this->data['listid'], $this->data['site_id']);

		if($favlist->getId())
		{
			$message = null;
			$error = null;

			switch($this->data['action'])
			{
				case 'edit' :
				case 'update_title' :
					if(empty($this->data['listname']))
					{
						$this->error[] = __('New name cannot be empty.', 'simplefavorites');
					}
					else
					{
						$oldtitle = $favlist->getTitle();
						$favlist->setTitle($this->data['listname']);
						$this->message[] = sprintf(__('Favlist »%s« has been renamed to »%s«.', 'simplefavorites'), $oldtitle, $favlist->getTitle());
						unset($oldtitle);
					}
					break;
				case 'publish' :
					$favlist->setStatus('publish');
					$this->message[] = sprintf(__('Favlist »%s« can now be seen by all site visitors.', 'simplefavorites'), $favlist->getTitle());
					break;
				case 'unpublish' :
					$favlist->setStatus('private');
					$this->message[] = sprintf(__('Favlist »%s« can only be seen by you.', 'simplefavorites'), $favlist->getTitle());
					break;
				case 'delete' :
					if($favlist->delete())
					{
						$this->message[] = __('Favlist has been deleted.', 'simplefavorites');
					}
					else
					{
						$this->error[] = sprintf(__('Could not delete favlist »%s«.', 'simplefavorites'), $favlist->getTitle());
					}
					break;
				case 'add' :
					if(!empty($this->data['postid']))
					{
						$favlist->addPost($this->data['postid']);
						$this->message[] = sprintf(__('Post #%d has been added to favlist »%s«.', 'simplefavorites'), $this->data['postid'], $favlist->getTitle());
					}
					break;
				case 'remove' :
					if(!empty($this->data['postid']))
					{
						$favlist->removePost($this->data['postid']);
						$this->message[] = sprintf(__('Post #%d has been deleted from favlist »%s«.', 'simplefavorites'), $this->data['postid'], $favlist->getTitle());
					}
					break;
				case 'editlist' :
					$this->data['within_dialogue'] = true;
					break;
			}

			if(empty($this->error) && $favlist->hasUpdates())
			{
				if($favlist->save())
				{
					if(empty($this->message))
					{
						$this->message[] = sprintf(__('Favlist »%s« has been updated.', 'simplefavorites'), $favlist->getTitle());
					}
				}
				else
				{
					$this->error[] = sprintf(__('Favlist »%s« could not be saved.', 'simplefavorites'), $favlist->getTitle());
				}
			}
			elseif($this->data['action'] !== 'editlist')
			{
				$this->message[] = sprintf(__('No changes done to Favlist »%s«.', 'simplefavorites'), $favlist->getTitle());
			}
		}
		else
		{
			$this->error[] = __('The selected Favlist could not be found.', 'simplefavorites');
		}

		if(!empty($this->data['listid']) && empty($this->data['postid']) && !empty($this->data['within_dialogue']))
		{
			$this->data['action'] = 'editlist';
		}

		$this->afterUpdateAction();

		$this->sendResponse();
	}

	protected function sendResponse(array $attr = [])
	{
		$args = [
			'status' => 'success',
			'message' => [],
			'html' => $this->getDialogue(),
			'favorite_data' => $this->getFavlistData()
		];

		if(!empty($this->error))
		{
			$args['status'] = 'error';
			foreach($this->error as $m)
			{
				if(is_string($m))
				{
					$args['message'][] = $m;
				}
			}
			if(empty($args['message']))
			{
				$args['message'][] = __('An error occurred while processing your request.', 'simplefavorites');
			}
		}
		else
		{
			if(!empty($this->message))
			{
				foreach($this->message as $m)
				{
					if(is_string($m))
					{
						$args['message'][] = $m;
					}
				}
			}
			if(empty($args['message']))
			{
				$args['message'][] = __('Request has been processed successful.', 'simplefavorites');
			}
		}

		$args['message'] = join("\n", $args['message']);

		if(!empty($sendResponse) && is_array($sendResponse))
		{
			$args = array_replace($args, $sendResponse);
		}

		$this->response($args);
	}

	private function getDialogue()
	{
		if(!$this->data['within_dialogue'])
		{
			return '';
		}

		$userfavlists = $this->getUserFavlists();
		if($this->data['action'] === 'editlist' && !empty($this->data['listid']) && isset($userfavlists[$this->data['listid']]))
		{
			$userfavlists = [$userfavlists[$this->data['listid']]];
		}

		$view = new View('favlist/select-favlist', [
			'lists' => $userfavlists,
			'create_list' => $this->data['action'] !== 'editlist',

			'post_id' => $this->data['postid'],
			'site_id' => $this->data['siteid'],
			'list_id' => $this->data['listid'],
			'action' => $this->data['action'],
			'listname' => $this->data['listname']
		]);

		return $view->get();
	}

	private function createFavlist()
	{
		// create a new list and restart the process...
		$favlist = new Favlist(null, $this->data['siteid']);
		$favlist->setTitle($this->data['listname']);

		if($favlist->save())
		{
			$this->data['listid'] = $favlist->getId();
			$this->data['listname'] = null;
			$this->message[] = sprintf(__('New favlist named »%s« has been created.', 'simplefavorites'), $favlist->getTitle());

			if(!empty($this->data['postid']))
			{
				$this->data['action'] = 'add';
				$this->processRequest();
			}
		}
		else
		{
			$this->error[] = sprintf(__('Could not create a new Favlist with name »%s«.', 'simplefavorites'), $this->data['listname']);
		}
		$this->sendResponse();
	}

	/**
	* Before Update Action
	* Provides hook for performing actions before a favorite
	*/
	private function beforeUpdateAction()
	{
		$user = ( is_user_logged_in() ) ? get_current_user_id() : null;
		do_action('favorites_before_favlist', $this->data, $user);
	}

	/**
	* After Update Action
	* Provides hook for performing actions after a favorite
	*/
	private function afterUpdateAction()
	{
		$user = ( is_user_logged_in() ) ? get_current_user_id() : null;
		do_action('favorites_after_favlist', $this->data, $user);
	}

}
