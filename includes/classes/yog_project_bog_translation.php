<?php
  /**
  * @desc YogProjectBogTranslation
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogProjectBogTranslation extends YogProjectTranslationAbstract
  {
    /**
    * @desc Get post type
    *
    * @param void
    * @return string
    */
    public function getPostType()
    {
      return POST_TYPE_BOG;
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
      $type = ($this->mcp3Project->hasSubType()) ? $this->mcp3Project->getSubType() : $this->mcp3Project->getType();

      // Type specific meta data
      switch (strtolower($type))
      {
        case 'bouwgrond':
          $data = array_merge($data, $this->getBouwgrondMetaData());
          break;
        case 'bedrijfsruimte':
          $data = array_merge($data, $this->getBedrijfsruimteMetaData());
          break;
        case 'kantoorruimte':
          $data = array_merge($data, $this->getKantoorruimteMetaData());
          break;
        case 'winkelruimte':
          $data = array_merge($data, $this->getWinkelruimteMetaData());
          break;
        case 'horeca':
          $data = array_merge($data, $this->getHorecaMetaData());
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
      $type = ($this->mcp3Project->hasSubType()) ? $this->mcp3Project->getSubType() : $this->mcp3Project->getType();
      if ($type == 'Bouwgrond')
        $type = 'bog-bouwgrond';

	    $categories = array('bedrijf', strtolower($type));

      // Bestaand / Nieuwbouw
      $bouwType = $this->mcp3Project->getStringByPath('//project:General/project:BouwType');
      if (!empty($bouwType))
      {
        switch ($bouwType)
        {
          case 'bestaande bouw':
            $categories[] = 'bog-bestaand';
            break;
          case 'nieuwbouw':
            $categories[] = 'bog-nieuwbouw';
            break;
        }
      }

      // Verkoop
      $koopPrijs = $this->mcp3Project->getStringByPath('//project:Details/project:Koop/project:Prijs');
      if (!empty($koopPrijs))
        $categories[] = 'bog-verkoop';

      // Verhuur
      $koopPrijs = $this->mcp3Project->getStringByPath('//project:Details/project:Huur/project:Prijs');
      if (!empty($koopPrijs))
        $categories[] = 'bog-verhuur';

      // Determine verkoop / verhuur category based on scenario (fallback)
      if (!in_array('bog-verkoop', $categories) && !in_array('bog-verhuur', $categories))
      {
        switch ($this->mcp3Project->getScenario())
        {
          case 'BOvk':
            $categories[] = 'bog-verkoop';
            break;
          case 'BOvh':
            $categories[] = 'bog-verhuur';
            break;
        }
      }

		  // State
      if (in_array(strtolower($this->determineState()), array('verkocht', 'verhuurd')))
			  $categories[] = 'bog-verkochtverhuurd';

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
    private function getGeneralMetaData()
    {
      $data = array(
        'uuid'                        => $this->mcp3Project->getStringByPath('/project:Project/@uuid'),
        'dlm'                         => $this->translateDate($this->mcp3Link->getDlm()),
        'scenario'                    => $this->mcp3Project->getScenario(),
        'ApiKey'                      => $this->mcp3Project->getStringByPath('/project:Project/project:YProjectNumber'),
        'Status'                      => $this->determineState(),
        'Naam'                        => $this->mcp3Project->getStringByPath('//project:General/project:Name'),
        'BouwType'                    => $this->mcp3Project->getStringByPath('//project:General/project:BouwType'),
        'Land'                        => $this->mcp3Project->getStringByPath('//project:General/project:Address/project:Country'),
        'Provincie'                   => $this->mcp3Project->getStringByPath('//project:General/project:Address/project:State'),
        'Gemeente'                    => $this->mcp3Project->getStringByPath('//project:General/project:Address/project:Municipality'),
        'Plaats'                      => $this->mcp3Project->getStringByPath('//project:General/project:Address/project:City'),
        'Wijk'                        => $this->mcp3Project->getStringByPath('//project:General/project:Address/project:Area'),
        'Buurt'                       => $this->mcp3Project->getStringByPath('//project:General/project:Address/project:Neighbourhood'),
        'Straat'                      => $this->mcp3Project->getStringByPath('//project:General/project:Address/project:Street'),
        'Postcode'                    => $this->mcp3Project->getStringByPath('//project:General/project:Address/project:Zipcode'),
        'Longitude'                   => $this->mcp3Project->getStringByPath('//project:General/project:GeoCode/project:Longitude'),
        'Latitude'                    => $this->mcp3Project->getStringByPath('//project:General/project:GeoCode/project:Latitude'),
        'NummerreeksStart'            => $this->mcp3Project->getStringByPath('//project:General/project:Nummerreeks/project:Start'),
        'NummerreeksEind'             => $this->mcp3Project->getStringByPath('//project:General/project:Nummerreeks/project:End'),
        'DatumVoorbehoudTot'          => $this->mcp3Project->getStringByPath('//project:General/project:Voorbehoud'),
        'Aanmelding'                  => $this->mcp3Project->getStringByPath('//project:Details/project:Aanmelding'),
        'KoopPrijsConditie'           => $this->translatePriceCondition($this->mcp3Project->getStringByPath('//project:Details/project:Koop/project:PrijsConditie')),
        'KoopPrijsVervanging'         => $this->mcp3Project->getStringByPath('//project:Details/project:Koop/project:PrijsVervanging'),
        'Bouwrente'                   => $this->translateBool($this->mcp3Project->getBoolByPath('//project:Details/project:Koop/project:Bouwrente')),
        'Veilingdatum'                => $this->mcp3Project->getStringByPath('//project:Details/project:Koop/project:Veiling/project:Datum'),
        'HuurPrijsConditie'           => $this->translatePriceCondition($this->mcp3Project->getStringByPath('//project:Details/project:Huur/project:PrijsConditie')),
        'HuurPrijsVervanging'         => $this->mcp3Project->getStringByPath('//project:Details/project:Huur/project:PrijsVervanging'),
        'Servicekosten'               => $this->mcp3Project->getIntByPath('//project:Details/project:Huur/project:Servicekosten'),
        'ServicekostenValuta'         => $this->mcp3Project->getStringByPath('//project:Details/project:Huur/project:Servicekosten/@valuta'),
        'ServicekostenBtwPercentage'  => $this->mcp3Project->getIntByPath('//project:Details/project:Huur/project:Servicekosten/@btwPercentage'),
        'ServicekostenBtwBelast'      => $this->translateBool($this->mcp3Project->getBoolByPath('//project:Details/project:Huur/project:Servicekosten/@btwBelast')),
        'ServicekostenConditie'       => $this->translatePriceCondition($this->mcp3Project->getStringByPath('//project:Details/project:Huur/project:ServicekostenConditie')),
        'Erfpacht'                    => $this->mcp3Project->getIntByPath('//project:KadastraleInformatie/project:Eigendom/project:ErfpachtPerJaar'),
        'PerceelOppervlakte'          => $this->mcp3Project->getIntByPath('//project:KadastraleInformatie/project:PerceelOppervlakte'),
        'AantalHuurders'              => $this->mcp3Project->getIntByPath('//project:Details/project:Koop/project:Belegging/project:AantalHuurders'),
        'BeleggingExpiratieDatum'     => $this->mcp3Project->getStringByPath('//project:Details/project:Belegging/project:ExpiratieDatum'),
        'Hoofdbestemming'             => $this->mcp3Project->getStringByPath('//project:Details/project:Bestemming/project:Hoofdbestemming'),
        'Nevenbestemming'             => $this->mcp3Project->getStringByPath('//project:Details/project:Bestemming/project:Nevenbestemming'),
        'WoonruimteSituatie'          => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Situatie'),
        'WoonruimteStatus'            => $this->mcp3Project->getStringByPath('//project:Details/project:Woonruimte/project:Status')
      );

      // Prices
      $data = array_merge($data, $this->handlePriceBtw('//project:Details/project:Koop/project:Prijs', 'KoopPrijs'));
      $data = array_merge($data, $this->handlePriceBtw('//project:Details/project:Huur/project:Prijs', 'HuurPrijs'));
      $data = array_merge($data, $this->handlePriceBtw('//project:Details/project:Koop/project:Belegging/project:Huuropbrengst', 'Huuropbrengst'));


      // Erfpacht duur
	    $data['ErfpachtDuur']     = $this->mcp3Project->getStringByPath('//project:KadastraleInformatie[0]/project:Eigendom/project:ErfpachtDuur');
	    if ($data['ErfpachtDuur'] != 'eeuwig')
		    $data['ErfpachtDuur'] .= ' ' . $this->mcp3Project->getStringByPath('//project:KadastraleInformatie[0]/project:Eigendom/project:EindDatum');

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
    * @desc Get bouwgrond meta data
    *
    * @param void
    * @return array
    */
    private function getBouwgrondMetaData()
    {
      $data = array(
        'BouwgrondBebouwingsmogelijkheid'       => $this->mcp3Project->getStringByPath('//project:Details/project:Bouwgrond/project:Bebouwingsmogelijkheid'),
        'BouwgrondBouwhoogte'                   => $this->mcp3Project->getIntByPath('//project:Details/project:Bouwgrond/project:Bouwhoogte'),
        'BouwgrondInUnitsVanaf'                 => $this->mcp3Project->getIntByPath('//project:Details/project:Bouwgrond/project:InUnitsVanaf'),
        'BouwgrondVloerOppervlakte'             => $this->mcp3Project->getIntByPath('//project:Details/project:Bouwgrond/project:VloerOppervlakte'),
        'Oppervlakte'                           => $this->mcp3Project->getIntByPath('//project:Details/project:Bouwgrond/project:VloerOppervlakte'),
        'BouwgrondVloerOppervlakteProcentueel'  => $this->mcp3Project->getIntByPath('//project:Details/project:Bouwgrond/project:VloerOppervlakteProcentueel')
      );

      return $data;
    }

    /**
    * @desc Get bedrijfsruimte meta data
    *
    * @param void
    * @return array
    */
    private function getBedrijfsruimteMetaData()
    {
      $data = array(
        'BedrijfshalOppervlakte'          => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Bedrijfsruimte/project:Bedrijfshal/project:Oppervlakte'),
        'Oppervlakte'                     => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Bedrijfsruimte/project:Bedrijfshal/project:Oppervlakte'),
        'BedrijfshalInUnitsVanaf'         => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Bedrijfsruimte/project:Bedrijfshal/project:InUnitsVanaf'),
        'BedrijfshalVrijeHoogte'          => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Bedrijfsruimte/project:Bedrijfshal/project:VrijeHoogte'),
        'BedrijfshalVrijeOverspanning'    => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Bedrijfsruimte/project:Bedrijfshal/project:VrijeOverspanning'),
        'BedrijfshalVloerbelasting'       => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Bedrijfsruimte/project:Bedrijfshal/project:Vloerbelasting'),
        'BedrijfshalVoorzieningen'        => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Bedrijfsruimte/project:Bedrijfshal/project:Voorzieningen/project:Voorziening/@naam'),
        'KantoorruimteOppervlakte'        => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Bedrijfsruimte/project:Kantoorruimte/project:Oppervlakte'),
        'KantoorruimteAantalVerdiepingen'  => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Bedrijfsruimte/project:Kantoorruimte/project:Verdiepingen/project:Aantal'),
        'KantoorruimteVoorzieningen'       => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Bedrijfsruimte/project:Kantoorruimte/project:Voorzieningen/project:Voorziening/@naam'),
        'TerreinOppervlakte'              => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Bedrijfsruimte/project:Terrein/project:Oppervlakte'),
        'TerreinBouwvolumeBouwhoogte'     => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Bedrijfsruimte/project:Terrein/project:Bouwvolume/project:Bouwhoogte')
      );

      // Vloer oppervlakte terrein
      $vloerOppervlakte             = $this->mcp3Project->getIntByPath('//project:Gebouw/project:Bedrijfsruimte/project:Terrein/project:Bouwvolume/project:VloerOppervlakte');
      $vloerOppervlakteProcentueel  = $this->mcp3Project->getIntByPath('//project:Gebouw/project:Bedrijfsruimte/project:Terrein/project:Bouwvolume/project:VloerOppervlakteProcentueel');

      if (!empty($vloerOppervlakte))
        $data['TerreinBouwvolumeVloerOppervlakte'] = $vloerOppervlakte . ' m&sub2;';
      else if (!empty($vloerOppervlakteProcentueel))
        $data['TerreinBouwvolumeVloerOppervlakte'] = $vloerOppervlakteProcentueel . '%';

      // Prices
      $data = array_merge($data, $this->handlePriceBtw('//project:Gebouw/project:Bedrijfsruimte/project:Bedrijfshal/project:Prijs', 'BedrijfshalPrijs'));
      $data = array_merge($data, $this->handlePriceBtw('//project:Gebouw/project:Bedrijfsruimte/project:Kantoorruimte/project:Prijs', 'KantoorruimtePrijs'));
      $data = array_merge($data, $this->handlePriceBtw('//project:Gebouw/project:Bedrijfsruimte/project:Terrein/project:Prijs', 'TerreinPrijs'));

      // Gebouw meta data
      $data = array_merge($data, $this->getGebouwMetaData());

      return $data;
    }

    /**
    * @desc Get kantoor ruimte meta data
    *
    * @param void
    * @return array
    */
    private function getKantoorruimteMetaData()
    {
      $data = array(
        'KantoorruimteOppervlakte'        => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Kantoorruimte/project:Oppervlakte'),
        'Oppervlakte'                     => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Kantoorruimte/project:Oppervlakte'),
        'KantoorruimteAantalVerdiepingen' => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Kantoorruimte/project:Verdiepingen/project:Aantal'),
        'KantoorruimteVoorzieningen'      => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Kantoorruimte/project:Voorzieningen/project:Voorziening/@naam'),
        'KantoorruimteInUnitsVanaf'       => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Kantoorruimte/project:InUnitsVanaf'),
        'KantoorruimteTurnKey'            => $this->translateBool($this->mcp3Project->getBoolByPath('//project:Gebouw/project:Kantoorruimte/project:Turnkey'))
      );

      // Gebouw meta data
      $data = array_merge($data, $this->getGebouwMetaData());

      return $data;
    }

    /**
    * @desc Get winkelruimte meta data
    *
    * @param void
    * @return array
    */
    private function getWinkelruimteMetaData()
    {
      $data = array(
        'WinkelruimteOppervlakte'                   => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Winkelruimte/project:Oppervlakte'),
        'Oppervlakte'                               => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Winkelruimte/project:Oppervlakte'),
        'WinkelruimteVerkoopVloerOppervlakte'       => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Winkelruimte/project:VerkoopVloerOppervlakte'),
        'WinkelruimteInUnitsVanaf'                  => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Winkelruimte/project:InUnitsVanaf'),
        'WinkelruimteFrontBreedte'                  => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Winkelruimte/project:Frontbreedte'),
        'WinkelruimteAantalVerdiepingen'            => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Winkelruimte/project:Verdiepingen/project:Aantal'),
        'WinkelruimteWelstandsklasse'               => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Winkelruimte/project:Welstandsklasse'),
        'WinkelruimteBrancheBeperking'              => $this->translateBool($this->mcp3Project->getBoolByPath('//project:Gebouw/project:Winkelruimte/project:Branchebeperking')),
        'WinkelruimteHorecaToegestaan'              => $this->translateBool($this->mcp3Project->getBoolByPath('//project:Gebouw/project:Winkelruimte/project:HorecaToegestaan')),
        'WinkelruimteBijdrageWinkeliersvereniging'  => $this->translateBool($this->mcp3Project->getBoolByPath('//project:Gebouw/project:Winkelruimte/project:BijdrageWinkeliersvereniging')),
        'WinkelruimtePersoneelTerOvername'          => $this->translateBool($this->mcp3Project->getBoolByPath('//project:Gebouw/project:Winkelruimte/project:TerOvername/project:Personeel'))
      );

      // Prices
      $data = array_merge($data, $this->handlePriceBtw('//project:Gebouw/project:Winkelruimte/project:TerOvername/project:PrijsInventarisGoodwill', 'WinkelruimtePrijsInventarisGoodwill'));

      // Gebouw meta data
      $data = array_merge($data, $this->getGebouwMetaData());

      return $data;
    }

    /**
    * @desc Get Horeca meta data
    *
    * @param void
    * @return array
    */
    private function getHorecaMetaData()
    {
      $data = array(
        'HorecaType'                    => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Horeca/project:Type'),
        'HorecaOppervlakte'             => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Horeca/project:Oppervlakte'),
        'Oppervlakte'                   => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Horeca/project:Oppervlakte'),
        'HorecaVerkoopVloerOppervlakte' => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Horeca/project:VerkoopVloerOppervlakte'),
        'HorecaWelstandsklasse'         => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Horeca/project:Welstandsklasse'),
        'HorecaConcentratieGebied'      => $this->translateBool($this->mcp3Project->getBoolByPath('//project:Gebouw/project:Horeca/project:ConcentratieGebied')),
        'HorecaRegio'                   => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Horeca/project:Regio'),
        'HorecaPersoneelTerOvername'    => $this->translateBool($this->mcp3Project->getBoolByPath('//project:Gebouw/project:Horeca/project:TerOvername/project:Personeel'))
      );

      // Prices
      $data = array_merge($data, $this->handlePriceBtw('//project:Gebouw/project:Horeca/project:TerOvername/project:PrijsInventarisGoodwill', 'HorecaPrijsInventarisGoodwill'));

      // Verdiepingen
      $verdiepingNodes = $this->mcp3Project->getNodesByXpath('//project:Gebouw/project:Horeca/project:Verdiepingen/project:Verdieping');
      $data['HorecaAantalVerdiepingen'] = count($verdiepingNodes);

      // Gebouw meta data
      $data = array_merge($data, $this->getGebouwMetaData());

      return $data;
    }

    /**
    * @desc Get bouwjaar meta data
    *
    * @param void
    * @return array
    */
    private function getGebouwMetaData()
    {
      $data = array(
        'InAanbouw'                         => $this->translateBool($this->mcp3Project->getBoolByPath('//project:Gebouw/project:Bouwjaar/project:InAanbouw')),
        'OnderhoudBinnen'                   => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Onderhoud/project:Binnen/project:Waardering'),
        'OnderhoudBinnenOmschrijving'       => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Onderhoud/project:Binnen/project:Omschrijving'),
        'OnderhoudBuiten'                   => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Onderhoud/project:Buiten/project:Waardering'),
        'OnderhoudBuitenOmschrijving'       => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Onderhoud/project:Buiten/project:Omschrijving'),
        'LokatieOmschrijving'               => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Lokatie/project:Omschrijving'),
        'Ligging'                           => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Lokatie/project:Ligging'),
        'SnelwegAfrit'                      => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Lokatie/project:Bereikbaarheid/project:SnelwegAfrit'),
        'NsStation'                         => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Lokatie/project:Bereikbaarheid/project:NsStation'),
        'NsVoorhalte'                       => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Lokatie/project:Bereikbaarheid/project:NsVoorhalte'),
        'BusKnooppunt'                      => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Lokatie/project:Bereikbaarheid/project:BusKnooppunt'),
        'TramKnooppunt'                     => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Lokatie/project:Bereikbaarheid/project:TramKnooppunt'),
        'MetroKnooppunt'                    => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Lokatie/project:Bereikbaarheid/project:MetroKnooppunt'),
        'Bushalte'                          => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Lokatie/project:Bereikbaarheid/project:Bushalte'),
        'Tramhalte'                         => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Lokatie/project:Bereikbaarheid/project:Tramhalte'),
        'Metrohalte'                        => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Lokatie/project:Bereikbaarheid/project:Metrohalte'),
        'BankAfstand'                       => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Lokatie/project:Voorzieningen/project:Bank/project:Afstand'),
        'BankAantal'                        => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Lokatie/project:Voorzieningen/project:Bank/project:Aantal'),
        'OntspanningAfstand'                => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Lokatie/project:Voorzieningen/project:Ontspanning/project:Afstand'),
        'OntspanningAantal'                 => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Lokatie/project:Voorzieningen/project:Ontspanning/project:Aantal'),
        'RestaurantAfstand'                 => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Lokatie/project:Voorzieningen/project:Restaurant/project:Afstand'),
        'RestaurantAantal'                  => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Lokatie/project:Voorzieningen/project:Restaurant/project:Aantal'),
        'WinkelAfstand'                     => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Lokatie/project:Voorzieningen/project:Winkel/project:Afstand'),
        'WinkelAantal'                      => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Lokatie/project:Voorzieningen/project:Winkel/project:Aantal'),
        'ParkerenOmschrijving'              => $this->mcp3Project->getStringByPath('//project:Gebouw/project:Lokatie/project:Parkeren/project:Omschrijving'),
        'AantalParkeerplaatsen'             => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Lokatie/project:Parkeren/project:Parkeerplaats/project:Aantal'),
        'AantalParkeerplaatsenOverdekt'     => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Lokatie/project:Parkeren/project:Parkeerplaats[project:Overdekt = \'true\']/project:Aantal'),
        'AantalParkeerplaatsenNietOverdekt' => $this->mcp3Project->getIntByPath('//project:Gebouw/project:Lokatie/project:Parkeren/project:Parkeerplaats[project:Overdekt != \'true\']/project:Aantal')
      );

      $periode = $this->mcp3Project->getStringByPath('//project:Gebouw/project:Bouwjaar/project:Periode');
      if (!empty($periode))
      {
        $data['Bouwjaar'] = $this->translateBouwjaarPeriode($periode);
      }
      else
      {
        $data['Bouwjaar'] = $this->mcp3Project->getStringByPath('//project:Gebouw/project:Bouwjaar/project:BouwjaarOmschrijving[0]/project:Jaar');
      }

      // Prices
      $data = array_merge($data, $this->handlePriceBtw('//project:Gebouw/project:Lokatie/project:Parkeren/project:Parkeerplaats[project:Overdekt = \'true\']/project:Prijs', 'PrijsParkeerplaatsenOverdekt'));
      $data = array_merge($data, $this->handlePriceBtw('//project:Gebouw/project:Lokatie/project:Parkeren/project:Parkeerplaats[project:Overdekt != \'true\']/project:Prijs', 'PrijsParkeerplaatsenNietOverdekt'));

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
	    $voorbehoudDate = $this->mcp3Project->getStringByPath('//project:Details/project:DatumVoorbehoudTot');

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
    * @desc Handle price btw element
    *
    * @param string $xpath
    * @paran string $field
    * @return array
    */
    private function handlePriceBtw($xpath, $field)
    {
      return array(
        $field                    => $this->mcp3Project->getIntByPath($xpath),
        $field . 'Valuta'         => $this->mcp3Project->getStringByPath($xpath . '/@valuta'),
        $field . 'BtwPercentage'  => ($this->mcp3Project->getBoolByPath($xpath . '/@btwBelast') ? $this->mcp3Project->getIntByPath($xpath . '/@btwPercentage') : ''),
        $field . 'BtwBelast'      => $this->translateBool($this->mcp3Project->getBoolByPath($xpath . '/@btwBelast'))
      );
    }
  }