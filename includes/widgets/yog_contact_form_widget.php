<?php
/**
* @desc YogContactFormWidget
* @author Kees Brandenburg - Yes-co Nederland
*/
class YogContactFormWidget extends WP_Widget
{
  const NAME                = 'Yes-co Contact formulier';
  const DESCRIPTION         = 'Contact formulier wat direct in je eigen Yes-co systeem binnen komt.';
  const CLASSNAME           = 'yog-contact-form';
  const FORM_ACTION         = 'http://api.yes-co.com/1.0/response';
  const JS_LOCATION         = 'http://api.yes-co.com/1.0/embed/js/response-forms.js';

  const DEFAULT_THANKS_MSG  = 'Het formulier is verzonden, we nemen zo spoedig mogelijk contact met u op.';
  const WIDGET_ID_PREFIX    = 'yogcontactformwidget-';

  /**
  * @desc Constructor
  *
  * @param void
  * @return YogContactFormWidget
  */
  public function __construct()
  {
    $options = array( 'classsname'  => self::CLASSNAME,
                      'description' => self::DESCRIPTION);
		
    parent::__construct(false, $name = self::NAME, $options);
  }

  /**
   * @desc Method shortcodeAttributesToSettings
   *
   * @param {Array} $atts
   * @return {
   */
  public function shortcodeAttributesToSettings($atts)
  {
    $settings               = array();



    return $settings;
  }

