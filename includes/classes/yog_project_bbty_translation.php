<?php
  /**
  * @desc YogProjectBBtyTranslation
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogProjectBBtyTranslation extends YogProjectTranslationAbstract
  {
    /**
    * @desc Get post type
    *
    * @param void
    * @return string
    */
    public function getPostType()
    {
      return POST_TYPE_BBTY;
    }

    /**
    * @desc Get the title
    *
    * @param void
    * @return string
    */
    public function determineTitle()
    {
      $title    = $this->mcp3Project->getName();

      if (empty($title))
        $title = $this->mcp3Project->getStringByPath('//project:General/project:Name');

      return $title;
    }

    /**
    * @desc Check if a parent uuid is set
    *
    * @param void
    * @return bool
    */
    public function hasParentUuid()
    {
      return true;
    }

    /**
    * @desc Get the parent uuid
    *
    * @param void
    * @return string
    * @throws Exception
    */
    public function getParentUuid()
    {
      return $this->mcp3Project->getBBprUuid();
    }

    /**
    * @desc Get meta data
    *
    * @param void
    * @return array
    */
    public function getMetaData()
    {
      // General meta data
      $data = array(
        'uuid'                        => $this->mcp3Project->getStringByPath('/project:Project/@uuid'),
        'dlm'                         => $this->translateDate($this->mcp3Link->getDlm()),
        'scenario'                    => $this->mcp3Project->getScenario(),
        'ApiKey'                      => $this->mcp3Project->getStringByPath('/project:Project/project:YProjectNumber'),
        'Status'                      => $this->determineState(),
        'Naam'                        => $this->mcp3Project->getStringByPath('//project:General/project:Name'),
        'KoopPrijsMin'                => $this->mcp3Project->getIntByPath('//project:Details/project:Koop/project:PrijsMin'),
        'KoopPrijsMax'                => $this->mcp3Project->getIntByPath('//project:Details/project:Koop/project:PrijsMax'),
        'HuurPrijsMin'                => $this->mcp3Project->getIntByPath('//project:Details/project:Huur/project:Prijs/project:Min'),
        'HuurPrijsMax'                => $this->mcp3Project->getIntByPath('//project:Details/project:Huur/project:Prijs/project:Max'),
        'HuurPrijsConditie'           => $this->translatePriceCondition($this->mcp3Project->getStringByPath('//project:Details/project:Huur/project:PrijsConditie')),
        'PerceelOppervlakteMin'       => $this->mcp3Project->getIntByPath('//project:Details/project:PerceelOppervlakte/project:Min'),
        'PerceelOppervlakteMax'       => $this->mcp3Project->getIntByPath('//project:Details/project:PerceelOppervlakte/project:Max'),
        'WoonOppervlakteMin'          => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:WoonOppervlakte/project:Min'),
        'WoonOppervlakteMax'          => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:WoonOppervlakte/project:Max'),
        'InhoudMin'                   => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:Inhoud/project:Min'),
        'InhoudMax'                   => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:Inhoud/project:Max'),
        'PermanenteBewoning'          => $this->translateBool($this->mcp3Project->getBoolByPath('//project:Details/project:Woonruimte/project:Bestemming/project:PermanenteBewoning')),
        'Recreatiewoning'             => $this->translateBool($this->mcp3Project->getBoolByPath('//project:Details/project:Woonruimte/project:Bestemming/project:Recreatiewoning')),
        'Aantalkamers'                => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:Verdieping/project:AantalKamers'),
        'GarageType'                  => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Garage/project:Type'),
        'GarageCapaciteit'            => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Garage/project:Capaciteit'),
        'GarageVoorzieningen'         => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Garage/project:Voorzieningen/project:Voorziening/@naam'),
        'GarageIsolatievormen'        => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Garage/project:Isolatievormen/project:Isolatievorm/@naam'),
        'TuinType'                    => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Tuin/project:Type'),
        'TuinTotaleOppervlakte'       => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:Tuin/project:TotaleOppervlakte'),
        'HoofdTuinType'               => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Tuin/project:HoofdtuinType'),
        'HoofdTuinDiepte'             => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:Tuin/project:Diepte'),
        'HoofdTuinBreedte'            => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:Tuin/project:Breedte'),
        'HoofdTuinTotaleOppervlakte'  => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:Tuin/project:Oppervlakte'),
        'TuinLigging'                 => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Tuin/project:Ligging'),
        'HoofdTuinAchterom'           => $this->translateBool($this->mcp3Project->getBoolByPath('//project:Details/project:Woonruimte/project:Tuin/project:Achterom')),
        'BergingType'                 => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:SchuurBerging/project:Soort'),
        'BergingVoorzieningen'        => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:SchuurBerging/project:Voorzieningen/project:Voorziening/@naam'),
        'BergingIsolatievormen'       => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:SchuurBerging/project:Isolatievormen/project:Isolatievorm/@naam'),
        'Verwarming'                  => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Installatie/project:Verwarming/project:Type'),
        'WarmWater'                   => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Installatie/project:WarmWater/project:Type'),
        'CvKetel'                     => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Installatie/project:CvKetel/project:Type'),
        'CvKetelBouwjaar'             => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Installatie/project:CvKetel/project:Bouwjaar'),
        'CvKetelBrandstof'            => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Installatie/project:CvKetel/project:GasOlie'),
        'CvKetelEigendom'             => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Installatie/project:CvKetel/project:Eigendom'),
        'CvCombiketel'                => $this->translateBool($this->mcp3Project->getBoolByPath('//project:Details/project:Woonruimte/project:Installatie/project:CvKetel/project:Combiketel')),
        'Dak'                         => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Diversen/project:Dak'),
        'DakMaterialen'               => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Diversen/project:DakMaterialen/project:DakMateriaal/@naam')
      );

      // Type
		  $type                     = ($this->mcp3Project->hasSubType()) ? $this->mcp3Project->getSubType() : $this->mcp3Project->getType();
	    $data['Type']             = $type;

      // Subtype specific
      switch (strtolower($this->mcp3Project->getSubType()))
      {
        case 'woonhuis':
  				$data["SoortWoning"]    = $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Woonhuis/project:SoortWoning');
				  $data["TypeWoning"]     = $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Woonhuis/project:TypeWoning');
				  $data["KenmerkWoning"]  = $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Woonhuis/project:Kenmerk');
          break;
        case 'appartement':
				  $data["SoortWoning"]    = $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Appartement/project:SoortAppartement');
				  $data["KenmerkWoning"]  = $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Appartement/project:Kenmerk');
          break;
      }

      return $data;
    }
    
    /**
     * Determine price to sort project by
     * 
     * @param void
     * @return mixed
     */
    public function determineSortPrice()
    {
      $price = $this->mcp3Project->getStringByPath('//project:Details/project:Koop/project:PrijsMin');
      if (!empty($price))
        return $price;

      $price = $this->mcp3Project->getStringByPath('//project:Details/project:Huur/project:Prijs/project:Min');
      if (!empty($price))
        return $price;
      
      $price = $this->mcp3Project->getStringByPath('//project:Details/project:Koop/project:PrijsMax');
      if (!empty($price))
        return $price;
      
      $price = $this->mcp3Project->getStringByPath('//project:Details/project:Huur/project:Prijs/project:Max');
      if (!empty($price))
        return $price;
      
      return 0;
    }

    /**
    * @desc Get the categories to link project to
    *
    * @param void
    * @return array
    */
    public function getCategories()
    {
	    $categories = array('complexen', 'complex-type');

      // Verkoop
      $min = $this->mcp3Project->getIntByPath('//project:Details/project:Koop/project:PrijsMin');
      $max = $this->mcp3Project->getIntByPath('//project:Details/project:Koop/project:PrijsMax');
      if (!empty($min) || !empty($max))
        $categories[] = 'complex-type-verkoop';

      // Verhuur
      $min = $this->mcp3Project->getIntByPath('//project:Details/project:Huur/project:Prijs/project:Min');
      $max = $this->mcp3Project->getIntByPath('//project:Details/project:Huur/project:Prijs/project:Max');
      if (!empty($min) || !empty($max))
        $categories[] = 'complex-type-verhuur';

      // Allow the theme to add custom categories
      $this->getThemeCategories($this->mcp3Project, $categories);

      return $categories;
    }

    /**
    * @desc Determine project state
    *
    * @param void
    * @return string
    */
    private function determineState()
    {
	    $state = $this->mcp3Project->getStringByPath('//project:General/project:ObjectStatus');

      return $state;
    }
  }