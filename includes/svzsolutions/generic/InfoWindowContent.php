<?php

  /**
   * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Generic Info Window Content file
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.1
   * @versionDate:  2009-10-17
   * @date:         2009-10-17
   */

  /**
   * SVZ_Solutions_Generic_Info_Window_Content class
   *
   */
  class SVZ_Solutions_Generic_Info_Window_Content
  {
    const COMPONENT_TAB_HOLDER_CLASSNAME            = 'sg-component-tabs-holder';
    const COMPONENT_TAB_LINKS_HOLDER_CLASSNAME      = 'sg-component-tab-links-holder';
    const COMPONENT_TAB_LINK_LOAD_DYNAMIC_CLASSNAME = 'sg-component-tab-link-load-dynamic';
    const COMPONENT_TAB_CONTENTS_HOLDER_CLASSNAME   = 'sg-component-tab-contents-holder';
    const COMPONENT_TAB_CONTENT_HOLDER_CLASSNAME    = 'sg-component-tab-content-holder';
    const MAIN_CLASSNAME                            = 'sg-info-window-content-main-holder';
    const HEADER_CLASSNAME                          = 'sg-info-window-content-header-holder';
    const CONTENT_CLASSNAME                         = 'sg-info-window-content-holder';
    const FOOTER_CLASSNAME                          = 'sg-info-window-content-footer-holder';
    const CONFIG_CLASSNAME													= 'sg-info-window-content-config';

    private $tabs       = array();
    private $className  = 'sg-info-window-content';
    private $headerHtml = '';
    private $footerHtml = '';
    private $content		= '';
    private $config			= false;

    /**
     * Constructor
     *
     * @param void
     * @return void
     */
    public function __construct()
    {

    }
    
    /**
     * Method setJSONConfig which will allow for a config to be given to the Javascript object by JSON encoding it
     * 
     * @param object $config (JSON encoded string or an object / array which will be auto json)
     * @return void
     */
    public function setConfig($config)
    {
    	if (!is_string($config))
    		$config = json_encode($config);
    	
    	$this->config = $config;
    }

    /**
     * Method that sets the header html for the info window
     *
     * @param string $headerHtml
     * @return void
     */
    public function setHeaderHtml($headerHtml)
    {
      if (!is_string($headerHtml))
        throw new Exception(__METHOD__ . '; Invalid $headerHtml, not a string');

      $this->headerHtml = $headerHtml;
    }

    /**
     * Method that checks if the header html for the info window is set
     *
     * @param void
     * @return boolean
     */
    public function hasHeaderHtml()
    {
      return !empty($this->headerHtml);
    }

    /**
     * Method that gets the header html for the info window
     *
     * @param void
     * @return string
     */
    public function getHeaderHtml()
    {
      return '<div class="' . self::HEADER_CLASSNAME . '">' . $this->headerHtml . '</div>';
    }

    /**
     * Method that sets the footer html for the info window
     *
     * @param string $headerHtml
     * @return void
     */
    public function setFooterHtml($footerHtml)
    {
      if (!is_string($footerHtml))
        throw new Exception(__METHOD__ . '; Invalid $footerHtml, not a string');

      $this->footerHtml .= $footerHtml;
    }

    /**
     * Method that checks if the footer html for the info window is set
     *
     * @param void
     * @return boolean
     */
    public function hasFooterHtml()
    {
      return !empty($this->footerHtml);
    }

    /**
     * Method that gets the footer html for the info window
     *
     * @param void
     * @return string
     */
    public function getFooterHtml()
    {
      return '<div class="' . self::FOOTER_CLASSNAME . '">' . $this->footerHtml . '</div>';
    }

    /**
     * Method that adds a class name to the surrounding element of the info window
     *
     * @param string $className
     * @return void
     */
    public function addClassName($className)
    {
      if (!is_string($className) || empty($className))
        throw new Exception(__METHOD__ . '; Invalid $className, not a string or empty');

      $this->className .= ' ' . $className;
    }

    /**
     * Method that returns the class name to the surrounding element of the info window
     *
     * @param void
     * @return string
     */
    public function getClassName()
    {
      return $this->className;
    }
    
    /**
     * Method setContent which will set the content 
     * 
     * @param string $content
     * @return void
     */
    public function setContent($content)
    {
    	$this->content = $content;
    }
    
  	/**
     * Method getContentHtml which will return the content 
     * 
     * @param void
     * @return string $content
     */
    public function getContentHtml()
    {
    	return '<div class="' . self::CONTENT_CLASSNAME . '">' . $this->content . '</div>';
    }

    /**
     * Method that adds a tab to the info window
     *
     * @param string $label
     * @param string $content
     * @return void
     */
    public function addTab($label, $content, $href = '#')
    {
      if (!is_string($label) || empty($label))
        throw new Exception(__METHOD__ . '; Invalid $label, not a string or empty');

      if (!is_string($content))
        throw new Exception(__METHOD__ . '; Invalid $content, not a string');

      if (!is_string($href))
        throw new Exception(__METHOD__ . '; Invalid $href, not a string');

      $this->tabs[] = array('label' => $label, 'content' => $content, 'href' => $href);
    }

    /**
     * Method that checks if ther are any tabs set
     *
     * @param void
     * @return boolean
     */
    public function hasTabs()
    {
      return !empty($this->tabs);
    }

    /**
     * Method that gets the tabs for the info window
     *
     * @param void
     * @return string
     */
    public function getTabs()
    {
      return $this->tabs;
    }

    /**
     * Method that gets the tab html for the info window
     *
     * @param void
     * @return string
     */
    public function getTabHtml()
    {
      $html = '';

      if ($this->hasTabs())
      {
        $html .= '<div class="' . self::COMPONENT_TAB_HOLDER_CLASSNAME . '">';

        $html .= '<div class="' . self::COMPONENT_TAB_LINKS_HOLDER_CLASSNAME . '">';

        $html .= '<ul>';

        $activeClass = 'active';

        // Generate the tab links
        foreach ($this->getTabs() as $tab)
        {
          $extraClass = '';

          if (!empty($tab['href']) && $tab['href'] != '#')
            $extraClass .= ' ' . self::COMPONENT_TAB_LINK_LOAD_DYNAMIC_CLASSNAME;

          $html .= '<li><a' . (!empty($activeClass) || !empty($extraClass) ? ' class="' . $activeClass . $extraClass . '"' : '') . ' href="' . $tab['href'] . '">' . $tab['label'] . '</a></li>';

          $activeClass = '';
        }

        $html .= '</ul>';

        $html .= '</div>';

        // Generate the tab contents
        $html .= '<div class="' . self::COMPONENT_TAB_CONTENTS_HOLDER_CLASSNAME . '">';

        $activeClass = 'active';

        // Generate the tab links
        foreach ($this->getTabs() as $tab)
        {
          $html .= '<div class="' . self::COMPONENT_TAB_CONTENT_HOLDER_CLASSNAME . (!empty($activeClass) ? ' ' . $activeClass : '') . '">' . $tab['content'] . '</div>';

          $activeClass = '';
        }

        $html .= '</div>';

        $html .= '</div>';
      }

      return $html;
    }

    /**
     * Method that returns the html for the info window
     *
     * @param void
     * @return string
     */
    public function getHTML()
    {
      $html = '<div class="' . $this->getClassName() . '">';
      
      if ($this->config !== false)
      	$html .= '<div class="sg-config ' . self::CONFIG_CLASSNAME . '">' . $this->config . '</div>';

      $html .= '<div class="' . self::MAIN_CLASSNAME . '">';
      
      if ($this->hasHeaderHtml())
        $html .= $this->getHeaderHtml();

      if ($this->hasTabs())
      	$html .= $this->getTabHtml();
      else
      	$html .= $this->getContentHtml();

      if ($this->hasFooterHtml())
        $html .= $this->getFooterHtml();

      $html .= '</div>';

      $html .= '</div>';

      return $html;
    }
  }

?>