  /**
  * @desc Display widget
  *
  * @param array $args
  * @param array $instance
  * @return void
  */
  public function widget($args, $instance)
  {
    $title          = apply_filters('widget_title', $instance['title']);
    $yescoKey       = empty($instance['yesco_key']) ? '' : $instance['yesco_key'];
    $placeholder    = (!empty($instance['placeholder']) && $instance['placeholder'] == '1') ? true : false;
    $actions        = empty($instance['actions']) ? '' : $instance['actions'];
    $tagObject      = empty($instance['tag_object']) ? '' : $instance['tag_object'];
    $tagRelation    = empty($instance['tag_relation']) ? '' : $instance['tag_relation'];
    $roleRelation   = empty($instance['role_relation']) ? '' : esc_attr($instance['role_relation']);
    $thanksMsg      = empty($instance['thanks_msg']) ? self::DEFAULT_THANKS_MSG : $instance['thanks_msg'];
    $showFirstname  = empty($instance['show_firstname']) ? false : true;
    $showLastname   = empty($instance['show_lastname']) ? false : true;
    $showEmail      = empty($instance['show_email']) ? false : true;
    $showPhone      = empty($instance['show_phone']) ? false : true;
    $showAddress    = empty($instance['show_address']) ? false : true;
    $showRemarks    = empty($instance['show_remarks']) ? false : true;
    $showNewsletter = empty($instance['show_newsletter']) ? false : true;
    $widgetId       = empty($args['widget_id']) ? 0 : str_replace(self::WIDGET_ID_PREFIX, '', $args['widget_id']);
    $jsShow         = empty($instance['js_show']) ? '' : esc_attr($instance['js_show']);
    $jsSend         = empty($instance['js_send']) ? '' : esc_attr($instance['js_send']);

    if (!empty($_GET['send']) && $_GET['send'] == $widgetId)
    {
      // Show thank you page
      echo $args['before_widget'];
      if (!empty($title))
        echo $args['before_title'] . $title . $args['after_title'];

      echo '<p>' . $thanksMsg . '</p>';

      echo $args['after_widget'];

      if (!empty($jsSend))
        wp_enqueue_script('widget-' . $widgetId . '-send-js', $jsSend);
    }
    else if (!empty($yescoKey))
    {
      if (!empty($jsShow))
        wp_enqueue_script('widget-' . $widgetId . '-show-js', $jsShow);

      // Show form
      if (!empty($_SERVER['HTTP_HOST']))
      {
        $thankYouPage  = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $thankYouPage .= ((strpos($thankYouPage, '?') === false) ? '?' : '&amp;') . 'send=' . $widgetId;
      }

      echo $args['before_widget'];
      if (!empty($title))
        echo $args['before_title'] . $title . $args['after_title'];

      echo '<form method="post" action="#" onsubmit="this.action = \'' . self::FORM_ACTION . '\';">';
        echo '<input type="hidden" name="yesco_key" value="' . $yescoKey . '" />';

        echo '<input type="hidden" name="title" value="' . $title . '" />';
        echo '<input type="hidden" name="source" value="' . get_bloginfo('name') . '" />';

        if (!empty($tagObject))
          echo '<input type="hidden" name="project_tags[]" value="' . $tagObject . '" />';

        if (!empty($tagRelation))
          echo '<input type="hidden" name="person_tags[]" value="' . $tagRelation . '" />';

        if (!empty($roleRelation))
          echo '<input type="hidden" name="project_role" value="' . $roleRelation . '" />';

        if (!empty($thankYouPage))
          echo '<input type="hidden" name="thank_you_page" value="' . $thankYouPage . '" />';

        if (is_single() && yog_isObject())
        {
          $projectApiKey = yog_retrieveSpec('ApiKey');
          if (!empty($projectApiKey))
            echo '<input type="hidden" name="project_id"  value="' . $projectApiKey. '" />';
        }

        // First name
        if ($showFirstname)
        {
          echo '<p>';

          $label = 'Voornaam';

          $extraParams = '';

            if ($placeholder)
              $extraParams = 'placeholder="' . $label . '" ';
            else
              echo '<label for="person[firstname]">' . $label . ':</label>';

            echo '<input type="text" name="person[firstname]" id="person[firstname]" value="" ' . $extraParams . '/>';
          echo '</p>';
        }
        // Achternaam
        if ($showLastname)
        {
          echo '<p>';

          $label = 'Achternaam';

          $extraParams = '';

          if ($placeholder)
            $extraParams = 'placeholder="' . $label . '" ';
          else
            echo '<label for="person[lastname]">' . $label . ':</label>';

            echo '<input type="text" name="person[lastname]" id="person[lastname]" value="" class="required" ' . $extraParams . '/>';
          echo '</p>';
        }
        // E-mail
        if ($showEmail)
        {
          echo '<p>';

          $label = 'E-mail';

          $extraParams = '';

          if ($placeholder)
            $extraParams = 'placeholder="' . $label . '" ';
          else
            echo '<label for="person[email]">' . $label . ':</label>';

            echo '<input type="text" name="person[email]" id="person[email]" value="" class="required" ' . $extraParams . '/>';
          echo '</p>';
        }
        // Telephone
        if ($showPhone)
        {
          echo '<p>';

          $label = 'Telefoon';

          $extraParams = '';

          if ($placeholder)
            $extraParams = 'placeholder="' . $label . '" ';
          else
            echo '<label for="person[phone]">' . $label . ':</label>';

            echo '<input type="text" name="person[phone]" id="person[phone]" value="" ' . $extraParams . '/>';
          echo '</p>';
        }
        // Address
        if ($showAddress)
        {
          echo '<p>';

          $label = 'Straat';

          $extraParams = '';

          if ($placeholder)
            $extraParams = 'placeholder="' . $label . '" ';
          else
            echo '<label for="person[street]">' . $label . ':</label>';

            echo '<input type="text" name="person[street]" id="person[street]" value="" ' . $extraParams . '/>';
          echo '</p>';


          echo '<p>';

          $label = 'Huisnummer';

          $extraParams = '';
          $extraParams2 = '';

          if ($placeholder)
          {
            $extraParams = 'placeholder="Huisnr" ';
            $extraParams2 = 'placeholder="Postcode" ';
          }
          else
          {
            echo '<label for="personHousenumber" class="label-housenumber">' . $label . ':</label><label for="personZipcode" class="label-zipcode"> / Postcode:</label><br />';
          }

            //echo '<label for="personHousenumber" class="label-housenumber">Huisnummer</label><label for="personZipcode" class="label-zipcode"> / Postcode:</label><br />';

            echo '<input type="text" name="person[housenumber]" id="personHousenumber" value="" ' . $extraParams . '/><input type="text" name="person[zipcode]" id="personZipcode" value="" ' . $extraParams2 . '/>';
          echo '</p>';
          echo '<p>';

            $label = 'Plaats';

            $extraParams = '';

            if ($placeholder)
              $extraParams = 'placeholder="' . $label . '" ';
            else
              echo '<label for="person[city]">' . $label . ':</label>';

            echo '<input type="text" name="person[city]" id="person[city]" value="" ' . $extraParams . '/>';
          echo '</p>';
        }
        // Actions
        if (!empty($actions))
        {
          $actions = explode("\n", $actions);
          echo '<p>';
            echo '<label>Acties:</label><br />';
            foreach ($actions as $key => $action)
            {
              echo '<input type="checkbox" name="actions[]" id="actions_' . $key . '" value="' . $action . '" /> ';
              echo '<label for="actions_' . $key . '">' . $action . '</label><br />';
            }
          echo '</p>';
        }
        // Opmerkingen
        if ($showRemarks)
        {
          echo '<p>';

          $label = 'Opmerkingen';

          $extraParams = '';

          if ($placeholder)
            $extraParams = 'placeholder="' . $label . '" ';
          else
            echo '<label for="comments">' . $label . ':</label>';

            echo '<textarea name="comments" id="comments" ' . $extraParams . '></textarea>';
          echo '</p>';
        }
        // Newsletter
        if ($showNewsletter)
        {
          echo '<p><input type="checkbox" name="person_tags[]" id="person_tag_nieuwsbrief" value="nieuwsbrief" /> <label for="person_tag_nieuwsbrief">Schrijf mij in voor uw nieuwbrief</label></p>';
        }

        echo '<p><label>&nbsp;</label><input type="submit" value="Verzenden" /></p>';
      echo '</form>';

      echo $args['after_widget'];
			
			wp_enqueue_script('yog-response-forms', self::JS_LOCATION, array(), false, true);
    }
  }

