<?php
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_exception.php');

  /**
  * @desc YogSystemLinkManager
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogSystemLinkManager
  {
    /**
    * @desc Retrieve YogSystemLink by request
    *
    * @param array $request
    */
    public function retrieveByRequest($request)
    {
      if (!is_array($request))
        throw new YogException(__METHOD__ . '; Invalid request, must be an array', YogException::WRONG_REQUEST);

      if (!isset($request['action']) || !is_string($request['action']) || strlen(trim($request['action'])) == 0)
        throw new YogException(__METHOD__ . '; Invalid action, must be a non empty string', YogException::WRONG_REQUEST);

      if (!isset($request['uuid']) || !is_string($request['uuid']) || strlen(trim($request['uuid'])) == 0)
        throw new YogException(__METHOD__ . '; Invalid uuid, must be a non empty string', YogException::WRONG_REQUEST);

      if (!isset($request['signature']) || !is_string($request['signature']) || strlen(trim($request['signature'])) == 0)
        throw new YogException(__METHOD__ . '; Invalid signature, must be a non empty string', YogException::WRONG_REQUEST);

      // Determine payload


      // Retrieve all linked systems
      $systemLinks = get_option('yog_koppelingen');

      if (!is_array($systemLinks))
        throw new YogException(__METHOD__ . '; No system links found', YogException::NO_SYSTEM_LINK);

      // Determine requested system link
		  foreach ($systemLinks as $systemLink)
      {
        if ($systemLink['UUID'] == YogSystemLink::EMPTY_UUID)
          $systemLink['UUID'] = $request['uuid'];

        $systemLinkSignature = md5('action=' . $request['action'] . 'uuid=' . $systemLink['UUID'] . $systemLink['activatiecode']);

			  if ($systemLinkSignature == $request['signature'])
          return YogSystemLink::create($systemLink);
		  }

      throw new YogException(__METHOD__ . '; No system linked matches to provided request', YogException::WRONG_REQUEST);
    }

    /**
    * @desc Retrieve system link by activation code
    *
    * @param string $activationCode
    * @return YogSystemLink
    */
    public function retrieveByActivationCode($activationCode)
    {
      $systemLinks  = $this->retrieveAll();
      $systemLink   = null;

			foreach ($systemLinks as $curSystemLink)
      {
        if ($curSystemLink->getActivationCode() == $activationCode)
					$systemLink = $curSystemLink;
			}

      if (is_null($systemLink))
        YogException(__METHOD__ . '; No system link found', YogException::GLOBAL_ERROR);

      return $systemLink;
    }

    /**
    * @desc Retrieve all system links
    *
    * @param void
    * @return array
    */
    public function retrieveAll()
    {
      // Retrieve stored system links
      $storedSystemLinks = get_option('yog_koppelingen');
      if (!is_array($storedSystemLinks))
        $storedSystemLinks = array();

      // Translate to objects
      $systemLinks = array();
      foreach ($storedSystemLinks as $storedSystemLink)
      {
        $systemLinks[] = YogSystemLink::create($storedSystemLink);
      }

      return $systemLinks;
    }

    /**
    * @desc Store system link
    *
    * @param YogSystemLink $systemLink
    * @return void
    */
    public function store(YogSystemLink $systemLink)
    {
      // Retrieve existing system links
	    $existingSystemLinks = get_option('yog_koppelingen');
      if (!is_array($existingSystemLinks))
        $existingSystemLinks = array();

      // Make sure provided system link is not on system links array
      $systemlinks = array();

		  foreach ($existingSystemLinks as $existingSystemLink)
      {
			  if ($existingSystemLink['activatiecode'] != $systemLink->getActivationCode())
          $systemlinks[] = $existingSystemLink;
		  }

      // Add provided system link to array
      $systemlinks[] = array('naam'           => $systemLink->getName(),
                              'status'        => $systemLink->getState(),
                              'activatiecode' => $systemLink->getActivationCode(),
                              'UUID'          => $systemLink->getCollectionUuid());

      // Store
	    update_option('yog_koppelingen', $systemlinks);
    }

    /**
    * @desc Activate system link
    *
    * @param YogSystemLink $systemLink
    * @return void
    */
    public function activate(YogSystemLink $systemLink)
    {
      if ($systemLink->getCollectionUuid() == YogSystemLink::EMPTY_UUID)
        throw new YogException(__METHOD__ . '; Collection uuid not set');

      $systemLink->setState(YogSystemLink::STATE_ACTIVE);

      $this->store($systemLink);
    }

    /**
    * @desc Remove a system link
    *
    * @param YogSystemLink $systemLink
    * @return void
    */
    public function remove(YogSystemLink $systemLink)
    {
		  $existingSystemLinks  = get_option('yog_koppelingen');
		  $systemLinks          = array();

		  if (is_array($existingSystemLinks))
      {
			  foreach ($existingSystemLinks as $existingSystemLink)
        {
				  if ($existingSystemLink['activatiecode'] != $systemLink->getActivationCode())
					  $systemLinks[] = $existingSystemLink;
			  }
		  }

		  update_option('yog_koppelingen', $systemLinks);
    }
  }

  /**
  * @desc YogSystemLink
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogSystemLink
  {
    const STATE_ACTIVE  = 'Actief';
    const EMPTY_NAME    = 'Nog niet bekend, wacht op synchronisatie';
    const EMPTY_UUID    = '-';

    private $name;
    private $state;
    private $activationCode;
    private $collectionUuid;

    /**
    * @desc Constructor
    *
    * @param string $name
    * @param string $state
    * @param string $activationCode
    * @param string $collectionUuid
    * @return YogSystemLink
    */
    public function __construct($name, $state, $activationCode, $collectionUuid)
    {
      $this->setName($name);
      $this->setState($state);
      $this->setActivationCode($activationCode);
      $this->setCollectionUuid($collectionUuid);
    }

    /**
    * @desc Create a YogSystemLink
    *
    * @param array $systemLink
    * @return YogSystemLink
    */
    static public function create($systemLink)
    {
      if (!isset($systemLink['naam']) || !is_string($systemLink['naam']) || strlen(trim($systemLink['naam'])) == 0)
        throw new YogException(__METHOD__ . '; Invalid name, must be non empty string', YogException::GLOBAL_ERROR);

      if (!isset($systemLink['status']) || !is_string($systemLink['status']) || strlen(trim($systemLink['status'])) == 0)
        throw new YogException(__METHOD__ . '; Invalid state, must be non empty string', YogException::GLOBAL_ERROR);

      if (!isset($systemLink['activatiecode']) || !is_string($systemLink['activatiecode']) || strlen(trim($systemLink['activatiecode'])) == 0)
        throw new YogException(__METHOD__ . '; Invalid activatiecode, must be non empty string', YogException::GLOBAL_ERROR);

      if (!isset($systemLink['UUID']) || !is_string($systemLink['UUID']) || strlen(trim($systemLink['UUID'])) == 0)
        throw new YogException(__METHOD__ . '; Invalid UUID, must be non empty string', YogException::GLOBAL_ERROR);

      return new self($systemLink['naam'], $systemLink['status'], $systemLink['activatiecode'], $systemLink['UUID']);
    }

    /**
    * @desc Set the name
    *
    * @param string $name
    * @return void
    */
    public function setName($name)
    {
      $this->name = $name;
    }

    /**
    * @desc Get the name
    *
    * @param void
    * @return string
    */
    public function getName()
    {
      return $this->name;
    }

    /**
    * @desc Set the state
    *
    * @param string $state
    * @return void
    */
    public function setState($state)
    {
      $this->state = $state;
    }

    /**
    * @desc Get the state
    *
    * @param void
    * @return string
    */
    public function getState()
    {
      return $this->state;
    }

    /**
    * @desc Set the activationcode
    *
    * @param string $activationCode
    * @return void
    */
    public function setActivationCode($activationCode)
    {
      $this->activationCode = $activationCode;
    }

    /**
    * @desc Get the activationcode
    *
    * @param void
    * @return string
    */
    public function getActivationCode()
    {
      return $this->activationCode;
    }

    /**
    * @desc Set the collection uuid
    *
    * @param string $uuid
    * @return void
    */
    public function setCollectionUuid($uuid)
    {
      $this->collectionUuid = $uuid;
    }

    /**
    * @desc Get the collection uuid
    *
    * @param void
    * @return string
    */
    public function getCollectionUuid()
    {
      return $this->collectionUuid;
    }
  }