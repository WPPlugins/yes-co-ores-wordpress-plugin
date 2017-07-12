<?php
/**
* @desc YogAddressSearchFormWidget
* @author Kees Brandenburg - Yes-co Nederland
*/
class YogAddressSearchFormWidget extends WP_Widget
{
  const NAME                = 'Yes-co Adres Zoeken';
  const DESCRIPTION         = 'Zoekformulier om aan de hand van 1 tekst veld objecten te zoeken aan de hand van het adres.';
  const CLASSNAME           = 'yog-address-search';

  /**
  * @desc Constructor
  *
  * @param void
  * @return YogAddressSearchFormWidget
  */
  public function __construct()
  {
    $options = array( 'classsname'  => self::CLASSNAME,
                      'description' => self::DESCRIPTION);

    parent::__construct(false, $name = self::NAME, $options);
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
    $objectType     = empty($instance['object_type']) ? 'all' : esc_attr($instance['object_type']);
    $searchValue    = empty($_REQUEST['s']) ? '' : esc_attr($_REQUEST['s']);

    echo $args['before_widget'];
    if (!empty($title))
      echo $args['before_title'] . $title . $args['after_title'];

    echo '<form role="search" method="get" class="searchform ' . self::CLASSNAME . '" id="yog-address-search-form-widget" action="' . get_bloginfo('url') . '/">';
      echo '<input type="hidden" name="object_type" value="' . $objectType . '" />';

      if (locate_template('searchform-object.php') != '')
      {
        get_template_part('searchform', 'object');
      }
      else
      {
        echo '<div>';
          echo '<label class="screen-reader-text" for="s">' . _x( 'Search for:', 'label' ) . ':</label>';
          echo '<input type="text" value="' . $searchValue . '" name="s" id="s" />';
          echo '<input type="submit" id="searchsubmit" value="' . esc_attr_x( 'Search', 'submit button' ) . '" />';
        echo '</div>';
      }
    echo '</form>';

    echo $args['after_widget'];
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
    $instance                 = $old_instance;
    $instance['title']        = empty($new_instance['title']) ? '' : $new_instance['title'];
    $instance['object_type']  = empty($new_instance['object_type']) ? '' : $new_instance['object_type'];

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
    $objectType     = empty($instance['object_type']) ? 'all' : esc_attr($instance['object_type']);

    $availableObjectTypes = array(POST_TYPE_WONEN  => 'Woningen',
                                  POST_TYPE_BOG     => 'Bedrijfs onroerend goed',
                                  POST_TYPE_NBPR    => 'Nieuwbouw projecten',
                                  POST_TYPE_BBPR    => 'Bestaande bouw projecten');

    // Widget title
    echo '<p>';
      echo '<label for="' . $this->get_field_id('title') . '">' . __('Titel') . ': </label>';
      echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" />';
    echo '</p>';

    // Object Type
    echo '<p>';
      echo '<label for="' . $this->get_field_id('object_type') . '">Soort objecten: </label>';
      echo '<select name="' . $this->get_field_name('object_type') . '" id="' . $this->get_field_id('object_type') . '">';
        echo '<option value="">Alle objecten</option>';
        foreach ($availableObjectTypes as $availableObjectType => $label)
        {
          echo '<option value="' . $availableObjectType . '"' . ($objectType == $availableObjectType ? ' selected="selected"' : '') . '>' . $label . '</option>';
        }
      echo '</select>';
    echo '</p>';
  }
}