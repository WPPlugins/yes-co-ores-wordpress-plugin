<div class="wrap">
  <div class="icon32 icon32-config-yog"><br /></div>
  <h2>Yes-co Open Real Estate System instellingen</h2>
  <h2 class="nav-tab-wrapper">
    <a href="options-general.php?page=yesco_OG" class="nav-tab nav-tab-active">Instellingen</a>
    <a href="options-general.php?page=yesco_OG_shortcode_map" class="nav-tab">Map shortcode generator</a>
    <a href="options-general.php?page=yesco_OG_shortcode_objects" class="nav-tab">Objecten shortcode generator</a>
  </h2>
  <?php
  wp_nonce_field('update-options');

  if (!empty($errors))
  {
    echo '<div id="message" class="error below-h2" style=" padding: 5px 10px;">';
      echo '<b>Er zijn fouten geconstateerd waardoor de Yes-co ORES plugin niet naar behoren kan functioneren</b>:';
      echo '<ul style="padding-left:15px;list-style-type:circle"><li>' . implode('</li><li>', $errors) . '</li></ul>';
    echo '</div>';
  }

  if (!empty($warnings))
  {
    echo '<div id="message" class="error below-h2" style="padding: 5px 10px; background-color:#feffd1;border-color:#d5d738;">';
      echo '<ul style="padding-left:15px;list-style-type:circle"><li>' . implode('</li><li>', $warnings) . '</li></ul>';
    echo '</div>';
  }

  if (empty($errors))
  {
    ?>
    <h3>Objecten</h3>
    <div class="yog-setting">
      <input type="checkbox"<?php echo (get_option('yog_huizenophome')?' checked':'');?> name="yog_huizenophome" id="yog-toggle-home" class="yog-toggle-setting" />
      <label for="yog-toggle-home">Objecten plaatsen in blog (Objecten zullen tussen 'normale' blogposts verschijnen).</label><span class="msg"></span>
    </div>
    <div class="yog-setting">
      <input type="checkbox"<?php echo (get_option('yog_objectsinarchief')?' checked':'');?> name="yog_objectsinarchief" id="yog-toggle-archive" class="yog-toggle-setting" />
      <label for="yog-toggle-archive">Objecten plaatsen in archief (Objecten zullen tussen 'normale' blogposts verschijnen).</label><span class="msg"></span>
    </div>
    <div class="yog-setting">
      <input type="checkbox"<?php echo (get_option('yog_noextratexts')?' checked':'');?> name="yog_noextratexts" id="yog-toggle-extratext" class="yog-toggle-setting" />
      <label for="yog-toggle-extratext">Extra teksten van objecten <u>niet</u> meenemen bij synchronisatie.</label><span class="msg"></span>
    </div>
    <div class="yog-setting">
      <input type="checkbox"<?php echo (get_option('yog_nochilds_searchresults')?' checked':'');?> name="yog_nochilds_searchresults" id="yog-toggle-nochilds-searchresults" class="yog-toggle-setting" />
      <label for="yog-toggle-nochilds-searchresults">Individuele objecten gekoppeld aan een NBty/BBty <u>niet</u> tonen in overzichten.</label><span class="msg"></span>
    </div>
    <h3>Synchronisatie</h3>
    <div class="yog-setting">
      <input type="checkbox"<?php echo (get_option('yog_cat_custom') ? ' checked':'');?> name="yog_cat_custom" id="yog-toggle-cat-custom" class="yog-toggle-setting" />
      <label for="yog-toggle-cat-custom">Objecten bij synchronisatie koppelen aan Yes-co ORES categorie&euml;n i.p.v. de standaard wordpress categorie&euml;n (bijv.: <?php echo site_url();?> '/objecten/consument/ i.p.v. <?php echo site_url();?>/category/consument/).</label><span class="msg"></span>
    </div>
    <div class="yog-setting">
      <input type="checkbox"<?php echo (get_option('yog_sync_disabled') ? ' checked':'');?> name="yog_sync_disabled" id="yog-toggle-sync-disabled" class="yog-toggle-setting" />
      <label for="yog-toggle-sync-disabled">Normale synchronisatie uitschakelen. (Alleen gebruiken indien de synchronisatie op een andere manier gedaan wordt, zoals met het meegeleverde cli script!)</label><span class="msg"></span>
    </div>
    <div class="yog-setting">
      <br />Voorkeur voor te synchroniseren media formaat:
      <select name="yog_media_size" id="yog_media_size" class="yog-set-setting">
        <?php
        foreach ($mediaSizeOptions as $key => $title)
        {
          echo '<option value="' . $key . '"' . ($mediaSizeOption == $key ? ' selected="selected"' : '') . '>' . $title . '</option>';
        }
        ?>
      </select><span class="msg"></span>
    </div>
    <div id="yog-sortoptions" style="display:<?php echo(get_option('yog_cat_custom') ? 'block':'none');?>">
      <h3>Sortering</h3>
      <div class="yog-setting">
        Objecten in Yes-co ORES categorie&euml;n standaard sorteren op:
        <select name="yog_order" id="yog_order" class="yog-set-setting">
        <?php
        foreach ($sortOptions as $key => $title)
        {
          echo '<option value="' . $key . '"' . ($sortOption == $key ? ' selected="selected"' : '') . '>' . $title . '</option>';
        }
        ?>
        </select><span class="msg"></span>
      </div>
    </div>

    <h3>Javascript loading</h3>
    <div class="yog-setting">
      <input type="checkbox"<?php echo (get_option('yog_javascript_dojo_dont_enqueue')?' checked':'');?> name="yog_javascript_dojo_dont_enqueue" id="yog-toggle-javascript-dojo-dont-enqueue" class="yog-toggle-setting" />
      <label for="yog-toggle-javascript-dojo-dont-enqueue">Echo + defer load de Dojo Javascript library in plaats van gebruik te maken van de wp_enqueue (gebruik in het geval dat de jquery libraries conflicteren met deze plugin)</label><span class="msg"></span>
    </div>

    <br />
    <h3>Gekoppelde yes-co open accounts</h3>
    <span id="yog-add-system-link-holder">
      <b>Een koppeling toevoegen:</b><br>
      Activatiecode: <input id="yog-new-secret" name="yog-new-secret" type="text" style="width: 58px" maxlength="6" value="" /> <input type="button" class="button-primary" id="yog-add-system-link" value="Koppeling toevoegen" style="margin-left: 10px;" />
    </span>
    <div id="yog-system-links">
      <?php
      if (!empty($systemLinks))
      {
        foreach ($systemLinks as $systemLink)
        {
					// create sync url
					$action     = 'sync_yesco_og';
					$signature  = md5('action=' . $action . 'uuid=' . $systemLink->getCollectionUuid() . $systemLink->getActivationCode());
					$syncUrl		= get_site_url() . '/?action=' . $action . '&uuid=' . $systemLink->getCollectionUuid() . '&signature=' . $signature;

          echo '<div class="system-link" id="yog-system-link-' . $systemLink->getActivationCode() . '">';
            echo '<div data-sync-callback="' . $syncUrl . '">';
              echo '<b>Naam:</b> ' . $systemLink->getName() .'<br />';
              echo '<b>Status:</b> ' . $systemLink->getState() .'<br />';
              echo '<b>Activatiecode:</b> ' . $systemLink->getActivationCode() .' <br />';
              echo '<a onclick="jQuery(this).next().show(); jQuery(this).hide();">Koppeling verwijderen</a>';
              echo '<span class="hide" id="yog-system-link-' . $systemLink->getActivationCode() . '-remove">Wilt u deze koppeling verbreken? <span><a onclick="jQuery(this).parent().hide();jQuery(this).parent().prev().show();">annuleren</a> | <a onclick="yogRemoveSystemLink(\'' . $systemLink->getActivationCode() .'\');">doorgaan</a></span></span>';
							
							
            echo '</div>';
          echo '</div>';
        }
      }
	    ?>
    </div>
    <br /><br />
    <?php
    // BEGIN YOG MAP MARKER SETTINGS

    echo '<form method="post" action="options-general.php?page=' . $this->optionGroup . '" enctype="multipart/form-data">';


    if (!empty($_POST))
    {
      if (!empty($_POST['yog_google_maps_api_key']))
      {
        // Update API Key
        update_option('yog_google_maps_api_key', $_POST['yog_google_maps_api_key']);
      }
      else
      {
        delete_option('yog_google_maps_api_key');
      }
    }

    ?>
    <h3>Google Maps</h3>
    <div class="yog-setting">
      <label for="yog_google_maps_api_key">API Key</label>
      <input type="text" value="<?php echo (get_option('yog_google_maps_api_key') ? get_option('yog_google_maps_api_key') :''); ?>" name="yog_google_maps_api_key" id="yog_google_maps_api_key" />
      <span class="msg"></span>
    </div>
    <?php

    register_setting($this->optionGroup, $this->optionGroup);
    settings_fields($this->optionGroup);

    $settingsSectionId = 'markerSettings';
    $settingsMarkerPage = 'page-marker-settings';

    add_settings_section($settingsSectionId, 'Marker Settings', array($this, 'section'), $settingsMarkerPage);

    $postTypes    = yog_getAllPostTypes();

    foreach ($postTypes as $postType)
    {
      $postTypeObject = get_post_type_object($postType);
      $optionName     = 'yog-marker-type-' . $postType;
      $logoOptions    = get_option($optionName);

      add_settings_field('markerSettings_' . $postType, $postTypeObject->labels->singular_name, array($this, 'inputFile'), $settingsMarkerPage, $settingsSectionId, array($logoOptions, $postType, $optionName));
    }

    // Render the section and fields to the screen of the provided page
    do_settings_sections($settingsMarkerPage);

    submit_button();

    echo '</form>';
  }
  ?>
</div>