  /**
  * @desc Update widget settings
  *
  * @param array $new_instance
  * @param array $old_instance
  * @return array
  */
  public function update($new_instance, $old_instance)
  {
    $instance                     = $old_instance;
    $instance['title']            = empty($new_instance['title']) ? '' : $new_instance['title'];
    $instance['yesco_key']        = empty($new_instance['yesco_key']) ? '' : $new_instance['yesco_key'];
    $instance['placeholder']      = empty($new_instance['placeholder']) ? '' : $new_instance['placeholder'];
    $instance['actions']          = empty($new_instance['actions']) ? '' : $new_instance['actions'];
    $instance['thanks_msg']       = empty($new_instance['thanks_msg']) ? '' : $new_instance['thanks_msg'];
    $instance['tag_object']       = empty($new_instance['tag_object']) ? '' : trim($new_instance['tag_object']);
    $instance['tag_relation']     = empty($new_instance['tag_relation']) ? '' : trim($new_instance['tag_relation']);
    $instance['role_relation']    = empty($new_instance['role_relation']) ? '' : trim($new_instance['role_relation']);
    $instance['show_firstname']   = empty($new_instance['show_firstname']) ? 0 : 1;
    $instance['show_lastname']    = empty($new_instance['show_lastname']) ? 0 : 1;
    $instance['show_email']       = empty($new_instance['show_email']) ? 0 : 1;
    $instance['show_phone']       = empty($new_instance['show_phone']) ? 0 : 1;
    $instance['show_address']     = empty($new_instance['show_address']) ? 0 : 1;
    $instance['show_remarks']     = empty($new_instance['show_remarks']) ? 0 : 1;
    $instance['show_newsletter']  = empty($new_instance['show_newsletter']) ? 0 : 1;

    $filterJsShow = filter_var($new_instance['js_show'], FILTER_VALIDATE_URL);
    $filterJsSend = filter_var($new_instance['js_send'], FILTER_VALIDATE_URL);

    $instance['js_show']          = ($filterJsShow === false) ? '' : $filterJsShow;
    $instance['js_send']          = ($filterJsSend === false) ? '' : $filterJsSend;

    return $instance;
  }

