<?php
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_fields_settings.php');

  /**
  * @desc Check if post is an object
  *
  * @param int $postId (optional)
  * @return bool
  */
  function yog_isObject($postId = null)
  {
    if (is_null($postId))
      $postId = get_the_ID();

    $postType = get_post_type($postId);

    return in_array($postType, array(POST_TYPE_WONEN, POST_TYPE_BOG, POST_TYPE_NBPR, POST_TYPE_NBTY, POST_TYPE_NBBN, POST_TYPE_BBPR, POST_TYPE_BBTY));
  }

  /**
  * @desc Get the address of an object
  *
  * @param int $postId (optional)
  * @return string
  */
  function yog_getAddress($postId = null)
  {
    $specs   = yog_retrieveSpecs(array('Straat', 'Huisnummer', 'Plaats'), $postId);

    return implode(' ', $specs);
  }

  /**
  * @desc Retrieve specs of an obect
  *
  * @param array specs
  * @param int $postId (optional)
  * @param bool $returnTitle (optional, return title as key instead of spec name, default true)
  * @return array
  */
  function yog_retrieveSpecs($specs, $postId = null, $returnTitle = true)
  {
    if (!is_array($specs))
      throw new Exception(__METHOD__ . '; Invalid specs provided, must be an array');

    if (is_null($postId))
      $postId = get_the_ID();

    $postType       = get_post_type($postId);
    $values         = array();

    if (!empty($postType) && in_array($postType, array(POST_TYPE_WONEN, POST_TYPE_BOG, POST_TYPE_NBPR, POST_TYPE_NBTY, POST_TYPE_NBBN, POST_TYPE_BBPR, POST_TYPE_BBTY, POST_TYPE_RELATION)))
    {
      $fieldsSettings = YogFieldsSettingsAbstract::create($postType);

      foreach ($specs as $spec)
      {
        $postMetaName = $postType . '_' . $spec;

        if (strpos($postMetaName, 'MinMax') !== false)
        {
          $minValue     = get_post_meta($postId, str_replace('Max', '', $postMetaName), true);
          $maxValue     = get_post_meta($postId, str_replace('Min', '', $postMetaName), true);
          $value        = '';

          if (!empty($minValue))
            $value .= number_format($minValue, 0, ',', '.');
          if (!empty($maxValue))
            $value .= ' t/m ' . number_format($maxValue, 0, ',', '.');

          if (!empty($value))
          {
            if ($fieldsSettings->containsField($postMetaName))
            {
              $settings = $fieldsSettings->getField($postMetaName);

              if (!empty($settings['type']))
              {
                switch ($settings['type'])
                {
                  case 'oppervlakte':
                    $value .= ' m&sup2;';
                    break;
                  case 'inhoud':
                    $value .= ' m&sup3;';
                    break;
                  case 'cm':
                    $value .= ' cm';
                    break;
                  case 'meter':
                    $value  .- ' m';
                    break;
                }
              }

              if (!empty($settings['title']) && $returnTitle !== false)
                $spec = $settings['title'];
            }

            $values[$spec] = $value;
          }
        }
        else if ($spec == 'ParentLink')
        {
          if (yog_hasParentObject($postId))
          {
            $parent = yog_retrieveParentObject($postId);
            $url    = get_permalink($parent->ID);
            $title  = get_the_title($parent->ID);

            if ($returnTitle !== false && $fieldsSettings->containsField($postMetaName))
            {
              $settings = $fieldsSettings->getField($postMetaName);
              if (!empty($settings['title']))
                $spec = $settings['title'];
            }

            $values[$spec] = '<a href="' . $url . '" title="' . $title . '">' . $title . '</a>';
          }
        }
        else
        {
          $value = get_post_meta($postId, $postMetaName, true);

          if (!empty($value) && strlen(trim($value)) > 0)
          {
            // Transform value
            if ($fieldsSettings->containsField($postMetaName))
            {
              $settings = $fieldsSettings->getField($postMetaName);

              if (!empty($settings['type']))
              {
                switch ($settings['type'])
                {
                  case 'oppervlakte':
                    $value = number_format($value, 0, ',', '.') . ' m&sup2;';
                    break;
                  case 'inhoud':
                    $value = number_format($value, 0, ',', '.') . ' m&sup3;';
                    break;
                  case 'cm':
                    $value = number_format($value, 0, ',', '.') . ' cm';
                    break;
                  case 'meter':
                    $value = number_format($value, 0, ',', '.') . ' m';
                    break;
                  case 'date':
                    $dateTime = new DateTime($value);
                    $value = $dateTime->format('d-m-Y');
                    break;
                  case 'price':
                  case 'priceBtw':

                    $value = '&euro; ' . number_format($value, 0, ',', '.') . ',-';

                    break;
                }
              }

              if (!empty($settings['addition']))
                $value .= $settings['addition'];

              if (!empty($settings['title']) && $returnTitle !== false)
                $spec = $settings['title'];
            }

            $values[$spec] = $value;
          }
        }
      }
    }

    return $values;
  }

  /**
  * @desc Retrieve spec of an obect
  *
  * @param string $spec
  * @param int $postId (optional)
  * @return string
  */
  function yog_retrieveSpec($spec, $postId = null)
  {
    if (!is_string($spec) || strlen(trim($spec)) == 0)
      throw new Exception(__METHOD__ . '; Invalid spec, must be a non empty string');

    $values = yog_retrieveSpecs(array($spec), $postId);

    return array_shift($values);
  }

  /**
  * @desc Retrieve project prices
  *
  * @param string $priceTypeClass (default: priceType)
  * @param string $priceConditionClass (default: priceCondition)
  * @param int $postId (optional)
  * @param string $labelElem (optional, default span)
  * @param string $valueElem (optional, default none)
  * @return array
  */
  function yog_retrievePrices($priceTypeClass = 'priceType', $priceConditionClass = 'priceCondition', $postId = null, $labelElem = 'span', $valueElem = '')
  {
    $values         = array();
    $postType       = get_post_type(is_null($postId) ? false : $postId);

    if (!empty($priceTypeClass) && empty($labelElem))
      $labelElem = 'span';

    $labelElemStart = empty($labelElem) ? '' : '<' . $labelElem . (empty($priceTypeClass) ? '' : ' class="' . $priceTypeClass . '"') . '>';
    $labelElemEnd   = empty($labelElem) ? '' : '</' . $labelElem . '>';
    $valueElemStart = empty($valueElem) ? ' ' : '<' . $valueElem . '>';
    $valueElemEnd   = empty($valueElem) ? '' : '</' . $valueElem . '>';

    switch ($postType)
    {
      case POST_TYPE_NBPR:
        $priceMinMaxTypes = array('KoopAanneemSom' => 'Aanneemsom', 'HuurPrijs' => 'Huurprijs');
        break;
      case POST_TYPE_NBTY:
      case POST_TYPE_BBPR:
      case POST_TYPE_BBTY:
        $priceMinMaxTypes = array('KoopPrijs' => 'Koopprijs', 'HuurPrijs' => 'Huurprijs');
        break;
      default:
        $priceFields      = array('KoopPrijs', 'HuurPrijs');
        break;
    }

    if (!empty($priceMinMaxTypes))
    {
      foreach ($priceMinMaxTypes as $priceType => $label)
      {
        $minField = $priceType . 'Min';
        $maxField = $priceType . 'Max';
        $min      = yog_retrieveSpec($minField, $postId);
        $max      = yog_retrieveSpec($maxField, $postId);
        $value    = '';

        if (!empty($min) && !empty($max))
          $value = $min . ' t/m ' . $max;
        else if (!empty($min) && empty($max))
          $value = 'vanaf ' . $min;
        else if (!empty($max))
          $value = 't/m ' . $max;

        if (!empty($value))
        {
          $priceCondition = yog_retrieveSpec($priceType . 'Conditie', $postId);
          if (!empty($priceCondition))
            $value .= ' <span class="' . $priceConditionClass . '">' . $priceCondition . '</span>';

          $values[] = $labelElemStart . $label . ': ' . $labelElemEnd . $valueElemStart . $value . $valueElemEnd;
        }
      }
    }
    else if (!empty($priceFields))
    {
      foreach ($priceFields as $field)
      {
        $replace        = yog_retrieveSpec($field . 'Vervanging', $postId);
        $priceType      = ($field == 'HuurPrijs') ? 'Huurprijs' : yog_retrieveSpec($field . 'Soort', $postId);

        if (empty($priceType))
          $priceType = 'Vraagprijs';

        $price          = yog_retrieveSpec($field, $postId);

        // Some realtors synchronise a value of 1,- to Funda, replace it with prijs op aanvraag in case it occurs
        if (empty($replace) && (preg_match('/ 1,\-/', $price) || preg_match('/99\.999\.999,\-/', $price)))
          $replace = 'op aanvraag';

        if (empty($replace))
        {
          if (!empty($price))
          {
            $priceCondition = yog_retrieveSpec($field . 'Conditie', $postId);
            $value = $labelElemStart . $priceType . ': ' . $labelElemEnd . $valueElemStart . $price . (empty($priceCondition) ? '' : ' <span class="' . $priceConditionClass . '">' . $priceCondition . '</span>');

            if ($postType == POST_TYPE_BOG)
            {
              $btw = yog_retrieveSpec($field . 'BtwPercentage', $postId);
              if (!empty($btw))
                $value .= ' <span class="priceBtw">(' . $btw . '% BTW)</span>';
            }

            $values[] = $value . $valueElemEnd;
          }
        }
        else
        {
          $values[] = $labelElemStart . $priceType . ': ' . $labelElemEnd . $valueElemStart . $replace . $valueElemEnd;
        }
      }
    }

    return $values;
  }

  /**
  * @desc Check if object has a parent object
  *
  * @param $postId (optional, default: ID of current post)
  * @return bool
  */
  function yog_hasParentObject($postId = null)
  {
    $ancestorIds = get_post_ancestors($postId);
    return (is_array($ancestorIds) && count($ancestorIds) > 0);
  }

  /**
  * @desc Get the parent object id
  *
  * @param $postId (optional, default: ID of current post)
  * @return mixed (integer parent object id or false)
  */
  function yog_getParentObjectId($postId = null)
  {
    $ancestorIds = get_post_ancestors($postId);

    if (is_array($ancestorIds) && count($ancestorIds) > 0)
    {
      $parentId = array_shift($ancestorIds);
      return (int) $parentId;
    }

    return false;
  }

  /**
  * @desc Retrieve the parent object
  *
  * @param $postId (optional, default: ID of current post)
  * @return mixed (integer parent object or false)
  */
  function yog_retrieveParentObject($postId = null)
  {
    $parentId = yog_getParentObjectId($postId);
    if ($parentId !== false)
      return get_post($parentId);

    return false;
  }

  /**
  * @desc Check if object has children
  *
  * @param $postId (optional, default: ID of current post)
  * @return bool
  */
  function yog_hasChildObjects($postId = null)
  {
	  if (is_null($postId))
		  $postId = get_the_ID();

    $childs = get_posts(array('numberposts'     => 1,
                              'offset'          => 0,
                              'post_parent'     => $postId,
                              'post_type'       => array(POST_TYPE_NBTY, POST_TYPE_BBTY, POST_TYPE_NBBN, POST_TYPE_WONEN),
                              'post_status'     => array('publish')));

    return (is_array($childs) && count($childs) > 0);
  }

  /**
  * @desc Get the child objects
  *
  * @param $postId (optional, default: ID of current post)
  * @return array
  */
  function yog_retrieveChildObjects($postId = null)
  {
	  if (is_null($postId))
		  $postId = get_the_ID();

    return get_posts(array( 'numberposts'     => -1,
                            'offset'          => 0,
                            'orderby'         => 'title',
                            'order'           => 'ASC',
                            'post_parent'     => $postId,
                            'post_type'       => array(POST_TYPE_NBTY, POST_TYPE_BBTY, POST_TYPE_NBBN, POST_TYPE_WONEN),
                            'post_status'     => array('publish')));
  }

  /**
  * @desc Get the child NBbn objects
  *
  * @param $postId (optional, default: ID of current post)
  * @return array
  */
  function yog_retrieveChildNBbnObjects($postId = null)
  {
	  if (is_null($postId))
		  $postId = get_the_ID();

    return get_posts(array( 'numberposts'     => -1,
                            'offset'          => 0,
                            'orderby'         => 'title',
                            'order'           => 'ASC',
                            'post_parent'     => $postId,
                            'post_type'       => array(POST_TYPE_NBBN),
                            'post_status'     => array('publish')));
  }

  /**
  * @desc Get HTML for a table with all NBbn objects
  *
  * @param $postId (optional, default: ID of current post)
  * @return string
  */
  function yog_retrieveNbbnTable($postId = null)
  {
    $childs = yog_retrieveChildNBbnObjects();
    $html   = '';

    if (is_array($childs) && count($childs) > 0)
    {
      $html .= '<table class="yog-nbbn-table sorttable">';
        $html .= '<thead>';
          $html .= '<tr>';
            $html .= '<th class="yog-nbbn-bouwnr">Bouwnummer</th>';
            $html .= '<th class="yog-nbbn-woonopp">Woon opp.</th>';
            $html .= '<th class="yog-nbbn-perceelopp">Perceel opp.</th>';
            $html .= '<th class="yog-nbbn-grondprijs">Grond prijs</th>';
            $html .= '<th class="yog-nbbn-aanneemsom">Aanneemsom</th>';
            $html .= '<th class="yog-nbbn-koopaanneemsom">Koop aanneemsom</th>';
            $html .= '<th class="yog-nbbn-status">Status</th>';
          $html .= '</tr>';
        $html .= '<thead>';
        $html .= '<tbody>';

        foreach ($childs as $child)
        {
          $specs  = yog_retrieveSpecs(array('Naam', 'WoonOppervlakte', 'PerceelOppervlakte', 'GrondPrijs', 'AanneemSom', 'KoopAanneemSom', 'Status'), $child->ID);

          $name   = '';
          if (!empty($specs['Titel van object']) && strpos($specs['Titel van object'], '/') !== false)
          {
            $nameParts  = explode('/', $specs['Titel van object']);
            $name       = array_pop($nameParts);
          }

          $html .= '<tr>';
            $html .= '<td class="yog-nbbn-bouwnr">' . $name . '</td>';
            $html .= '<td class="yog-nbbn-woonopp">' . (empty($specs['Woon oppervlakte']) ? '' : $specs['Woon oppervlakte']) . '</td>';
            $html .= '<td class="yog-nbbn-perceelopp">' . (empty($specs['Perceel oppervlakte']) ? '' : $specs['Perceel oppervlakte']) . '</td>';
            $html .= '<td class="yog-nbbn-grondprijs">' . (empty($specs['Grond prijs']) ? '' : $specs['Grond prijs']) . '</td>';
            $html .= '<td class="yog-nbbn-aanneemsom">' . (empty($specs['Aanneemsom']) ? '' : $specs['Aanneemsom']) . '</td>';
            $html .= '<td class="yog-nbbn-koopaanneemsom">' . (empty($specs['Koop aanneemsom']) ? '' : $specs['Koop aanneemsom']) . '</td>';
            $html .= '<td class="yog-nbbn-status">' . (empty($specs['Status']) ? '' : $specs['Status']) . '</td>';
          $html .= '</tr>';
        }

        $html .= '</tbody>';
      $html .= '</table>';
    }

    return $html;
  }

  /**
  * @desc Retrieve linked relations
  *
  * @param $postId (optional, default: ID of current post)
  * @return array
  */
  function yog_retrieveRelations($postId = null)
  {
	  if (is_null($postId))
		  $postId   = get_the_ID();

    $postType   = get_post_type($postId);

    $relations      = get_post_meta($postId, $postType . '_Relaties',true);
    $relationPosts  = array();

    if (!empty($relations))
    {
	    foreach ($relations as $uuid => $relation)
	    {
	      $relationId = (int) $relation['postId'];
	      $role       = $relation['rol'];

	      $relationPosts[$role] = get_post($relationId);
	    }
    }

    return $relationPosts;
  }

  /**
  * @desc Retrieve linked relation with a specific role
  *
  * @param string $role
  * @param $postId (optional, default: ID of current post)
  * @return mixed (WP_Post or null)
  */
  function yog_retrieveRelationByRole($role, $postId = null)
  {
    if (!is_string($role) || strlen(trim($role)) == 0)
      throw new \Exception(__METHOD__ . '; Invalid role, must be a non empty string');

	  if (is_null($postId))
		  $postId   = get_the_ID();

    $postType   = get_post_type($postId);

    $relations      = get_post_meta($postId, $postType . '_Relaties',true);

    if (!empty($relations))
    {
	    foreach ($relations as $uuid => $relation)
	    {
        if ($relation['rol'] == $role)
        {
          $relationId = (int) $relation['postId'];
          return get_post($relationId);
        }
	    }
    }
  }

  /**
  * @desc Retrieve links for a post
  *
  * @param $postId (optional, default: ID of current post)
  * @return array
  */
  function yog_retrieveLinks($postId = null)
  {
	  if (is_null($postId))
		  $postId = get_the_ID();

    $postType = get_post_type($postId);

	  $links    = get_post_meta($postId, $postType . '_Links',true);
	  return $links;
  }

  /**
  * @desc Retrieve documents for a post
  *
  * @param $postId (optional, default: ID of current post)
  * @return array
  */
  function yog_retrieveDocuments($postId = null)
  {
	  if (is_null($postId))
		  $postId   = get_the_ID();

    $postType   = get_post_type($postId);
	  $documenten = get_post_meta($postId, $postType . '_Documenten',true);
	  return $documenten;
  }

  /**
  * @desc Retrieve movies for a post
  *
  * @param $postId (optional, default: ID of current post)
  * @return array
  */
  function yog_retrieveMovies($postId = null)
  {
    if (is_null($postId))
		  $postId = get_the_ID();

    $postType = get_post_type($postId);
	  $videos   = get_post_meta($postId, $postType . '_Videos',true);

    if (!empty($videos))
    {
      foreach ($videos as $uuid => $video)
      {
        $videos[$uuid]['type'] = 'other';

        if (!empty($video['videoereference_serviceuri']))
        {
          switch ($video['videoereference_serviceuri'])
          {
            case 'http://www.youtube.com/':
            case 'http://www.youtube.com':

              $videos[$uuid]['type']                        = 'youtube';
              $videos[$uuid]['videoereference_serviceuri']  = 'http://www.youtube.com';

              if (empty($videos[$uuid]['videoereference_id']) && !empty($videos[$uuid]['websiteurl']))
              {
                $chunks = @parse_url($videos[$uuid]['websiteurl'], PHP_URL_QUERY);
                if (!empty($chunks))
                {
                  parse_str($chunks, $params);
                  if (!empty($params['v']))
                    $videos[$uuid]['videoereference_id'] = $params['v'];
                }
              }

              if (!empty($videos[$uuid]['videoereference_id']))
              {
                $videos[$uuid]['websiteurl']      = 'http://www.youtube.com/watch?v=' . $videos[$uuid]['videoereference_id'];
                $videos[$uuid]['videostreamurl']  = 'http://www.youtube.com/embed/' . $videos[$uuid]['videoereference_id'];
              }

              break;
            case 'http://vimeo.com/':
            case 'http://vimeo.com':

              $videos[$uuid]['type']                        = 'vimeo';
              $videos[$uuid]['videoereference_serviceuri']  = 'http://vimeo.com';

              if (!empty($videos[$uuid]['videoereference_id']))
              {
                $videos[$uuid]['websiteurl']      = 'http://vimeo.com/' . $videos[$uuid]['videoereference_id'];
                $videos[$uuid]['videostreamurl']  = 'http://player.vimeo.com/video/' . $videos[$uuid]['videoereference_id'];
              }

              break;
            case 'http://www.flickr.com/':
            case 'http://www.flickr.com':

              $videos[$uuid]['type']                        = 'flickr';
              $videos[$uuid]['videoereference_serviceuri']  = 'http://www.flickr.com';
              break;
          }
        }
      }
    }

	  return $videos;
  }

  /**
  * @desc Retrieve embeded movies
  *
  * @param $postId (optional, default: ID of current post)
  * @return array
  */
  function yog_retrieveEmbedMovies($postId = null)
  {
	  $movies       = yog_retrieveMovies($postId);
    $embedMovies  = array();

    if (!empty($movies))
    {
      foreach ($movies as $uuid => $movie)
      {
        if (!empty($movie['videostreamurl']) && !empty($movie['videoereference_serviceuri']))
        {
          $embedMovies[$uuid] = $movie;
        }
      }
    }

	  return $embedMovies;
  }

  /**
  * @desc Retrieve non-embeded movies
  *
  * @param int $postId (optional, default: ID of current post)
  * @return array
  */
  function yog_retrieveExternalMovies($postId = null)
  {
	  $movies         = yog_retrieveMovies($postId);

    $externalMovies = array();

    if (!empty($movies))
    {
      foreach ($movies as $uuid => $movie)
      {
        if (empty($movie['videostreamurl']) || empty($movie['videoereference_serviceuri']))
        {
          $externalMovies[$uuid] = $movie;
        }
      }
    }

    return $externalMovies;
  }

  /**
  * Get embed code fot a specific movie
  *
  * @param array $movie
  * @param int $width
  * @param int $height
  * @return string
  */
  function yog_getMovieEmbedCode($movie, $width, $height, $class = null)
  {
    $code = '';

    // Determine embed code
    if (is_array($movie) && !empty($movie['videoereference_serviceuri']) && !empty($movie['videostreamurl']))
    {
      switch ($movie['videoereference_serviceuri'])
      {
        case 'http://www.youtube.com':
          $code = '<iframe class="youtube-player" type="text/html" width="' . $width . '" height="' . $height . '" src="' . $movie['videostreamurl'] . '" allowfullscreen frameborder="0"' . (empty($class) ? ' class="' . $class . '"' : '') . '></iframe>';
          break;
        case 'http://vimeo.com':
          $code = '<iframe src="' . $movie['videostreamurl'] . '" width="' . $width . '" height="' . $height . '" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen'  . (empty($class) ? ' class="' . $class . '"' : '') . '></iframe>';
          break;
        default:
          $code = '<iframe src="' . $movie['videostreamurl'] . '" width="' . $width . '" height="' . $height . '" frameborder="0"'  . (empty($class) ? ' class="' . $class . '"' : '') . '></iframe>';
          break;
      }
    }

    return $code;
  }

  /**
  * @desc Retrieve dossier items
  *
  * @param int $limit
  * @param int $postId (optional)
  * @return array
  */
  function yog_retrieveDossierItems($limit = null, $postId = null)
  {
    if (is_null($postId))
      $postId = get_the_ID();

    $items      = array();
    $mimeTypes  = array_filter(explode(';', get_option('yog_dossier_mimetypes', 'application/pdf')));

    if (is_array($mimeTypes) && count($mimeTypes) > 0)
    {
      $arguments = array('post_type'        => 'attachment',
                          'post_parent'     => $postId,
                          'post_mime_type'  => $mimeTypes,
                          'numberposts'     => (is_null($limit) ? -1 : $limit),
                          'orderby'         => 'title',
                          'order'           => 'ASC');

      $posts  = get_posts($arguments);

      foreach ($posts as $post)
      {
        $item = array('url'       => wp_get_attachment_url($post->ID),
                      'title'     => $post->post_title,
                      'mime_type' => $post->post_mime_type);

        $items[] = $item;
      }
    }

    return $items;
  }

  /**
  * @desc Check if an open house route is set (and in future)
  *
  * @param int $postId (optional)
  * @return bool
  */
  function yog_hasOpenHouse($postId = null)
  {
    $openHouseStart = yog_retrieveSpec('OpenHuisVan', $postId);
    if (!empty($openHouseStart))
    {
      $openHouseEnd   = yog_retrieveSpec('OpenHuisTot', $postId);

      if (empty($openHouseEnd))
        $openHouseEnd = strtotime($openHouseStart);
      else
        $openHouseEnd = strtotime($openHouseEnd);

      return ($openHouseEnd >= date('U'));
    }

    return false;
  }

  /**
  * @desc Get the open house date
  *
  * @param string $label (default: Open huis)
  * @param int $postId (optional)
  * @return string
  */
  function yog_getOpenHouse($label = 'Open huis', $postId = null)
  {
    $openHouse = '';
    if (yog_hasOpenHouse($postId))
    {
      $openHouseStart = strtotime(yog_retrieveSpec('OpenHuisVan', $postId));
      return '<span class="label">' . $label . ': </span>' . date('d-m-Y', $openHouseStart);
    }

    return $openHouse;
  }

  /**
  * @desc Retrieve HTML for the main image
  *
  * @param string $size (thumbnail, medium, large)
  * @param int $postId (optional)
  * @return array
  */
  function yog_retrieveMainImage($size, $postId = null)
  {
    if (is_null($postId))
      $postId = get_the_ID();

    $html = get_the_post_thumbnail($postId, $size);

    // Fallback when no post thumbnail is set
    if (empty($html))
    {
      $images = yog_retrieveImages($size, 1, $postId);
      if (!empty($images) && is_array($images) && count($images) > 0)
      {
        $image = $images[0];
        $html = '<img width="' . $image[1] . '" height="' . $image[2] . '" src="' . $image[0] . '" class="attachment-thumbnail wp-post-image" alt=""  />';
      }
    }

    return $html;
  }

  /**
  * @desc Retrieve images
  *
  * @param string $size (thumbnail, medium, large)
  * @param int $limit
  * @param int $postId (optional)
  * @return array
  */
  function yog_retrieveImages($size, $limit = null, $postId = null)
  {
    if (is_null($postId))
      $postId = get_the_ID();

    $arguments = array('post_type'        => 'attachment',
                        'post_parent'     => $postId,
                        'post_mime_type'  => 'image',
                        'numberposts'     => (is_null($limit) ? -1 : $limit),
                        'orderby'         => 'menu_order',
                        'order'           => 'ASC');

    $posts  = get_posts($arguments);
    $images = array();

    foreach ($posts as $post)
    {
      $image    = wp_get_attachment_image_src($post->ID, $size);
      if (empty($image[1]))
        $image[1] = get_option($size . '_size_w', 0);
      if (empty($image[2]))
        $image[2] = get_option($size . '_size_h', 0);

      $images[] = $image;
    }

    return $images;
  }

  /**
  * @desc Check if there are images without type 'Plattegrond'
  *
  * @param int $postId (optional)
  * @return bool
  */
  function yog_hasNormalImages($postId = null)
  {
  	if (is_null($postId))
  		$postId = get_the_ID();

    $found      = false;
    $arguments  = array('post_type'        => 'attachment',
                        'post_parent'     => $postId,
                        'post_mime_type'  => 'image');

    $images     = get_posts($arguments);

    while ($found === false && $image = array_pop($images))
    {
      $type = get_post_meta($images->ID, 'attachment_type', true);
      if ($type != 'Plattegrond')
        $found = true;
    }

    return $found;
  }

  /**
  * @desc Retrieve all images without type 'Plattegrond'
  *
  * @param string $size (thumbnail, medium, large)
  * @param int $limit
  * @param int $postId (optional)
  * @return array
  */
  function yog_retrieveNormalImages($size, $limit = null, $postId = null)
  {
    if (is_null($postId))
      $postId = get_the_ID();

    $arguments = array('post_type'        => 'attachment',
                        'post_parent'     => $postId,
                        'post_mime_type'  => 'image',
                        'numberposts'     => (is_null($limit) ? -1 : $limit),
                        'orderby'         => 'menu_order',
                        'order'           => 'ASC');

    $posts  = get_posts($arguments);
    $images = array();

    foreach ($posts as $post)
    {
      $type     = get_post_meta($post->ID, 'attachment_type', true);
      if ($type != 'Plattegrond' && (is_null($limit) || count($images) < $limit))
      {
        $image    = wp_get_attachment_image_src($post->ID, $size);
        if (empty($image[1]))
          $image[1] = get_option($size . '_size_w', 0);
        if (empty($image[2]))
          $image[2] = get_option($size . '_size_h', 0);

        $images[] = $image;
      }
    }

    return $images;
  }

  /**
  * @desc Check if there are images with type 'Plattegrond'
  *
  * @param int $postId (optional)
  * @return bool
  */
  function yog_hasImagePlans($postId = null)
  {
    if (is_null($postId))
      $postId = get_the_ID();

    $arguments = array('post_type'        => 'attachment',
                        'post_parent'     => $postId,
                        'post_mime_type'  => 'image',
                        'meta_key'        => 'attachment_type',
                        'meta_value'      => 'Plattegrond',
                        'numberposts'     => 1);

    $posts  = get_posts($arguments);
    return (is_array($posts) && count($posts) > 0);
  }

  /**
  * @desc Retrieve all images with type 'Plattegrond'
  *
  * @param string $size (thumbnail, medium, large)
  * @param int $limit
  * @param int $postId (optional)
  * @return array
  */
  function yog_retrieveImagePlans($size, $limit = null, $postId = null)
  {
    if (is_null($postId))
      $postId = get_the_ID();

    $arguments = array('post_type'        => 'attachment',
                        'post_parent'     => $postId,
                        'post_mime_type'  => 'image',
                        'meta_key'        => 'attachment_type',
                        'meta_value'      => 'Plattegrond',
                        'numberposts'     => (is_null($limit) ? -1 : $limit),
                        'orderby'         => 'menu_order',
                        'order'           => 'ASC');

    $posts  = get_posts($arguments);
    $images = array();

    foreach ($posts as $post)
    {
      $image    = wp_get_attachment_image_src($post->ID, $size);
      if (empty($image[1]))
        $image[1] = get_option($size . '_size_w', 0);
      if (empty($image[2]))
        $image[2] = get_option($size . '_size_h', 0);

      $images[] = $image;
    }

    return $images;
  }

  /**
  * @desc Check if geo location is set
  *
  * @param int $postId (optional)
  * @return bool
  */
  function yog_hasLocation($postId = null)
  {
    $specs = yog_retrieveSpecs(array('Latitude', 'Longitude'), $postId);
    return (!empty($specs['Latitude']) && !empty($specs['Longitude']));
  }

  /**
   * @desc function that generates a static map based on SvzMaps
   *
   * @param string $mapType (optional, default hybrid)
   * @param integer $zoomLevel (optional, default 18)
   * @param integer width (optional, default 486)
   * @param integer height (optional, default 400)
   * @param int $postId (optional)
   * @return html
   */
  function yog_retrieveStaticMap($mapType = 'hybrid', $zoomLevel = 18, $width = 486, $height = 400, $postId = null)
  {
    $specs      = yog_retrieveSpecs(array('Latitude', 'Longitude'), $postId);

    $latitude   = isset($specs['Latitude']) ? $specs['Latitude'] : false;
    $longitude  = isset($specs['Longitude']) ? $specs['Longitude'] : false;

    // Make sure the width is not above 640px
    if ($width > 640)
      $width = 640;

    $html       = '';

    if ($latitude !== false && $longitude !== false)
    {
      // Including of the SVZ Solutions library
      require_once(YOG_PLUGIN_DIR . '/includes/svzsolutions/maps/Map.php');

      // Create a new instance of Google Maps version 3 STATIC version
      $map                          = SVZ_Solutions_Maps_Map::getInstance(SVZ_Solutions_Maps_Map::MAP_TYPE_GOOGLE_MAPS, '3', 'static');
      $map->setWidth($width);
      $map->setHeight($height);
      $map->setMapType($mapType);
      $map->setZoomLevel($zoomLevel);
      $map->setCenterGeocode(new SVZ_Solutions_Generic_Geocode((float)$latitude, (float)$longitude));

      // Add a single admin marker
      $marker     = new SVZ_Solutions_Generic_Marker('admin', (float)$latitude, (float)$longitude);
      $map->addMarker($marker);

      $html .= '<img alt="" width="' . $map->getWidth() . '" height="' . $map->getHeight() . '" src="' . htmlentities($map->getStaticUrl()) . '" />';
    }

    return $html;
  }

  /**
   * @desc Method yog_generateMap which generates the map based on the settings
   *
   * @param {}
   * @param {String} $onLoad
   * @param {String} $extraAfterOnLoad
   * @param {Boolean} $adminMode
   * @return {String}
   */
  function yog_generateMap($map, $onLoad, $extraAfterOnLoad = '', $adminMode = false)
  {
      $markerTypeCss = '';

      $postTypes    = yog_getAllPostTypes();

      foreach ($postTypes as $postType)
      {
        // Add a admin marker type
        $markerType = new SVZ_Solutions_Generic_Marker_Type($postType);
				if ($option === false || empty($option['url']))	// TODO: replace with code so the default markers are shown if no marker is configured?
					$markerType->enableIcon();
        //$markerType->enableIcon();
        //$markerType->getLayer()->setName('admin'); // Define a layer where the provided marker types should live in
        //$markerType->getLayer()->setTypeFixed();
        $map->addMarkerType($markerType);

        $option = get_option('yog-marker-type-' . $postType);

        if ($option !== false && !empty($option['url']))
        {
          $markerTypeCss .= '.sg-marker-' . $postType . ' {
                          width: ' . $option['width'] . 'px;
                          height: ' . $option['height'] . 'px;';

          $markerTypeCss .= 'background-image: url("' . $option['url'] . '");';
          $markerTypeCss .= '}';
        }

      }

      // Add a admin marker type
      $markerType = new SVZ_Solutions_Generic_Marker_Type('admin');
      $markerType->enableIcon();
      $markerType->getLayer()->setName('admin'); // Define a layer where the provided marker types should live in

      if ($adminMode === true)
        $markerType->getLayer()->setTypeStatic();
      else
        $markerType->getLayer()->setTypeFixed();

      $map->addMarkerType($markerType);

      $baseUrl = substr(YOG_PLUGIN_URL, strpos(YOG_PLUGIN_URL, 'wp-content')) . '/inc/';

      $html = '<style type="text/css">' . $markerTypeCss . '</style>';

      global $htmlScript;

      $htmlScript = '<script type="text/javascript">
                // <![CDATA[

                  var yogMap, yogMapManager, map;
                  var yogBaseUrl = "' . home_url() . '";
                  var yogLocationPrefix = "' . $baseUrl . '";

                  var yogJsOnLoad = function(ready)
                  {
                      ' . $onLoad . '
                  };

                  var yogJsExtraAfterLoad = function(ready)
                  {
                      ' . $extraAfterOnLoad . '
                  };

                  var yogJsMapConfig = \'' . json_encode($map->getConfig()) . '\';

                // ]]>
                </script>';

      function print_my_inline_script() {
        global $htmlScript;

        echo $htmlScript;
      }

      add_action( 'wp_footer', 'print_my_inline_script' );
      add_action( 'admin_footer', 'print_my_inline_script' );

      //$html .= $htmlScript;

      $url = YOG_PLUGIN_URL . '/inc/js/yog-map-bootstrap.js';

      if (get_option('yog_javascript_dojo_dont_enqueue'))
        echo '<script defer type="text/javascript" src="' . $url . '"></script>';
      else
        wp_enqueue_script('yog-map', $url, false, '1.0');

      $html .= '<div id="' . $map->getContainerId() . '" class="map-holder" style="display: none; width: ' . $map->getWidth() . $map->getWidthUnit() . '; height: ' . $map->getHeight() . $map->getHeightUnit() . ';"></div>';

      return $html;
  }

  /**
   * @desc function that generates a dynamic map based on SvzMaps
   *
   * @param string $mapType (optional, default hybrid)
   * @param integer $zoomLevel (optional, default 18)
   * @param integer width (optional, default 486)
   * @param integer height (optional, default 400)
   * @param string $extraAfterOnLoad (optional, default empty string)
   * @param bool $adminMode (optional, default false)
   * @param int $postId (optional)
   * @return string
   */
  function yog_retrieveDynamicMap($mapType = 'hybrid', $zoomLevel = 18, $width = 486, $height = 400, $extraAfterOnLoad = '', $adminMode = false, $postId = null)
  {
    if (!YogPlugin::isDojoLoaded())
      YogPlugin::loadDojo();

    $latitude = false;
    $longitude = false;

    $postId     = get_the_ID();

    $specs      = yog_retrieveSpecs(array('Latitude', 'Longitude'));

    $latitude   = isset($specs['Latitude']) ? $specs['Latitude'] : false;
    $longitude  = isset($specs['Longitude']) ? $specs['Longitude'] : false;

    $html       = '';

    if (($latitude !== false && $longitude !== false) || $adminMode === true)
    {
      if ($adminMode && $latitude === false && $longitude === false)
      {
        $latitude   = 52.06758749919184;
        $longitude  = 5.34619140625;
      }

      // Including of the SVZ Solutions library
      require_once(YOG_PLUGIN_DIR . '/includes/svzsolutions/maps/Map.php');

      // Create a new instance of Google Maps version 3
      $map                          = SVZ_Solutions_Maps_Map::getInstance(SVZ_Solutions_Maps_Map::MAP_TYPE_GOOGLE_MAPS, '3');

      $map->setHeight($height);

      if (strpos($width, '%') !== false)
      {
        $map->setWidth((int) str_replace('%', '', $width));
        $map->setWidthUnit('%');
        $map->disableScrollwheel();
      }
      else
      {
        $map->setWidth($width);
      }

      // Sets the id of the container (HTMLDomElement) the map must be put on.
      $map->setContainerId('yesco-og-dynamic-map');

      // Sets the default map type to satellite
      $map->setMapType($mapType);

      // Sets the zoom level to start with to 18.
      $map->setZoomLevel($zoomLevel);

      // Sets the geocode the map should start at centered.
      $map->setCenterGeocode(new SVZ_Solutions_Generic_Geocode((float)$latitude, (float)$longitude));

      if ($adminMode === true)
      {
        // Add a single marker
        $marker     = new SVZ_Solutions_Generic_Marker('admin', (float)$latitude, (float)$longitude);

        $marker->setDraggable(true);
      }
      else
      {
        // Use the object type custom marker
        $post       = get_post($postId);
        $postType   = $post->post_type;

        // Add a single marker
        $marker     = new SVZ_Solutions_Generic_Marker($postType, (float)$latitude, (float)$longitude);

        $marker->setDraggable(false);
      }

      $map->addMarker($marker);

      $onLoad = '
                            // Hide the static version
                            var staticMapHolder = dojo.byId("yesco-og-static-map-holder");

                            if (staticMapHolder)
                              dojo.style(staticMapHolder, "display", "none");

                            // Show the dynamic version
                            var dynamicMap = dojo.byId("yesco-og-dynamic-map");

                            if (dynamicMap)
                              dojo.style(dynamicMap, "display", "block");

                            ';

      $html = yog_generateMap($map, $onLoad, $extraAfterOnLoad, $adminMode);
    }

    return $html;
  }

  /**
   * @desc Method yog_loadMapData
   *
   * @param {Void}
   * @return {Void}
   */
  function yog_loadMapData()
  {
    // Including of the SVZ Solutions library
    require_once(YOG_PLUGIN_DIR . '/includes/svzsolutions/maps/Map.php');

    // Reading out data applied in the requests
    $mapClusterMode   = SVZ_Solutions_Generic_Marker_Manager::CLUSTER_MODE_NONE;

    $markerManager    = new SVZ_Solutions_Generic_Marker_Manager();
    $markerManager->setListDataLoadUrl('data-info-window-list.php');
    $markerManager->setClusterMode($mapClusterMode);

    // Depending on the map type retrieve the property using wp_query
    $postTypes  = (!empty($_GET['post_types']) ? explode(',', $_GET['post_types']) : array());

    // @TODO: Filter the ones that are activated in this plugin

    $posts      = get_posts(array('numberposts' => 999999, 'post_type' => $postTypes));

    foreach ($posts as $post)
    {
      $postType      = $post->post_type;
      $latitude      = yog_retrieveSpec('Latitude', $post->ID);
      $longitude     = yog_retrieveSpec('Longitude', $post->ID);

      if (strlen(trim($latitude)) > 0 && strlen(trim($longitude)) > 0)
      {
        $latitude   = (float)$latitude;
        $longitude  = (float)$longitude;

        $marker     = new SVZ_Solutions_Generic_Marker($postType, $latitude, $longitude);

        // Set the url to load all the markers from
        $dataLoadUrl = admin_url('admin-ajax.php') . '?action=loadmarkerdetails&postID=' . $post->ID;
        $marker->setDataLoadUrl($dataLoadUrl);

        $markerManager->addMarker($marker);
      }
    }

    // Generate JSON output
    $output           = new StdClass();
    $output->markers  = $markerManager->toArray();

    echo json_encode($output);
    exit;
  }

  /**
   * @desc Method yog_loadMarkerDetails which loads the info window details
   *
   * @param {Void}
   * @return {Void}
   */
  function yog_loadMarkerDetails()
  {
    $postID         = $_GET['postID'];

    $yogMapWidget   = new YogMapWidget();
    echo $yogMapWidget->generateDetailWindow($postID);

    exit;
  }

  // Make available in case the user is logged in and in case it is not
  add_action('wp_ajax_loadmapdata', 'yog_loadMapData');
  add_action('wp_ajax_nopriv_loadmapdata', 'yog_loadMapData');

  // Make available in case the user is logged in and in case it is not
  add_action('wp_ajax_loadmarkerdetails', 'yog_loadMarkerDetails');
  add_action('wp_ajax_nopriv_loadmarkerdetails', 'yog_loadMarkerDetails');


  /**
   * @desc Method which generates a photo slider and main image
   *
   * @param string $largeImageSize (optional; thumbnail, medium, large. default: large)
   * @param string $thumbnailSize (optional; thumbnail, medium, large. default: thumbnail)
   * @param bool $scrollable (optional, default false)
   * @param string $type (optional; Plattegrond, Normaal or null)
   * @param int $postId (optional)
   * @return string
   */
  function yog_retrievePhotoSlider($largeImageSize = 'large', $thumbnailSize = 'thumbnail', $scrollable = false, $type = null, $postId = null)
  {
    if ($type == 'Plattegrond')
      $largeImages      = yog_retrieveImagePlans($largeImageSize);
    else if ($type == 'Normaal')
      $largeImages      = yog_retrieveNormalImages($largeImageSize);
    else
      $largeImages      = yog_retrieveImages($largeImageSize);

    if ($type == 'Plattegrond')
      $thumbnails       = yog_retrieveImagePlans($thumbnailSize);
    else if ($type == 'Normaal')
      $thumbnails       = yog_retrieveNormalImages($thumbnailSize);
    else
      $thumbnails       = yog_retrieveImages($thumbnailSize);

    $largeImageHeight   = get_option($largeImageSize . '_size_h');
    $largeImageWidth    = get_option($largeImageSize . '_size_w');

    $thumbs             = array();
    $html               = '';
    $scrollable         = true;

    if (!empty($largeImages) && count($largeImages) > 0 && !empty($largeImages[0][0]))
    {
			// Enqueue scripts / css
      $minifyExtension = (YOG_DEBUG_MODE === true) ? '' : '.min';
      wp_enqueue_script('yog-image-slider', YOG_PLUGIN_URL .'/inc/js/image_slider' . $minifyExtension . '.js', array('jquery'), YOG_PLUGIN_VERSION, true);
      wp_enqueue_style('yog-photo-slider',  YOG_PLUGIN_URL . '/inc/css/photo_slider' . $minifyExtension . '.css', array(), YOG_PLUGIN_VERSION);
			
      $html = '<div class="yog-images-holder">
                <div id="imageactionsholder" class="clearfix yog-main-">
                   <div class="mainimage" style="height:' . $largeImageHeight .'px;">
                     <img class="yog-big-image" id="bigImage" alt="" src="' . $largeImages[0][0] . '" style="max-height:' . $largeImageHeight . 'px;max-width:' . $largeImageWidth . 'px;" />
                   </div>
                 </div>';

      if (!empty($thumbnails) && count($thumbnails) > 1 && count($thumbnails) == count($largeImages))
      {
        $thumbnailsHtml = '';
        foreach ($thumbnails as $key => $thumbnail)
        {
          $largeImage      = $largeImages[$key];
          $thumbnailsHtml .= '<a href="' . $largeImage[0] . '" class="yog-thumb"><img class="yog-image-' . $key . '" alt="" src="' . $thumbnail[0] . '" /></a>';
        }

        $html .= '<div id="imgsliderholder" class="yog-image-slider-holder' .($scrollable === true ? ' yog-scrolling-enabled' : '') . '">';
        if ($scrollable === true)
          $html .= '<div class="left yog-scroll"><a title="Vorige foto" onclick="return false;" href="#">&nbsp;</a></div>';
        if ($scrollable === true)
          $html .= '<div class="right yog-scroll"><a title="Volgende foto" onclick="return false;" href="#">&nbsp;</a></div>';

        $html .= '<div id="imgslider" class="yog-image-slider">
                    <div id="slider-container">' . $thumbnailsHtml . '</div>
                  </div>';

        $html .= '</div>';
      }

      $html .= '</div>';
    }

    return $html;
  }

  /**
   * @desc Method yog_retrievePostTypes
   *
   * @param void
   * @return array
   */
  function yog_getAllPostTypes()
  {
    $postTypes  = array(
      POST_TYPE_WONEN,
      POST_TYPE_BOG,
      POST_TYPE_NBPR,
      POST_TYPE_NBTY,
      POST_TYPE_NBBN,
      POST_TYPE_BBPR,
      POST_TYPE_BBTY,
      POST_TYPE_RELATION
    );

    return $postTypes;
  }

  /**
   * @desc Retrieve all the custom post types for objects
   *
   * @param void
   * @return array
   */
  function yog_getAllObjectPostTypes()
  {
    $postTypes  = array(
      POST_TYPE_WONEN,
      POST_TYPE_BOG,
      POST_TYPE_NBPR,
      POST_TYPE_NBTY,
      POST_TYPE_NBBN,
      POST_TYPE_BBPR,
      POST_TYPE_BBTY
    );

    return $postTypes;
  }