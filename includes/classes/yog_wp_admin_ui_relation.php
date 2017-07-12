<?php
  /**
  * @desc YogWpAdminObjectUiWonen
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogWpAdminUiRelation extends YogWpAdminUiAbstract
  {
    /**
    * @desc Get the post type
    *
    * @param void
    * @return string
    */
    public function getPostType()
    {
      return POST_TYPE_RELATION;
    }

    /**
    * @desc Determine columns used in overview
    *
    * @param array $columns
    * @return array
    */
    public function determineColumns($columns)
    {
	    return array(
	      'cb'            => '<input type="checkbox" />',
	      'title'         => 'Naam',
	      'type'          => 'Type'
	    );
    }

    /**
    * @desc Determine content of a single column in overview
    *
    * @param string $columnId
    * @return void
    */
    public function generateColumnContent($columnId)
    {
      switch ($columnId)
      {
        case 'type':
          $type = yog_retrieveSpec('type');
          switch ($type)
          {
            case 'Business':
              echo 'Bedrijf';
              break;
            case 'Person':
              echo 'Persoon';
              break;
          }
          break;
      }
    }

    /**
    * @desc Add containers to project screen
    *
    * @param void
    * @return void
    */
    public function addMetaBoxes()
    {
	    add_meta_box('yog-contact-meta',        'Contact gegevens', array($this, 'renderContactMetaBox'),         POST_TYPE_RELATION, 'normal', 'low');
      add_meta_box('yog-main-address-meta',   'Hoofd adres',      array($this, 'renderMainAddressMetaBox'),     POST_TYPE_RELATION, 'normal', 'low');
      add_meta_box('yog-postal-address-meta', 'Post adres',       array($this, 'renderPostalAddressMetaBox'),   POST_TYPE_RELATION, 'normal', 'low');
      add_meta_box('yog-location',            'Locatie',          array($this, 'renderMapsMetaBox'),            POST_TYPE_RELATION, 'side', 'low');
    }

    /**
    * @desc Render contact meta box
    *
    * @param object $post
    * @return void
    */
    public function renderContactMetaBox($post)
    {
      $type = yog_retrieveSpec('type', $post->ID);

      echo '<input type="hidden" name="yog_nonce" id="myplugin_noncename" value="' .wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	    echo '<table class="form-table">';
      if ($type == 'Business')
	      echo $this->retrieveInputs($post->ID, array('Emailadres', 'Website', 'Telefoonnummer', 'Faxnummer'));
      else
        echo $this->retrieveInputs($post->ID, array('Titel', 'Initialen', 'Voornaam', 'Voornamen', 'Tussenvoegsel', 'Achternaam', 'Emailadres', 'Website', 'Telefoonnummer', 'Telefoonnummerwerk', 'Telefoonnummermobiel', 'Faxnummer'));
	    echo '</table>';
    }

    /**
    * @desc Render main address meta box
    *
    * @param object $post
    * @return void
    */
    public function renderMainAddressMetaBox($post)
    {
	    echo '<table class="form-table">';
	    echo $this->retrieveInputs($post->ID, array('Hoofdadres_land', 'Hoofdadres_provincie', 'Hoofdadres_gemeente', 'Hoofdadres_stad', 'Hoofdadres_wijk', 'Hoofdadres_buurt', 'Hoofdadres_straat', 'Hoofdadres_postcode', 'Hoofdadres_huisnummer'));
	    echo '</table>';
    }

    /**
    * @desc Render postal address meta box
    *
    * @param object $post
    * @return void
    */
    public function renderPostalAddressMetaBox($post)
    {
	    echo '<table class="form-table">';
	    echo $this->retrieveInputs($post->ID, array('Postadres_land', 'Postadres_provincie', 'Postadres_gemeente', 'Postadres_stad', 'Postadres_wijk', 'Postadres_buurt', 'Postadres_straat', 'Postadres_postcode', 'Postadres_huisnummer'));
	    echo '</table>';
    }

    /**
      * @desc Extend saving of huis post type with storing of custom fields
      *
      * @param int $postId
      * @param StdClass $post
      * @return void
      */
    public function extendSave($postId, $post)
    {
      // Check if post is of type wonen
	    if ($post->post_type != POST_TYPE_RELATION)
        return $postId;

      // Verify nonce
	    if ( !wp_verify_nonce($_POST['yog_nonce'], plugin_basename(__FILE__) ))
		    return $postId;

	    // verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything
	    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
	      return $postId;

	    // Check permissions
		  if (!current_user_can( 'edit_page', $postId ) )
		    return $postId;

		  // Handle meta data
      $fieldsSettings = YogFieldsSettingsAbstract::create($post->post_type);

      // Handle normal fields
		  foreach ($fieldsSettings->getFieldNames() as $fieldName)
		  {
			  if (empty($_POST[$fieldName]))
			    delete_post_meta($postId, $fieldName);
			  else
			    update_post_meta($postId, $fieldName, $_POST[$fieldName]);
		  }
    }
  }