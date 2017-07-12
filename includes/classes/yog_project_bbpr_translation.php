<?php
  /**
  * @desc YogProjectBBprTranslation
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogProjectBBprTranslation extends YogProjectTranslationAbstract
  {
    /**
    * @desc Get post type
    *
    * @param void
    * @return string
    */
    public function getPostType()
    {
      return POST_TYPE_BBPR;
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
        'Land'                        => $this->mcp3Project->getStringByPath('//project:General/project:Location/project:Country'),
        'Provincie'                   => $this->mcp3Project->getStringByPath('//project:General/project:Location/project:State'),
        'Gemeente'                    => $this->mcp3Project->getStringByPath('//project:General/project:Location/project:Municipality'),
        'Plaats'                      => $this->mcp3Project->getStringByPath('//project:General/project:Location/project:City'),
        'Wijk'                        => $this->mcp3Project->getStringByPath('//project:General/project:Location/project:Area'),
        'Buurt'                       => $this->mcp3Project->getStringByPath('//project:General/project:Location/project:Neighbourhood'),
        'Straat'                      => $this->mcp3Project->getStringByPath('//project:General/project:Location/project:Street'),
        'Postcode'                    => $this->mcp3Project->getStringByPath('//project:General/project:Location/project:Zipcode'),
        'Longitude'                   => $this->mcp3Project->getStringByPath('//project:General/project:GeoCode/project:Longitude'),
        'Latitude'                    => $this->mcp3Project->getStringByPath('//project:General/project:GeoCode/project:Latitude'),
        'KoopPrijsMin'                => $this->mcp3Project->getIntByPath('//project:Details/project:Koop/project:Prijs/project:Min'),
        'KoopPrijsMax'                => $this->mcp3Project->getIntByPath('//project:Details/project:Koop/project:Prijs/project:Max'),
        'HuurPrijsMin'                => $this->mcp3Project->getIntByPath('//project:Details/project:Huur/project:Prijs/project:Min'),
        'HuurPrijsMax'                => $this->mcp3Project->getIntByPath('//project:Details/project:Huur/project:Prijs/project:Max'),
        'HuurPrijsConditie'           => $this->translatePriceCondition($this->mcp3Project->getStringByPath('//project:Details/project:Huur/project:PrijsConditie')),
        'PerceelOppervlakteMin'       => $this->mcp3Project->getIntByPath('//project:Details/project:PerceelOppervlakte/project:Min'),
        'PerceelOppervlakteMax'       => $this->mcp3Project->getIntByPath('//project:Details/project:PerceelOppervlakte/project:Max'),
        'WoonOppervlakteMin'          => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:WoonOppervlakte/project:Min'),
        'WoonOppervlakteMax'          => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:WoonOppervlakte/project:Max'),
        'InhoudMin'                   => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:Inhoud/project:Min'),
        'InhoudMax'                   => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:Inhoud/project:Max')
      );

      // Housenumber
      if ($this->mcp3Project->hasLocation())
      {
        $address                  = $this->mcp3Project->getLocation();
        $data['Huisnummer']       = $address->getHouseNumber() . $address->getHouseNumberAddition();
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
      $price = $this->mcp3Project->getStringByPath('//project:Details/project:Koop/project:Prijs/project:Min');
      if (!empty($price))
        return $price;

      $price = $this->mcp3Project->getStringByPath('//project:Details/project:Huur/project:Prijs/project:Min');
      if (!empty($price))
        return $price;
      
      $price = $this->mcp3Project->getStringByPath('//project:Details/project:Koop/project:Prijs/project:Max');
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
	    $categories = array('complexen', 'complex');

      // Verkoop
      $min = $this->mcp3Project->getIntByPath('//project:Details/project:Koop/project:Prijs/project:Min');
      $max = $this->mcp3Project->getIntByPath('//project:Details/project:Koop/project:Prijs/project:Max');
      if (!empty($min) || !empty($max))
        $categories[] = 'complex-verkoop';

      // Verhuur
      $min = $this->mcp3Project->getIntByPath('//project:Details/project:Huur/project:Prijs/project:Min');
      $max = $this->mcp3Project->getIntByPath('//project:Details/project:Huur/project:Prijs/project:Max');
      if (!empty($min) || !empty($max))
        $categories[] = 'complex-verhuur';

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
      return 'beschikbaar';
    }
  }