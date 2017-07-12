<?php
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_translation.php');

  /**
  * @desc YogProjectTranslationAbstract
  * @author Kees Brandenburg - Yes-co Nederland
  */
  abstract class YogProjectTranslationAbstract extends YogTranslationAbstract
  {
    protected $mcp3Project;
    protected $mcp3Link;

    /**
    * @desc Constructor
    *
    * @param Yog3McpXmlProjectAbstract $mcp3Project
    * @param Yog3McpProjectLink $mcp3Link
    * @return YogProjectTranslationAbstract
    */
    private function __construct(Yog3McpXmlProjectAbstract $mcp3Project, Yog3McpProjectLink $mcp3Link)
    {
      $this->mcp3Project  = $mcp3Project;
      $this->mcp3Link     = $mcp3Link;
    }

    /**
    * @desc Create from Yog3McpProjectAbstract
    *
    * @param Yog3McpXmlProjectAbstract $mcp3Project
    * @param Yog3McpProjectLink $mcp3Link
    * @return YogProjectTranslationAbstract
    */
    static public function create(Yog3McpXmlProjectAbstract $mcp3Project, Yog3McpProjectLink $mcp3Link)
    {
      if ($mcp3Project instanceOf Yog3McpXmlProjectWonen)
      {
        require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_project_wonen_translation.php');

        return new YogProjectWonenTranslation($mcp3Project, $mcp3Link);
      }
      else if ($mcp3Project instanceOf Yog3McpXmlProjectBog)
      {
        require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_project_bog_translation.php');

        return new YogProjectBogTranslation($mcp3Project, $mcp3Link);
      }
      else if ($mcp3Project instanceOf Yog3McpXmlProjectNBpr)
      {
        require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_project_nbpr_translation.php');

        return new YogProjectNBprTranslation($mcp3Project, $mcp3Link);
      }
      else if ($mcp3Project instanceOf Yog3McpXmlProjectNBty)
      {
        require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_project_nbty_translation.php');

        return new YogProjectNBtyTranslation($mcp3Project, $mcp3Link);
      }
      else if ($mcp3Project instanceOf Yog3McpXmlProjectNBbn)
      {
        require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_project_nbbn_translation.php');

        return new YogProjectNBbnTranslation($mcp3Project, $mcp3Link);
      }
      else if ($mcp3Project instanceOf Yog3McpXmlProjectBBpr)
      {
        require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_project_bbpr_translation.php');

        return new YogProjectBBprTranslation($mcp3Project, $mcp3Link);
      }
      else if ($mcp3Project instanceOf Yog3McpXmlProjectBBty)
      {
        require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_project_bbty_translation.php');

        return new YogProjectBBtyTranslation($mcp3Project, $mcp3Link);
      }
      else
      {
        throw new YogException(__METHOD__ . '; Unsupported 3mcp project', YogException::GLOBAL_ERROR);
      }
    }

    /**
    * @desc Get the data for the post
    *
    * @param void
    * @return array
    */
    public function getPostData()
    {
		  $data['post_title']         = $this->determineTitle();
	    $data['post_content']       = $this->getContent();
	    $data['post_status']        = 'publish';
	    $data['post_author']        = 1;
	    $data['menu_order']         = 0;
	    $data['comment_status']     = 'closed';
	    $data['ping_status']        = 'closed';
	    $data['post_date']          = $this->translateDate($this->mcp3Link->getDoc());
	    $data['post_parent']        = 0;
	    $data['post_type']          = $this->getPostType();

      return $data;
    }

    /**
    * @desc Check if a parent project uuid is set
    * Can be overwritten by the extending translation class
    *
    * @param void
    * @return bool
    */
    public function hasParentUuid()
    {
      return false;
    }

    /**
    * @desc Get the parent uuid
    * Can be overwritten by the extending translation class
    *
    * @param void
    * @return string
    * @throws Exception
    */
    public function getParentUuid()
    {
      throw new YogException(__METHOD__ . '; No parent uuid', YogException::GLOBAL_ERROR);
    }

    /**
    * @desc Get video data
    *
    * @param void
    * @return array
    */
    public function getVideos()
    {
      $videoXmls  = $this->mcp3Project->getMediaVideos();
      $videos     = array();

      foreach ($videoXmls as $videoXml)
      {
        $uuid         = $videoXml->getStringByPath('@uuid');
        $serviceUri   = $videoXml->getStringByPath('project:Video/project:VideoReference/project:ServiceUri');
        $referenceId  = $videoXml->getStringByPath('project:Video/project:VideoReference/project:Id');
        $websiteUrl   = $videoXml->getStringByPath('project:Video/project:WebsiteUrl');

        // Determine service URI (if empty)
        if (empty($serviceUri) && !empty($websiteUrl))
        {
          if (preg_match('#(http|https)://vimeo.com/([0-9]+)#', $websiteUrl, $matches))
          {
            $serviceUri   = 'http://vimeo.com';
            $referenceId  = $matches[2];
          }
        }

        // Determine stream url
        $streamUrl  = $videoXml->getStringByPath('project:Video/project:VideoStreamUrl');

        if (empty($streamUrl) && !empty($referenceId))
        {
          switch ($serviceUri)
          {
            case 'http://www.youtube.com':
              $streamUrl = 'http://www.youtube.com/v/' . $referenceId;
              break;
            case 'http://vimeo.com':
              $streamUrl = 'http://player.vimeo.com/video/' . $referenceId;
              break;
          }
        }

        $videos[$uuid] = array(
          'uuid'                        => $uuid,
          'order'                       => $videoXml->getStringByPath('project:Video/@order'),
          'title'                       => $videoXml->getStringByPath('project:Video/project:Title'),
          'videostreamurl'              => $streamUrl,
          'websiteurl'                  => $websiteUrl,
          'videoereference_serviceuri'  => $serviceUri,
          'videoereference_id'          => $referenceId
        );
      }

      return $videos;
    }

    /**
    * @desc Get external document data
    *
    * @param void
    * @return array
    */
    public function getExternalDocuments()
    {
      $documentXmls = $this->mcp3Project->getMediaDocuments();
      $documents    = array();

      foreach ($documentXmls as $documentXml)
      {
        $uuid = $documentXml->getStringByPath('@uuid');

        $documents[$uuid] = array(
          'uuid'      => $uuid,
          'order'     => $documentXml->getStringByPath('project:Document/@order'),
          'title'     => $documentXml->getStringByPath('project:Document/project:Title'),
          'type'      => $documentXml->getStringByPath('project:Document/project:Type'),
          'url'       => $documentXml->getStringByPath('project:Document/project:SourceUrl')
        );
      }

      return $documents;
    }

    /**
    * @desc Get link data
    *
    * @param void
    * @return array
    */
    public function getLinks()
    {
      $linkXmls = $this->mcp3Project->getLinks();
      $links    = array();

      foreach ($linkXmls as $linkXml)
      {
        $uuid = $linkXml->getStringByPath('@uuid');

        $links[$uuid] = array(
          'uuid'  => $uuid,
          'order' => $linkXml->getStringByPath('@order'),
          'title' => $linkXml->getStringByPath('project:Title'),
          'type'  => $linkXml->getStringByPath('project:Type'),
          'url'   => $linkXml->getStringByPath('project:Url')
        );
      }

      return $links;
    }

    /**
    * @desc Get linked relations data
    *
    * @param void
    * @return array
    */
    public function getRelationLinks()
    {
      $relations = array();
      foreach ($this->mcp3Project->getRelationReferences() as $relationReference)
      {
        $relations[$relationReference->getUuid()] = $relationReference->getRole();
      }

      return $relations;
    }

    /**
    * Get the project tags
    *
    * @param void
    * @return array
    */
    public function getTags()
    {
      return $this->mcp3Project->getTags();
    }

    /**
    * @desc Get the categories to link project to
    * Should be extended by implementing class
    *
    * @param void
    * @return array
    */
    abstract public function getCategories();

    /**
     * @desc Method getThemeCategories Allow the theme to influence creation of extra categories on projects
     *
     * @param {Yog3McpXmlProjectAbstract} $mcp3Project
     * @param {Array} $categories
     * @return {Void}
     */
    protected function getThemeCategories(Yog3McpXmlProjectAbstract $mcp3Project, &$categories)
    {
      $templateDir = get_template_directory();

      // Include the Theme's function directory
      if (file_exists($templateDir . '/functions.php'))
        require_once($templateDir . '/functions.php');

      // Execute the hook if provided in the functions.php
      if (function_exists('yog_plugin_get_categories'))
      {
        $extendCategories = yog_plugin_get_categories($mcp3Project);

        if (is_array($extendCategories))
          $categories       = array_merge($categories, $extendCategories);

      }
    }

    /**
    * @desc get the text content
    *
    * @param void
    * @return string
    */
    protected function getContent()
    {
      $texts          = $this->mcp3Project->getTexts();
      $extraTextTypes = array('textextra1', 'textextra2', 'textextra3', 'textextra4', 'textextra5');
      $skipExtraTexts = (get_option('yog_noextratexts') == '1');
      $content        = '';
      $intro          = '';

      // Start with intro text
      if (array_key_exists('intro', $texts))
        $intro = '<div class="yogcontent intro"><p>' .nl2br($texts['intro']) .'</p></div>';

      foreach ($texts as $type => $text)
      {
        if ($type != 'intro' && (!$skipExtraTexts || !in_array($type, $extraTextTypes)))
          $content .= '<div class="yogcontent ' . $type . '"><p>' .nl2br($text) .'</p></div>';
      }

      return $intro . ((!empty($intro) && !empty($content)) ? '<!--more-->' : '') . $content;
    }

    /**
    * @desc Translate price condition
    *
    * @param string $condition
    * @return string
    */
    protected function translatePriceCondition($condition)
    {
      return str_replace( array('kosten koper', 'vrij op naam', 'per maand', 'per jaar', 'per vierkante meter per jaar'),
                          array('k.k.', 'v.o.n.', 'p.m.', 'p.j.', 'per m2/jaar'),
                          $condition);
    }

    /**
    * @desc Translate bouwjaar periode
    *
    * @param string $periode
    * @return string
    */
    protected function translateBouwjaarPeriode($periode)
    {
      return str_replace(array('2001-', '-1905'), array('na 2001', 'voor 1906'), $periode);
    }
  }