  /**
  * @desc Display widget form
  *
  * @param array $instance
  * @return void
  */
  public function form($instance)
  {
    $title          = empty($instance['title']) ? '' : esc_attr($instance['title']);
    $yescoKey       = empty($instance['yesco_key']) ? '' : $instance['yesco_key'];
    $placeholder    = empty($instance['placeholder']) ? '' : $instance['placeholder'];
    $actions        = empty($instance['actions']) ? '' : esc_attr($instance['actions']);
    $thanksMsg      = empty($instance['thanks_msg']) ? self::DEFAULT_THANKS_MSG : esc_attr($instance['thanks_msg']);
    $tagObject      = empty($instance['tag_object']) ? '' : esc_attr($instance['tag_object']);
    $tagRelation    = empty($instance['tag_relation']) ? '' : esc_attr($instance['tag_relation']);
    $roleRelation   = empty($instance['role_relation']) ? '' : esc_attr($instance['role_relation']);
    $jsShow         = empty($instance['js_show']) ? '' : esc_attr($instance['js_show']);
    $jsSend         = empty($instance['js_send']) ? '' : esc_attr($instance['js_send']);

    $showFields = array('show_firstname'  => 'Voornaam',
                        'show_lastname'   => 'Achternaam',
                        'show_email'      => 'E-mail',
                        'show_phone'      => 'Telefoon nummer',
                        'show_address'    => 'Adres',
                        'show_remarks'    => 'Opmerkingen',
                        'show_newsletter' => 'Inschrijven nieuwsbrief');

    $roles  = array('Geïnteresseerde',
                    'Reserve optant');

    // Widhet title
    echo '<p>';
      echo '<label for="' . $this->get_field_id('title') . '">' . __('Titel') . ': </label>';
      echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" />';
    echo '</p>';

    // Yes-co Key
    echo '<p>';
      echo '<label for="' . $this->get_field_id('yesco_key') . '">' . __('Yes-co key') . ': </label>';
      echo '<input class="widefat" id="' . $this->get_field_id('yesco_key') . '" name="' . $this->get_field_name('yesco_key') . '" type="text" value="' . $yescoKey . '" />';
      echo '<small>' . __('Te achterhalen in Yes-co App Market') . '</small>';
    echo '</p>';

    // Placeholder?
    $show = empty($placeholder) ? false : true;
    echo '<p>';
      echo '<label for="' . $this->get_field_id('placeholder') . '">' . __('Toon labels in velden') . ': </label>';
      echo '<input id="' . $this->get_field_id('placeholder') . '" name="' . $this->get_field_name('placeholder') . '" type="checkbox" value="1" ' . ($show === true ? 'checked="checked" ' : '') . '/>';
    echo '</p>';

    // Fields to show
    echo '<strong>Tonen</strong>';
    echo '<table>';
    foreach ($showFields as $field => $label)
    {
      $show = empty($instance[$field]) ? false : true;
		  echo '<tr>';
        echo '<td><label for="' . $this->get_field_id($field) . '">' . __($label) . '</label>: </td>';
        echo '<td><input id="' . $this->get_field_id($field) . '" name="' . $this->get_field_name($field) . '" type="checkbox" value="1" ' . ($show === true ? 'checked="checked" ' : '') . '/></td>';
      echo '</tr>';
    }
    echo '</table>';

    // Actions to use
    echo '<p>';
      echo '<label for="' . $this->get_field_id('actions') . '"><strong>' . __('Acties') . '</strong>&nbsp;<small>(1 actie per regel)</small></label>';
      echo '<textarea name="' . $this->get_field_name('actions') . '" id="' . $this->get_field_id('actions') . '" class="widefat">' . $actions . '</textarea>';
    echo '</p>';

    // Thank you message to show
    echo '<p>';
      echo '<label for="' . $this->get_field_id('thanks_msg') . '"><strong>' . __('Formulier verstuurd boodschap') . '</strong></label>';
      echo '<textarea name="' . $this->get_field_name('thanks_msg') . '" id="' . $this->get_field_id('thanks_msg') . '" class="widefat">' . $thanksMsg . '</textarea>';
    echo '</p>';

    // Tags / role
    echo '<p>';
      echo '<strong>Koppelen</strong>&nbsp;<small>(in Yes-co systeem)</small><br />';
      echo '<label for="' . $this->get_field_id('tag_object') . '">Tag aan object: </label>';
      echo '<input class="widefat" id="' . $this->get_field_id('tag_object') . '" name="' . $this->get_field_name('tag_object') . '" type="text" value="' . $tagObject . '" />';
      echo '<label for="' . $this->get_field_id('tag_relation') . '">Tag aan relatie: </label>';
      echo '<input class="widefat" id="' . $this->get_field_id('tag_relation') . '" name="' . $this->get_field_name('tag_relation') . '" type="text" value="' . $tagRelation . '" />';
      echo '<label for="' . $this->get_field_id('role_relation') . '">Relatie aan object als rol: </label><br />';
      echo '<select name="' . $this->get_field_name('role_relation') . '" id="' . $this->get_field_id('role_relation') . '">';
        echo '<option value=""></option>';
        foreach ($roles as $role)
        {
          echo '<option value="' . $role . '"' . ($roleRelation == $role ? ' selected="selected"' : '') . '>' . $role . '</option>';
        }
      echo '</select>';
    echo '</p>';

    // Javascript
    echo '<p>';
      echo '<strong>Javascript</strong>&nbsp;<small>(URL inladen)</small><br />';
      echo '<label for="' . $this->get_field_id('js_show') . '">Bij tonen formulier: </label>';
      echo '<input class="widefat" id="' . $this->get_field_id('js_show') . '" name="' . $this->get_field_name('js_show') . '" type="text" value="' . $jsShow . '" />';
      echo '<label for="' . $this->get_field_id('js_send') . '">Bij versturen van formulier: </label>';
      echo '<input class="widefat" id="' . $this->get_field_id('js_send') . '" name="' . $this->get_field_name('js_send') . '" type="text" value="' . $jsSend . '" />';
    echo '</p>';

    if (!empty($this->number) && is_numeric($this->number))
      echo '<p>Shortcode: [yog-widget type="contact" id="' . $this->number . '"]</p>';
  }
}