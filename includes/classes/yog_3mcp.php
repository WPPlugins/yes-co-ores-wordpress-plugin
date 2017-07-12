<?php
  require_once(YOG_PLUGIN_DIR . '/includes/config/config.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_3mcp_xml.php');

  /**
  * @desc Yog3McpFeedReader
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class Yog3McpFeedReader
  {
    static private $instance;

    private $xml;
    private $mcp3Version;

    /**
    * @desc Constructor
    *
    * @param void
    * @return Yog3McpFeedReader
    */
    private function __construct()
    {

    }

    /**
    * @desc Get an instance of the feed reader
    *
    * @param void
    * @return Yog3McpFeedReader
    */
    static public function getInstance()
    {
      if (is_null(self::$instance))
        self::$instance = new self();

      return self::$instance;
    }

    /**
    * @desc Read feed
    *
    * @param string $collectionUuid
    * @return void
    * @throws YogException
    */
    public function read($collectionUuid)
    {
      $mcp3Versions           = explode(';', MCP3_VERSIONS);
      $registeredMcp3Version  = get_option('yog_3mcp_version');

      foreach ($mcp3Versions as $mcp3Version)
      {
        try
        {
          $url = $this->determine3McpUrl(sprintf(MCP3_FEED_URL, $collectionUuid, $mcp3Version, $collectionUuid));

          $this->xml = YogSimpleXMLElement::createXmlFromUrl($url);
          $this->xml->registerXPathNamespace('atom', ATOM_NAMESPACE);
          $this->xml->registerXpathNamespace('mcp', sprintf(MCP_ATOM_NAMESPACE, $mcp3Version));

          $this->mcp3Version = $mcp3Version;

          if ($registeredMcp3Version != $mcp3Version)
            update_option('yog_3mcp_version', $mcp3Version);

          break;
        }
        catch (Exception $e)
        {

        }
      }

      if (is_null($this->xml))
        throw new YogException(__METHOD__ . '; Failed to read feed');
    }

    /**
    * @desc Get project entity links
    *
    * @param void
    * @return array
    */
    public function getProjectEntityLinks()
    {
      $nodes        = $this->xml->xpath("//atom:entry[atom:category/@term = 'project']");
      $entityLinks  = array('BBpr' => array(), 'BBty' => array(),
                            'BBvk' => array(), 'BBvh' => array(), 'LIvk' => array(),
                            'BOvk' => array(), 'BOvh' => array(),
                            'NBpr' => array(), 'NBty' => array(), 'NBbn' => array(),
                            'NBvk' => array(), 'NBvh' => array());

      if ($nodes !== false && count($nodes) > 0)
      {
        foreach ($nodes as $node)
        {
          $uuid     = $this->translateUuid((string) $node->id);
          $url      = $this->determine3McpUrl((string) $node->link['href']);
          $doc      = (string) $node->published;
          $dlm      = (string) $node->updated;
          $scenario = $node->xpath('mcp:projectScenario');
          $scenario = (string) $scenario[0];

          if (in_array($scenario, array('BBvk', 'BBvh', 'NBvk', 'NBvh', 'LIvk', 'BOvk', 'BOvh', 'NBpr', 'NBty', 'NBbn', 'BBpr', 'BBty')))
            $entityLinks[$scenario][$uuid] = new Yog3McpProjectLink($uuid, $url, $doc, $dlm, $scenario);
        }
      }

      return $entityLinks;
    }

    /**
    * @desc Get relation entity links
    *
    * @param void
    * @return array
    */
    public function getRelationEntityLinks()
    {
      $nodes        = $this->xml->xpath("//atom:entry[atom:category/@term = 'relation']");
      $entityLinks  = array();

      if ($nodes !== false && count($nodes) > 0)
      {
        foreach ($nodes as $node)
        {
          $uuid     = $this->translateUuid((string) $node->id);
          $url      = $this->determine3McpUrl((string) $node->link['href']);
          $doc      = (string) $node->published;
          $dlm      = (string) $node->updated;

          $entityLinks[$uuid] = new Yog3McpRelationLink($uuid, $url, $doc, $dlm);
        }
      }

      return $entityLinks;
    }

    /**
    * @desc Get media link by uuid
    *
    * @param string $uuid
    * @return Yog3McpMediaLink
    */
    public function getMediaLinkByUuid($uuid, $preferedMediaSize = 'medium')
    {
      // Search node
      $nodes        = $this->xml->xpath("//atom:entry[atom:category/@term = 'media' and atom:id = 'urn:uuid:" . $uuid . "']");

      if ($nodes === false || count($nodes) == 0)
        throw new YogException(__METHOD__ . '; Image with uuid (' . $uuid . ') not found', YogException::GLOBAL_ERROR);

      if (count($nodes) > 1)
        throw new YogException(__METHOD__ . '; Multiple images with uuid (' . $uuid . ') not found', YogException::GLOBAL_ERROR);

      $node     = array_shift($nodes);
      
      // Determine media url
      $mediaUrl = null;
      
      if (count($node->link) > 1)
      {
        foreach ($node->link as $link)
        {
          $mcpAttributes = $link->attributes(sprintf(MCP_ATOM_NAMESPACE, $this->mcp3Version));
          if (isset($mcpAttributes['mediaFormat']) && (string) $mcpAttributes['mediaFormat'] === $preferedMediaSize)
            $mediaUrl = (string) $link['href'];
        }
      }
      
      if (is_null($mediaUrl))
        $mediaUrl = (string) $node->link['href'];

      // Variables
      $url      = $this->determine3McpUrl($mediaUrl);
      $doc      = (string) $node->published;
      $dlm      = (string) $node->updated;
      $mimeType = (string) $node->link['type'];

      return new Yog3McpMediaLink($uuid, $url, $doc, $dlm, $mimeType);
    }

    /**
    * @desc Get dossier link by uuid
    *
    * @param string $uuid
    * @return Yog3McpMediaLink
    */
    public function getDossierLinkByUuid($uuid)
    {
      // Search node
      $nodes        = $this->xml->xpath("//atom:entry[(atom:category/@term = 'document' or atom:category/@term = 'dossier') and atom:id = 'urn:uuid:" . $uuid . "']");

      if ($nodes === false || count($nodes) == 0)
        throw new YogException(__METHOD__ . '; Document with uuid (' . $uuid . ') not found', YogException::GLOBAL_ERROR);

      if (count($nodes) > 1)
        throw new YogException(__METHOD__ . '; Multiple documents with uuid (' . $uuid . ') not found', YogException::GLOBAL_ERROR);

      $node     = array_shift($nodes);

      // Variables
      //$uuid     = $this->translateUuid((string) $node->id);
      $url      = $this->determine3McpUrl((string) $node->link['href']);
      $doc      = (string) $node->published;
      $dlm      = (string) $node->updated;
      $mimeType = (string) $node->link['type'];
      $category = (string) $node->category['term'];

      return new Yog3McpDossierLink($uuid, $url, $doc, $dlm, $mimeType, $category);
    }

    /**
    * @desc Retrieve project by link
    *
    * @param Yog3McpProjectLink $link
    * @return Yog3McpXmlProjectAbstract
    * @throws YogException
    */
    public function retrieveProjectByLink(Yog3McpProjectLink $link)
    {
      $namespace = sprintf(PROJECT_NAMESPACE, $this->mcp3Version);
      return Yog3McpXmlProjectAbstract::create(YogSimpleXMLElement::createXmlFromUrl($link->getUrl()), $namespace);
    }

    /**
    * @desc Retrieve relation by link
    *
    * @param Yog3McpRelationLink $link
    * @return Yog3McpXmlRelationAbstract
    * @throws YogException
    */
    public function retrieveRelationByLink(Yog3McpRelationLink $link)
    {
      $namespace = sprintf(RELATION_NAMESPACE, $this->mcp3Version);
      return Yog3McpXmlRelationAbstract::create(YogSimpleXMLElement::createXmlFromUrl($link->getUrl()), $namespace);
    }

    /**
    * @desc Translate id to uuid string
    *
    * @param string $uuid
    * @return string
    */
    private function translateUuid($uuid)
    {
      return str_replace('urn:uuid:','', $uuid);
    }

    /**
    * @desc Determine 3MCP url
    *
    * @param string $url
    * @return string
    */
    static protected function determine3McpUrl($url)
    {
      // Add authentication to url
      if (defined('MCP3_USERNAME') && defined('MCP3_PASSWORD') && MCP3_USERNAME != '' && MCP3_PASSWORD != '')
      {
        $protocol = substr($url, 0, strpos($url, '://')) . '://';

	      $url = str_replace($protocol,'',$url);
	      $url = $protocol . MCP3_USERNAME .':' . MCP3_PASSWORD .'@' .$url;
      }

      // Rewrite staging url's
      /*
      if (strpos($url, 'https://webservice.staging.yes-co.com/3mcp/1.4/') !== false)
      {
        $filename = basename($url);
        $addition = substr($filename, 0, 2) . '/' . substr($filename, 2, 2) . '/' . $filename;
        $url      = str_replace(array('https://', $filename), array('http://', $addition), $url);
      }
      */

      return $url;
    }
  }

  /**
  * @desc Yog3McpEntityLink
  */
  class Yog3McpEntityLink
  {
    private $uuid;
    private $url;
    private $doc;
    private $dlm;

    /**
    * @desc Constructor
    *
    * @param string $uuid
    * @param string $url
    * @param string $doc
    * @param string $dlm
    * @return Yog3McpEntityLink
    */
    public function __construct($uuid, $url, $doc, $dlm)
    {
      $this->setUuid($uuid);
      $this->setUrl($url);
      $this->setDoc($doc);
      $this->setDlm($dlm);
    }

    /**
    * @desc Set the uuid
    *
    * @param string $uuid
    * @return void
    */
    public function setUuid($uuid)
    {
      $this->uuid = $uuid;
    }

    /**
    * @desc Get the uuid
    *
    * @param void
    * @return string
    */
    public function getUuid()
    {
      return $this->uuid;
    }

    /**
    * @desc Set the url
    *
    * @param string $url
    * @return void
    */
    public function setUrl($url)
    {
      $this->url = $url;
    }

    /**
    * @desc Get the url
    *
    * @param void
    * @return string
    */
    public function getUrl()
    {
      return $this->url;
    }

    /**
    * @desc Set the doc
    *
    * @param string $doc
    * @return void
    */
    public function setDoc($doc)
    {
      $this->doc = $doc;
    }

    /**
    * @desc Get the doc
    *
    * @param void
    * @return string
    */
    public function getDoc()
    {
      return $this->doc;
    }

    /**
    * @desc Set the dlm
    *
    * @param string $dlm
    * @return void
    */
    public function setDlm($dlm)
    {
      $this->dlm = $dlm;
    }

    /**
    * @desc Get the dlm
    *
    * @param void
    * @return string
    */
    public function getDlm()
    {
      return $this->dlm;
    }
  }

  /**
  * @desc Yog3McpProjectLink
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class Yog3McpProjectLink extends Yog3McpEntityLink
  {
    private $scenario;

    /**
    * @desc Constructor
    *
    * @param string $uuid
    * @param string $url
    * @param string $doc
    * @param string $dlm
    * @param string $scenario
    * @return Yog3McpProjectLink
    */
    public function __construct($uuid, $url, $doc, $dlm, $scenario)
    {
      parent::__construct($uuid, $url, $doc, $dlm);
      $this->setScenario($scenario);
    }

    /**
    * @desc Set scenario
    *
    * @param string $scenario
    * @return void
    */
    public function setScenario($scenario)
    {
      $this->scenario = $scenario;
    }

    /**
    * @desc Get the scenario
    *
    * @param void
    * @return string
    */
    public function getScenario()
    {
      return $this->scenario;
    }
  }

  /**
  * @desc Yog3McpRelationLink
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class Yog3McpRelationLink extends Yog3McpEntityLink
  {

  }

  /**
  * @desc Yog3McpMediaLink
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class Yog3McpMediaLink extends Yog3McpEntityLink
  {
    private $mimeType;

    /**
    * @desc Constructor
    *
    * @param string $uuid
    * @param string $url
    * @param string $doc
    * @param string $dlm
    * @param string $mimeType
    * @return Yog3McpMediaLink
    */
    public function __construct($uuid, $url, $doc, $dlm, $mimeType)
    {
      parent::__construct($uuid, $url, $doc, $dlm);
      $this->setMimeType($mimeType);
    }

    /**
    * @desc Set MimeType
    *
    * @param string $mimeType
    * @return void
    */
    public function setMimeType($mimeType)
    {
      $this->mimeType = $mimeType;
    }

    /**
    * @desc Get the MimeType
    *
    * @param void
    * @return string
    */
    public function getMimeType()
    {
      return $this->mimeType;
    }
  }

  /**
  * @desc Yog3McpDossierLink
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class Yog3McpDossierLink extends Yog3McpEntityLink
  {
    private $mimeType;
    private $category;

    /**
    * @desc Constructor
    *
    * @param string $uuid
    * @param string $url
    * @param string $doc
    * @param string $dlm
    * @param string $mimeType
    * @param string $entryType
    * @return Yog3McpMediaLink
    */
    public function __construct($uuid, $url, $doc, $dlm, $mimeType, $category)
    {
      parent::__construct($uuid, $url, $doc, $dlm);
      $this->setMimeType($mimeType);
      $this->setCategory($category);
    }

    /**
    * @desc Set MimeType
    *
    * @param string $mimeType
    * @return void
    */
    public function setMimeType($mimeType)
    {
      $this->mimeType = $mimeType;
    }

    /**
    * @desc Get the MimeType
    *
    * @param void
    * @return string
    */
    public function getMimeType()
    {
      return $this->mimeType;
    }

    /**
     * Set the category
     *
     * @param string $category (document or dossier)
     * @return void
     */
    public function setCategory($category)
    {
      $this->category = $category;
    }

    /**
     * Get the category
     *
     * @param void
     * @return string
     */
    public function getCategory()
    {
      return $this->category;
    }
  }

  /**
  * @desc YogSimpleXMLElement
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogSimpleXMLElement extends SimpleXMLElement
  {
    /**
    * @desc Create from URL
    *
    * @param string $url
    * @return YogSimpleXMLElement
    * @throws YogException
    */
    static public function createXmlFromUrl($url)
    {
      return new self(self::retrieveContent($url));
    }

    /**
    * @desc Retrieve content by url
    *
    * @param string $url
    * @return string
    * @throws YogException
    */
    static protected function retrieveContent($url)
    {
      $content = YogHttpManager::retrieveContent($url, true);

	    if ($content === false)
        throw new YogException(__METHOD__ . '; Unable to open XML file (' . $url . ')', YogException::GLOBAL_ERROR);

      return $content;
    }
  }