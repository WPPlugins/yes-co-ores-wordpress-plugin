<?php

  /**
   * Copyright (c) 2009, SVZ Solutions All Rights Reserved.
   * Available via BSD license, see license file included for details.
   *
   * @title:        SVZ Solutions Info Window Content file
   * @authors:      Stefan van Zanden <info@svzsolutions.nl>
   * @company:      SVZ Solutions
   * @contributers:
   * @version:      0.1
   * @versionDate:  2009-10-17
   * @date:         2009-10-17
   */

  require_once('InfoWindowContent.php');

  /**
   * SVZ_Solutions_Generic_Info_Window_Content_List class
   *
   */
  class SVZ_Solutions_Generic_Info_Window_Content_List extends SVZ_Solutions_Generic_Info_Window_Content
  {
    const COMPONENT_LIST_HOLDER_CLASSNAME       = 'sg-component-list-holder';
    const COMPONENT_LIST_ITEM_HOLDER_CLASSNAME  = 'sg-component-list-item-holder';
    private $listItems                          = array();

    /**
     * Constructor
     *
     * @param void
     * @return void
     */
    public function __construct()
    {
      parent::__construct();
    }

    /**
     * Method that adds a list item to the info window
     *
     * @param string $listItemHtml
     * @return void
     */
    public function addListItemHtml($listItemHtml)
    {
      if (!is_string($listItemHtml) || empty($listItemHtml))
        throw new Exception(__METHOD__ . '; Invalid $listItemHtml, not a string or empty');

      $this->listItems[] = $listItemHtml;
    }

    /**
     * Method that checks if ther are any listItems set
     *
     * @param void
     * @return boolean
     */
    public function hasListItems()
    {
      return !empty($this->listItems);
    }

    /**
     * Method that gets the listItems for the info window
     *
     * @param void
     * @return string
     */
    public function getListItems()
    {
      return $this->listItems;
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

      if ($this->hasListItems())
      {
        $html .= '<div class="' . self::COMPONENT_LIST_HOLDER_CLASSNAME . '">';

        foreach ($this->getListItems() as $listItem)
        {
          $html .= '<div class="' . self::COMPONENT_LIST_ITEM_HOLDER_CLASSNAME . '">';

          $html .= $listItem;

          $html .= '</div>';
        }

        $html .= '</div>';
      }

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