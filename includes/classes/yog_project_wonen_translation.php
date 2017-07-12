<?php
  /**
  * @desc YogProjectWonenTranslation
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogProjectWonenTranslation extends YogProjectTranslationAbstract
  {
    const POST_TYPE = 'huis';

    /**
    * @desc Get post type
    *
    * @param void
    * @return string
    */
    public function getPostType()
    {
      return POST_TYPE_WONEN;
    }

    /**
    * @desc Get base name
    *
    * @param void
    * @return string
    */
    public function getBaseName()
    {
      return plugin_basename(__FILE__);
    }

    /**
    * @desc Check if a parent uuid is set
    *
    * @param void
    * @return bool
    */
    public function hasParentUuid()
    {
      return $this->mcp3Project->hasParentUuid();
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
      if (!$this->hasParentUuid())
        throw new Exception(__METHOD__ . '; Object does not have a parent object');

      return $this->mcp3Project->getParentUuid();
    }

    /**
    * @desc Get the title
    *
    * @param void
    * @return string
    */
    public function determineTitle()
    {
      if ($this->mcp3Project->hasAddress())
      {
        $address  = $this->mcp3Project->getAddress();
        $title    = $address->getStreet() . ' ' . $address->getHouseNumber() . $address->getHouseNumberAddition() . ' ' . $address->getCity();
      }
      else
      {
        $title    = $this->mcp3Project->getName();
      }

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
      $data = $this->getGeneralMetaData();

      // Type specific meta data
      switch (strtolower($this->mcp3Project->getType()))
      {
        case 'woonruimte':
          $data = array_merge($data, $this->getWoonruimteMetaData());
          break;
        case 'bouwgrond':
          $data = array_merge($data, $this->getBouwgrondMetaData());
          break;
        case 'parkeergelegenheid':
          $data = array_merge($data, $this->getParkeergelegenheidMetaData());
          break;
        case 'berging':
          $data = array_merge($data, $this->getBergingMetaData());
          break;
        case 'standplaats':
          $data = array_merge($data, $this->getStandplaatsMetaData());
          break;
        case 'ligplaats':
          $data = array_merge($data, $this->getLigplaatsMetaData());
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
      $price = $this->mcp3Project->getStringByPath('//project:Details/project:Koop/project:Prijs');
      if (!empty($price))
        return $price;

      $price = $this->mcp3Project->getStringByPath('//project:Details/project:Huur/project:Prijs');
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
	    $categories = array('consument');

		  if (in_array($this->mcp3Project->getScenario(), array('BBvk', 'BBvh', 'LIvk')))
			  $categories[] = 'bestaand';
		  elseif (in_array($this->mcp3Project->getScenario(), array('NBvk', 'NBvh')))
			  $categories[] = 'nieuwbouw';

      switch (strtolower($this->mcp3Project->getType()))
      {
        // Woonruimte
        case 'woonruimte':
          $categories[] = 'woonruimte';
          $categories[] = strtolower($this->mcp3Project->getSubType());

          // Check for open house
          $openHouseStart = $this->mcp3Project->getStringByPath('//project:Details/project:OpenHuis/project:Van');
          $openHouseEnd   = $this->mcp3Project->getStringByPath('//project:Details/project:OpenHuis/project:Tot');

          if ((!empty($openHouseStart) && strtotime($openHouseStart) > time()) || (!empty($openHouseEnd) || strtotime($openHouseEnd) > time()))
            $categories[] = 'open-huis';

          break;
        // Other
        default:
          $categories[] = strtolower($this->mcp3Project->getType());
          break;
      }

      // Verkoop
      $koopPrijs = $this->mcp3Project->getStringByPath('//project:Details/project:Koop/project:Prijs');
      if (!empty($koopPrijs))
        $categories[] = 'verkoop';

      // Verhuur
      $koopPrijs = $this->mcp3Project->getStringByPath('//project:Details/project:Huur/project:Prijs');
      if (!empty($koopPrijs))
        $categories[] = 'verhuur';

		  // State
      if (in_array(strtolower($this->determineState()), array('verkocht', 'verhuurd')))
			  $categories[] = 'verkochtverhuurd';

      // Allow the theme to add custom categories
      $this->getThemeCategories($this->mcp3Project, $categories);

      return $categories;
    }

    /**
    * @desc General meta data
    *
    * @param void
    * @return array
    */
    protected function getGeneralMetaData()
    {
      $data = array(
        'uuid'                  => $this->mcp3Project->getStringByPath('/project:Project/@uuid'),
        'dlm'                   => $this->translateDate($this->mcp3Link->getDlm()),
        'scenario'              => $this->mcp3Project->getScenario(),
        'ApiKey'                => $this->mcp3Project->getStringByPath('/project:Project/project:YProjectNumber'),
        'Status'                => $this->determineState(),
        'Naam'                  => $this->mcp3Project->getStringByPath('//project:General/project:Name'),
        'Land'                  => $this->mcp3Project->getStringByPath('//project:General/project:Address/project:Country'),
        'Provincie'             => $this->mcp3Project->getStringByPath('//project:General/project:Address/project:State'),
        'Gemeente'              => $this->mcp3Project->getStringByPath('//project:General/project:Address/project:Municipality'),
        'Plaats'                => $this->mcp3Project->getStringByPath('//project:General/project:Address/project:City'),
        'Wijk'                  => $this->mcp3Project->getStringByPath('//project:General/project:Address/project:Area'),
        'Buurt'                 => $this->mcp3Project->getStringByPath('//project:General/project:Address/project:Neighbourhood'),
        'Straat'                => $this->mcp3Project->getStringByPath('//project:General/project:Address/project:Street'),
        'Postcode'              => $this->mcp3Project->getStringByPath('//project:General/project:Address/project:Zipcode'),
        'Longitude'             => $this->mcp3Project->getStringByPath('//project:General/project:GeoCode/project:Longitude'),
        'Latitude'              => $this->mcp3Project->getStringByPath('//project:General/project:GeoCode/project:Latitude'),
        'DatumVoorbehoudTot'    => $this->mcp3Project->getStringByPath('//project:General/project:Voorbehoud'),
        'KoopPrijsSoort'        => ucfirst($this->mcp3Project->getStringByPath('//project:Details/project:Koop/project:PrijsSoort')),
        'KoopPrijs'             => $this->mcp3Project->getStringByPath('//project:Details/project:Koop/project:Prijs'),
        'KoopPrijsConditie'     => $this->translatePriceCondition($this->mcp3Project->getStringByPath('//project:Details/project:Koop/project:PrijsConditie')),
        'KoopPrijsVervanging'   => $this->mcp3Project->getStringByPath('//project:Details/project:Koop/project:PrijsVervanging'),
        'Veilingdatum'          => $this->mcp3Project->getStringByPath('//project:Details/project:Koop/project:Veiling/project:Datum'),
        'HuurPrijs'             => $this->mcp3Project->getIntByPath('//project:Details/project:Huur/project:Prijs'),
        'HuurPrijsConditie'     => $this->translatePriceCondition($this->mcp3Project->getStringByPath('//project:Details/project:Huur/project:PrijsConditie')),
        'OpenHuisVan'           => $this->mcp3Project->getStringByPath('//project:Details/project:OpenHuis/project:Van'),
        'OpenHuisTot'           => $this->mcp3Project->getStringByPath('//project:Details/project:OpenHuis/project:Tot'),
        'OppervlaktePerceel'    => $this->mcp3Project->getIntByPath('//project:KadastraleInformatie/project:PerceelOppervlakte'),
        'ZakelijkeRechten'      => $this->mcp3Project->getStringByPath('//project:Details/project:ZakelijkeRechten/project:ZakelijkRecht/@naam'),
        'Informatieplicht'      => $this->mcp3Project->getStringByPath('//project:Details/project:Informatie/project:Informatieplicht'),
        'OzbGebruikersDeel'     => $this->mcp3Project->getStringByPath('//project:Details/project:ZakelijkeLasten/project:OzbGebruikersDeel'),
        'OzbZakelijkeDeel'      => $this->mcp3Project->getStringByPath('//project:Details/project:ZakelijkeLasten/project:OzbZakelijkeDeel'),
        'Waterschapslasten'     => $this->mcp3Project->getStringByPath('//project:Details/project:ZakelijkeLasten/project:WaterschapsLasten'),
        'Stookkosten'           => $this->mcp3Project->getStringByPath('//project:Details/project:ZakelijkeLasten/project:Stookkosten'),
        'RuilverkavelingsRente' => $this->mcp3Project->getStringByPath('//project:Details/project:ZakelijkeLasten/project:RuilverkavelingsRente'),
        'Rioolrechten'          => $this->mcp3Project->getStringByPath('//project:Details/project:ZakelijkeLasten/project:Rioolrechten'),
        'Eigendomsoort'         => $this->mcp3Project->getStringByPath('//project:KadastraleInformatie/project:Eigendomsoort'),
        'ErfpachtPerJaar'       => $this->mcp3Project->getIntByPath('//project:KadastraleInformatie/project:Eigendom/project:ErfpachtPerJaar')
      );

      // Erfpacht duur
	    $data['ErfpachtDuur']     = $this->mcp3Project->getStringByPath('//project:KadastraleInformatie[0]/project:Eigendom/project:ErfpachtDuur');
	    if ($data['ErfpachtDuur'] != 'eeuwig')
		    $data['ErfpachtDuur'] .= ' ' . $this->mcp3Project->getStringByPath('//project:KadastraleInformatie[0]/project:Eigendom/project:EindDatum');

      // Service costs
      $serviceKosten            = $this->mcp3Project->getStringByPath('//project:Details/project:Koop/project:Servicekosten');
      $bijdrageVve              = $this->mcp3Project->getStringByPath('//project:Details/project:ZakelijkeLasten/project:BijdrageVve');
	    $data['Servicekosten']    = empty($serviceKosten) ? $bijdrageVve : $serviceKosten;

      // Housenumber
      if ($this->mcp3Project->hasAddress())
      {
        $address                  = $this->mcp3Project->getAddress();
        $data['Huisnummer']       = $address->getHouseNumber() . $address->getHouseNumberAddition();
      }

      // Type
		  $type                     = ($this->mcp3Project->hasSubType()) ? $this->mcp3Project->getSubType() : $this->mcp3Project->getType();
	    $data['Type']             = $type;

      // Aanvaarding
      $aanvaardingType          = $this->mcp3Project->getStringByPath('//project:Details/project:Aanvaarding/project:Type');
   	  if($aanvaardingType == 'per datum')
		    $data['Aanvaarding'] = 'per ' . $this->mcp3Project->getStringByPath('//project:Details/project:Aanvaarding/project:Datum');
      else
		    $data['Aanvaarding'] = $this->mcp3Project->getStringByPath('//project:Details/project:Aanvaarding/project:Type');

      $toelichting              = $this->mcp3Project->getStringByPath('//project:Details/project:Aanvaarding/project:Toelichting');
	    if (is_string($toelichting) && strlen(trim($toelichting)) > 0)
		    $data['Aanvaarding'] .= ', ' .$toelichting;

      return $data;
    }

    /**
    * @desc Determine project state
    *
    * @param void
    * @return string
    */
    private function determineState()
    {
	    $state          = $this->mcp3Project->getStringByPath('//project:General/project:ObjectStatus');
	    $voorbehoudDate = $this->mcp3Project->getStringByPath('//project:General/project:Voorbehoud');

	    if (in_array(strtolower($state), array('verkocht onder voorbehoud', 'verhuurd onder voorbehoud')) && (empty($voorbehoudDate) || strtotime($voorbehoudDate) < date('U')))
	    {
        $koopPrijs = $this->mcp3Project->getStringByPath('//project:Details/project:Koop/project:Prijs');
        $huurPrijs = $this->mcp3Project->getStringByPath('//project:Details/project:Huur/project:Prijs');

	      if (!empty($koopPrijs))
	        $state = 'Verkocht';
	      else if (!empty($huurPrijs))
	        $state = 'Verhuurd';
	    }

      return $state;
    }

    /**
    * @desc Get woonruimte meta data
    *
    * @param void
    * @return array
    */
    protected function getWoonruimteMetaData()
    {
      $data = array(
        'PremieSubsidies'             => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:PremieSubsidie/project:Soort/@naam'),
        'Bijzonderheden'              => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Diversen/project:Bijzonderheden/project:Bijzonderheid/@naam'),
        'Aantalkamers'                => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:Verdieping/project:AantalKamers'),
        'AantalSlaapkamers'           => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:Verdieping/project:AantalSlaapkamers'),
        'Oppervlakte'                 => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:WoonOppervlakte'),
        'Inhoud'                      => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:Inhoud'),
        'Woonkamer'                   => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Verdieping/project:Kamers/project:Woonkamer/project:Type'),
        'Keuken'                      => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Verdieping/project:Kamers/project:Keuken/project:Type'),
        'KeukenVernieuwd'             => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Verdieping/project:Kamers/project:Keuken/project:JaarVernieuwd'),
        'Ligging'                     => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Ligging'),
        'GarageType'                  => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Garage/project:Type'),
        'GarageCapaciteit'            => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Garage/project:Capaciteit'),
        'TuinType'                    => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Tuin/project:Type'),
        'TuinTotaleOppervlakte'       => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:Tuin/project:TotaleOppervlakte'),
        'HoofdTuinType'               => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Tuin/project:HoofdtuinType'),
        'HoofdTuinTotaleOppervlakte'  => $this->mcp3Project->getIntByPath('//project:Details/project:Woonruimte/project:Tuin/project:Oppervlakte'),
        'TuinLigging'                 => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Tuin/project:Ligging'),
        'BergingType'                 => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:SchuurBerging/project:Soort'),
        'BergingVoorzieningen'        => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:SchuurBerging/project:Voorzieningen/project:Voorziening/@naam'),
        'BergingIsolatie'             => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:SchuurBerging/project:Isolatievormen/project:Isolatievorm/@naam'),
        'PraktijkruimteType'          => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Praktijkruimte/project:Type'),
        'PraktijkruimteMogelijk'      => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:PraktijkruimteMogelijk/project:Type'),
        'EnergielabelKlasse'          => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Energielabel/project:Energieklasse'),
        'HuidigGebruik'               => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Bestemming/project:HuidigGebruik'),
        'HuidigeBestemming'           => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Bestemming/project:HuidigeBestemming'),
        'PermanenteBewoning'          => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Bestemming/project:PermanenteBewoning'),
        'Recreatiewoning'             => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Bestemming/project:Recreatiewoning'),
        'Verwarming'                  => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Installatie/project:Verwarming/project:Type'),
        'WarmWater'                   => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Installatie/project:WarmWater/project:Type'),
        'CvKetel'                     => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Installatie/project:CvKetel/project:Type'),
        'CvKetelBouwjaar'             => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Installatie/project:CvKetel/project:Bouwjaar'),
        'Isolatie'                    => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Diversen/project:Isolatievormen/project:Isolatie/@naam'),
        'Dak'                         => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Diversen/project:Dak'),
        'DakMaterialen'               => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Diversen/project:DakMaterialen/project:DakMateriaal/@naam'),
        'OnderhoudBinnen'             => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Onderhoud/project:Binnen/project:Waardering'),
        'OnderhoudBuiten'             => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Onderhoud/project:Buiten/project:Waardering'),
        'OnderhoudSchilderwerkBinnen' => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Onderhoud/project:SchilderwerkBinnen'),
        'OnderhoudSchilderwerkBuiten' => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Onderhoud/project:SchilderwerkBuiten'),
        'OnderhoudPlafond'            => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Onderhoud/project:Plafond'),
        'OnderhoudMuren'              => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Onderhoud/project:Muren'),
        'OnderhoudVloer'              => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Onderhoud/project:Vloer'),
        'OnderhoudDak'                => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Onderhoud/project:Dak'),
				'Verdiepingen'								=> $this->mcp3Project->getNumFloors()
      );

			// Bouwjaar
			$bouwjaarPeriode  = $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Bouwjaar/project:Periode');
			$bouwjaar         = $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Bouwjaar/project:BouwjaarOmschrijving/project:Jaar');
			$data["Bouwjaar"] = empty($bouwjaarPeriode) ? $bouwjaar : $this->translateBouwjaarPeriode($bouwjaarPeriode);

      // Voorzieningen
      $voorzieningen    = array();
      $names            = $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Voorzieningen/project:Voorziening/@naam');
      if (!empty($names))
        $voorzieningen[] = $names;
      $names            = $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Verdieping/project:Indelingen/project:Indeling/@naam');
      if (!empty($names))
        $voorzieningen[] = $names;

      if (count($voorzieningen) > 0)
        $data['Voorzieningen'] = implode(', ', $voorzieningen);

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
    * @desc Get bouwgrond meta data
    *
    * @param void
    * @return array
    */
    protected function getBouwgrondMetaData()
    {
      return array(
			  'Oppervlakte' => $this->mcp3Project->getIntByPath('//project:Details/project:Bouwgrond/project:Oppervlakte'),
			  'Ligging'     => $this->mcp3Project->getStringByPath('//project:Details/project:Bouwgrond/project:Ligging')
      );
    }

    /**
    * @desc Get Parkeergelegenheid meta data
    *
    * @param void
    * @return array
    */
    protected function getParkeergelegenheidMetaData()
    {
      return array(
			  'Oppervlakte' => $this->mcp3Project->getIntByPath('//project:Details/project:Parkeergelegenheid/project:Oppervlakte')
      );
    }

    /**
    * @desc Get Berging meta data
    *
    * @param void
    * @return array
    */
    protected function getBergingMetaData()
    {
      return array(
			  'Oppervlakte' => $this->mcp3Project->getIntByPath('//project:Details/project:Berging/project:Oppervlakte')
      );
    }

    /**
    * @desc Get Standplaats meta data
    *
    * @param void
    * @return array
    */
    protected function getStandplaatsMetaData()
    {
      return array(
			  'Oppervlakte' => $this->mcp3Project->getIntByPath('//project:Details/project:Standplaats/project:Oppervlakte')
      );
    }

    /**
    * @desc Get Ligplaats meta data
    *
    * @param void
    * @return array
    */
    protected function getLigplaatsMetaData()
    {
      return array(
			  'Oppervlakte' => $this->mcp3Project->getIntByPath('//project:Details/project:Ligplaats/project:Oppervlakte')
      );
    }
  }