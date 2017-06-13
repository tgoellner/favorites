<?php

namespace SimpleFavorites\Entities\Template;

use SimpleFavorites\Config\SettingsRepository;

class View
{
	/**
	* Settings Repository
	*/
	private $settings_repo;

	public function __construct($template_name = null, array $parameters = [])
	{
		$this->settings_repo = new SettingsRepository;

        $this->setTemplate($template_name);
        $this->addParam($parameters);
	}

    public function setTemplate($template_name)
    {
        if(!empty($template_name) && is_string($template_name))
        {
            $this->template_name = $template_name;
        }
    }

    public function getTemplate()
    {
        return $this->template_name;
    }

    public function getTemplateFile()
    {
        if(is_string($this->template_name) && !empty($this->template_name))
        {
            $filename = $this->template_name . '.php';
            $filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
            $filename = ltrim($filename, DIRECTORY_SEPARATOR);

            $path = '';

            if(strrpos($filename, DIRECTORY_SEPARATOR) !== false)
            {
                $path = DIRECTORY_SEPARATOR . substr($filename, 0, strrpos($filename, DIRECTORY_SEPARATOR));
                $filename = substr($filename, strrpos($filename, DIRECTORY_SEPARATOR) + 1);
            }

            // is there a template file in the theme
            $template_paths = [
                !empty($path) ? get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'template-parts' . DIRECTORY_SEPARATOR . 'favorites' . $path : '',
                get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'template-parts' . DIRECTORY_SEPARATOR . 'favorites',
                !empty($path) ? get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'favorites' . $path : '',
                get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'favorites',
                !empty($path) ? get_stylesheet_directory() . $path : '',
                get_stylesheet_directory(),

				!empty($path) ? get_template_directory() . DIRECTORY_SEPARATOR . 'template-parts' . DIRECTORY_SEPARATOR . 'favorites' . $path : '',
                get_template_directory() . DIRECTORY_SEPARATOR . 'template-parts' . DIRECTORY_SEPARATOR . 'favorites',
                !empty($path) ? get_template_directory() . DIRECTORY_SEPARATOR . 'favorites' . $path : '',
                get_template_directory() . DIRECTORY_SEPARATOR . 'favorites',
                !empty($path) ? get_template_directory() . $path : '',
                get_template_directory(),

                dirname(FAVORITES_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Views' . $path
            ];

            $template_paths = array_filter($template_paths);
            $template_paths = array_unique($template_paths);

            foreach ( $template_paths as $template_path )
            {
                if ( file_exists( $template_path . DIRECTORY_SEPARATOR . $filename ) )
                {
                    return $template_path . DIRECTORY_SEPARATOR . $filename;
                }
            }
        }

        return null;
    }

    public function addParam($key, $value = null)
    {
        if(!isset($this->params))
        {
            $this->params = [];
        }

        if(is_array($key))
        {
            $this->params = array_replace($this->params, $key);
            return;
        }

        if($value === null && isset($this->params[$key]))
        {
            unset($this->params[$key]);
        }
        else
        {
            $this->params[$key] = $value;
        }
    }

    public function getParam($key = null, $default = null)
    {
        if(!isset($this->params))
        {
            $this->params = [];
        }

        if(!empty($key))
        {
            return is_string($key) && isset($this->params[$key]) ? $this->params[$key] : $default;
        }

        return $this->params;
    }

    public function get($template_name = null, array $variables = [])
    {
        $this->setTemplate($template_name);
        $this->addParam($variables);

        $output = '';

        if($filename = $this->getTemplateFile())
        {
            extract($this->getParam());

            ob_start();
            include($filename);
            $output = ob_get_contents();
            ob_end_clean();

            foreach(array_keys($this->getParam()) as $key)
            {
                unset($$key);
            }
        }

        return $output;
    }

    public function out($template_name = null, array $variables = [])
    {
        echo $this->get($template_name, $variables);
    }
}
