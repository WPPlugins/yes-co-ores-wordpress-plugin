<?php
/**
* @desc YogRecentObjectsWidget
* @author Kees Brandenburg - Yes-co Nederland
*/
class YogRecentObjectsWidget extends WP_Widget
{
  const NAME              = 'Yes-co Recente objecten';
  const DESCRIPTION       = 'De laatst gepubliceerde objecten';
  const CLASSNAME         = 'yog-recent-list';
  const DEFAULT_LIMIT     = 5;
  const DEFAULT_IMG_SIZE  = 'thumbnail';

  /**
  * @desc Constructor
  *
  * @param void
  * @return YogRecentObjectsWidget
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
		global $wpdb;

    $title									= apply_filters('widget_title', $instance['title']);
    $limit									= empty($instance['limit']) ? self::DEFAULT_LIMIT : (int) $instance['limit'];
    $imgSize								= empty($instance['img_size']) ? self::DEFAULT_IMG_SIZE : $instance['img_size'];
    $postTypes							= $this->determinePostTypes($instance);
		$nochildsSearchresults	= get_option('yog_nochilds_searchresults');

		// Create SQL to retrieve posts
		$sql = 'SELECT * FROM ' . $wpdb->posts  . ' WHERE post_type IN (\'' . implode('\',\'', $postTypes) . '\') ';
    if (!empty($nochildsSearchresults))
      $sql .= 'AND (post_type != \'' . POST_TYPE_WONEN . '\' OR post_parent = 0) ';
    $sql .= 'AND post_status = \'publish\'';
		$sql .= 'ORDER BY post_date DESC LIMIT ' . $limit;

		// Retrieve posts
		$posts = $wpdb->get_results($sql, OBJECT);

		if (!empty($posts))
		{
			$customTemplate = locate_template('object-recent.php');

			// Enqueue styles
			if (!(empty($customTemplate) || !file_exists(get_template_directory() . '/recent_objects.css')))
			{
				$minifyExtension = (YOG_DEBUG_MODE === true) ? '' : '.min';
				wp_enqueue_style('yog-recent-object', YOG_PLUGIN_URL . '/inc/css/recent_objects' . $minifyExtension . '.css');
			}

			echo $args['before_widget'];
			if (!empty($title))
				echo $args['before_title'] . $title . $args['after_title'];

			// Handle posts with default styling
			if ($customTemplate == '')
			{
				echo '<div class="recent-objects">';

				foreach ($posts as $post)
				{
					$images     = yog_retrieveImages($imgSize, 1, $post->ID);
					$title      = yog_retrieveSpec('Naam', $post->ID);
					$link       = get_permalink($post->ID);
					$prices     = yog_retrievePrices('recent-price-label', 'recent-price-specification', $post->ID);
					$openHouse  = yog_getOpenHouse('Open huis', $post->ID);
					$city       = yog_retrieveSpec('Plaats', $post->ID);

					echo '<div class="recent-object">';
						// Image
						if (!empty($images))
						{
							echo '<div class="recent-img">';
								echo '<a href="' . $link . '" rel="bookmark" title="' . $title . '">';
									echo '<img src="' . $images[0][0] . '" width="' . $images[0][1] . '" height="' . $images[0][2] . '" alt="' . $title . '" />';
								echo '</a>';
							echo '</div>';
						}

						echo '<h2><a href="' . $link . '" rel="bookmark" title="' . $title . '">' . $title . '</a></h2>';
						echo '<h3><a href="' . $link . '" rel="bookmark" title="' . $title . '">' . $city . '</a></h3>';

						// Prices
						if (!empty($prices))
						{
							echo '<div class="recent-prices">';
							foreach ($prices as $price)
							{
								echo '<div class="recent-price">' . $price . '</div>';
							}
							echo '</div>';
						}
						// Open house
						if (!empty($openHouse))
							echo '<div class="recent-open-house">' . $openHouse . '</div>';

					echo '</div>';
				}

				echo '</div>';
			}
			// Use template file from theme for posts
			else
			{
				// Backup original post
				global $post;
        if (!empty($post))
          $orgPost	= clone $post;

				// Call template for each recent post
				foreach ($posts as $post)
				{
					setup_postdata($post);
					include($customTemplate);	// Include the template, instead of using get_template_part so variables are also useable
				}

				// Restore original post
        if (!empty($orgPost))
        {
          $post = $orgPost;
          setup_postdata($orgPost);
        }
			}

      do_action('yog_recent_objects_widget_after', $instance);

			echo $args['after_widget'];
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
    // Determine post types
    $postTypes              = array();
    if (!empty($new_instance['post_type_' . POST_TYPE_WONEN]))
      $postTypes[]          = POST_TYPE_WONEN;
    if (!empty($new_instance['post_type_' . POST_TYPE_BOG]))
      $postTypes[]          = POST_TYPE_BOG;
    if (!empty($new_instance['post_type_' . POST_TYPE_NBPR]))
      $postTypes[]          = POST_TYPE_NBPR;
    if (!empty($new_instance['post_type_' . POST_TYPE_NBTY]))
      $postTypes[]          = POST_TYPE_NBTY;

    $instance               = $old_instance;
    $instance['title']      = empty($new_instance['title']) ? '' : $new_instance['title'];
    $instance['img_size']   = empty($new_instance['img_size']) ? self::DEFAULT_IMG_SIZE : $new_instance['img_size'];
    $instance['post_types'] = implode(',', $postTypes);
    if (!empty($new_instance['limit']) && ctype_digit($new_instance['limit']))
      $instance['limit']    = (int) $new_instance['limit'];

    // Widget settings storage is extendible by a theme or other plugin
    $instance = apply_filters('yog_recent_objects_widget_update', $instance, $new_instance);

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
    $title              = empty($instance['title']) ? '' : esc_attr($instance['title']);
    $limit              = empty($instance['limit']) ? self::DEFAULT_LIMIT : (int) $instance['limit'];
    $imgSize            = empty($instance['img_size']) ? self::DEFAULT_IMG_SIZE : $instance['img_size'];
    $postTypes          = $this->determinePostTypes($instance);

    $supportedPostTypes = array(POST_TYPE_WONEN => 'Wonen', POST_TYPE_BOG => 'BOG', POST_TYPE_NBPR => 'Nieuwbouw projecten', POST_TYPE_NBTY => 'Nieuwbouw types');

    echo '<p>';
      echo '<label for="' . $this->get_field_id('title') . '">' . __('Titel') . ': </label>';
      echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" />';
    echo '</p>';

    echo '<p>';
      echo '<label>' . __('Ondersteunde objecten') . ': </label><br />';
      foreach ($supportedPostTypes as $postType => $label)
      {
        $id   = $this->get_field_id('post_type_' . $postType);
        $name = $this->get_field_name('post_type_' . $postType);
        echo '<input type="checkbox" name="' . $name . '" value="' . $postType . '" id="' . $id . '"' . (in_array($postType, $postTypes) ? ' checked="checked"' : '') . ' /> <label for="' . $id . '">' . $label . '</label><br />';
      }
    echo '</p>';

    echo '<p>';
      echo '<label for="' . $this->get_field_id('img_size') . '">' . __('Formaat afbeeldingen') . ': </label>';
      echo '<select id="' . $this->get_field_id('img_size') . '" name="' . $this->get_field_name('img_size') . '">';
      foreach (get_intermediate_image_sizes() as $size)
      {
        echo '<option value="' . $size . '"' . (($size == $imgSize) ? ' selected="selected"' : '') . '>' . __(ucfirst($size)) . '</option>';
      }
      echo '</select>';
    echo '</p>';

		echo '<p>';
      echo '<label for="' . $this->get_field_id('limit') . '">' . __('Aantal te tonen objecten') . ': </label>';
      echo '<input id="' . $this->get_field_id('limit') . '" name="' . $this->get_field_name('limit') . '" type="text" value="' . $limit . '" size="3" maxlength="1" />';
    echo '</p>';

    // Widget settings are extendible by a theme or other plugin
    do_action('yog_recent_objects_widget_after_settings', $this, $instance);
  }

  /**
  * @desc Determine configured post types
  *
  * @param array $instance
  * @return array
  */
  private function determinePostTypes($instance)
  {
    $postTypes          = array(POST_TYPE_WONEN);
    if (isset($instance['post_types']))
      $postTypes        = empty($instance['post_types']) ? array() : explode(',', $instance['post_types']);

    return $postTypes;
  }
}