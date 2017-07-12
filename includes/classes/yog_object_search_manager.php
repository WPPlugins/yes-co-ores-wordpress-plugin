<?php
require_once(YOG_PLUGIN_DIR . '/includes/config/config.php');
require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_fields_settings.php');

/**
* @desc YogObjectSearchManager
* @author Kees Brandenburg - Yes-co Nederland
*/
class YogObjectSearchManager
{
  static public $instance;
  private $db;
  private $searchExtended = false;

  /**
  * @desc Constructor
  *
  * @param void
  * @return YogObjectWonenManager
  */
  private function __construct()
  {
    global $wpdb;
    $this->db = $wpdb;
  }

  /**
  * @desc Get the instance of the YogObjectSearch
  *
  * @param void
  * @return YogObjectSearch
  */
  static public function getInstance()
  {
    if (is_null(self::$instance))
      self::$instance = new self();

    return self::$instance;
  }

  /**
  * @desc Extend the wordpress search with object functionality
  *
  * @param void
  * @return void
  */
  public function extendSearch()
  {
    // Make sure the search is only extended once
    if ($this->searchExtended === false)
    {
      add_action('posts_where_request', array($this, 'extendSearchWhere'));
      add_action('posts_orderby_request', array($this, 'changePostSortOrder'));
      $this->searchExtended = true;
    }
  }

  /**
   * Adjust sort order for search widget
   *
   * @param string $order
   * @return string
   */
  public function changePostSortOrder($order)
  {
    if (is_search() && !empty($_REQUEST['order']))
    {
      switch ($_REQUEST['order'])
      {
        case 'date_asc':
          $order = 'post_date ASC';
          break;
        case 'date_desc':
          $order = 'post_date DESC';
          break;
        case 'title_asc':
          $order = 'post_title ASC';
          break;
        case 'title_desc':
          $order = 'post_title DESC';
          break;
        case 'price_asc':
          $order = 'CAST((SELECT meta_value FROM ' . $this->db->postmeta . ' WHERE post_id=' . $this->db->posts . '.ID AND meta_key=\'yog_price_order\') AS SIGNED) ASC';
          break;
        case 'price_desc';
          $order = 'CAST((SELECT meta_value FROM ' . $this->db->postmeta . ' WHERE post_id=' . $this->db->posts . '.ID AND meta_key=\'yog_price_order\') AS SIGNED) DESC';
          break;
        case 'bog_surface_asc':
          $order = 'CAST((SELECT meta_value FROM ' . $this->db->postmeta . ' WHERE post_id=' . $this->db->posts . '.ID AND meta_key=\'bedrijf_Oppervlakte\') AS SIGNED) ASC';
          break;
        case 'bog_surface_desc':
          $order = 'CAST((SELECT meta_value FROM ' . $this->db->postmeta . ' WHERE post_id=' . $this->db->posts . '.ID AND meta_key=\'bedrijf_Oppervlakte\') AS SIGNED) DESC';
          break;
      }
    }

    return $order;
  }

  /**
  * @desc Extend the where to also search on the object custom fields, should not be called manually
  *
  * @param string $where
  * @return string
  */
  public function extendSearchWhere($where)
  {
    if (is_search())
    {
      if (!empty($_REQUEST['object_type']) && in_array($_REQUEST['object_type'], array(POST_TYPE_WONEN, POST_TYPE_BOG, POST_TYPE_NBPR, POST_TYPE_NBTY, POST_TYPE_BBPR, POST_TYPE_BBTY)))
        $where = $this->extendSearchWhereSearchWidget($where);
      else
        $where = $this->extendSearchWhereDefault($where);
    }
    else if (is_category() || is_archive())
    {
      $where = $this->extendCategoryWhere($where);
    }

    return $where;
  }
  
  /**
   * Extend where query for showing category
   * 
   * @param string $where
   * @return string
   */
  private function extendCategoryWhere($where)
  {
    $yogNochildsSearchresults = get_option('yog_nochilds_searchresults');
    
    if (!empty($yogNochildsSearchresults))
      $where .= ' AND (post_type != \'' . POST_TYPE_WONEN . '\' OR post_parent = 0)';
    
    return $where;
  }

  /**
  * @desc Extend normal search queries
  *
  * @param string $where
  * @return string
  */
  private function extendSearchWhereDefault($where)
  {
    global $wp;

    $objectType = empty($_REQUEST['object']) ? 'all' : $_REQUEST['object'];
    $searchTerm = $wp->query_vars['s'];
    $postTbl    = $this->db->posts;

    // Check if search field is filled
		if (empty($searchTerm) || $searchTerm == '%25' || $searchTerm == '%')
    {
      // Only search specific post type
      if ($objectType != 'all')
        $where .= " AND " . $postTbl . ".post_type = '" . $objectType . "'";
      else if (!empty($_REQUEST['object']))
        $where .= " AND " . $postTbl . ".post_type IN ('" . POST_TYPE_WONEN . "', '" . POST_TYPE_BOG . "', '" . POST_TYPE_NBPR . "', '" . POST_TYPE_NBTY . "', '" . POST_TYPE_BBPR . "', '" . POST_TYPE_BBTY . "')";

			return $where;
    }

    // Escape search terms
    if (method_exists($this->db, '_real_escape'))
    {
		  $searchTerm         = $this->db->_real_escape($searchTerm);
      $objectType         = $this->db->_real_escape($objectType);
    }
    else
    {
      $searchTerm         = addslashes($searchTerm);
      $objectType         = addslashes($objectType);
    }

    // Determine supported fields
    $supportedMetaFields = array();
    if (in_array($objectType, array(POST_TYPE_WONEN, 'all')))
      $supportedMetaFields = array_merge($supportedMetaFields, array('huis_Wijk','huis_Buurt','huis_Land','huis_Provincie','huis_Gemeente','huis_Plaats','huis_Straat','huis_Huisnummer','huis_Postcode','huis_SoortWoning','huis_TypeWoning','huis_KenmerkWoning'));
    if (in_array($objectType, array(POST_TYPE_BOG, 'all')))
      $supportedMetaFields = array_merge($supportedMetaFields, array('bedrijf_Wijk', 'bedrijf_Buurt', 'bedrijf_Land', 'bedrijf_Provincie', 'bedrijf_Gemeente', 'bedrijf_Plaats', 'bedrijf_Straat', 'bedrijf_Huisnummer', 'bedrijf_Postcode', 'bedrijf_Type'));
    if (in_array($objectType, array(POST_TYPE_NBPR, 'all')))
      $supportedMetaFields = array_merge($supportedMetaFields, array('yog-nbpr_Wijk', 'yog-nbpr_Buurt', 'yog-nbpr_Land', 'yog-nbpr_Provincie', 'yog-nbpr_Gemeente', 'yog-nbpr_Plaats', 'yog-nbpr_Straat', 'yog-nbpr_Huisnummer', 'yog-nbpr_Postcode', 'yog-nbpr_ProjectSoort'));
    if (in_array($objectType, array(POST_TYPE_BBPR, 'all')))
      $supportedMetaFields = array_merge($supportedMetaFields, array('yog-bbpr_Wijk', 'yog-bbpr_Buurt', 'yog-bbpr_Land', 'yog-bbpr_Provincie', 'yog-bbpr_Gemeente', 'yog-bbpr_Plaats', 'yog-bbpr_Postcode'));

    $metaTbl              = $this->db->postmeta;

    $whereQuery           = array();

		foreach ($supportedMetaFields as $metaField)
    {
			$whereQuery[] = "meta_key = '" . $metaField . "' AND meta_value LIKE '%" . $searchTerm . "%'";
    }

		$query = "SELECT DISTINCT post_id FROM " . $metaTbl . " WHERE (" . implode(') OR (', $whereQuery) . ')';

		// Retrieve post ids
		$postIds =  $this->db->get_col($query, 0);

		if (is_array($postIds) && count($postIds))
    {
      $orgPart  = "(wp_posts.post_title LIKE '%" . $searchTerm . "%')";
      $idPart   = $postTbl . ".ID IN (" . implode(',', $postIds)  . ")";

      // Add to original where (if replace part is found)
      if (strpos($where, $searchTerm) !== false)
      {
        $where = str_replace($orgPart, $orgPart . ' OR ' . $idPart, $where);
      }
      // Create new where (fallback if replace part is not found)
      else
      {
        $where  = " AND (" . $idPart;
        $where .= " OR " . $postTbl . ".post_title LIKE '%" .$searchTerm ."%' OR " . $postTbl . ".post_content LIKE '%" .$searchTerm ."%'";
        $where .= ") AND " . $postTbl . ".post_status = 'publish' AND " . $postTbl . ".post_type IN ('post', 'page', 'attachment', '" . POST_TYPE_WONEN . "', '" . POST_TYPE_BOG . "', '" . POST_TYPE_NBPR . "', '" . POST_TYPE_NBTY . "', '" . POST_TYPE_BBPR . "', '" . POST_TYPE_BBTY . "')";
      }

      // Only search specific post type
      if ($objectType != 'all')
        $where .= " AND " . $postTbl . ".post_type = '" . $objectType . "'";
      else if (!empty($_REQUEST['object']))
        $where .= " AND " . $postTbl . ".post_type IN ('" . POST_TYPE_WONEN . "', '" . POST_TYPE_BOG . "', '" . POST_TYPE_NBPR . "', '" . POST_TYPE_NBTY . "', '" . POST_TYPE_BBPR . "', '" . POST_TYPE_BBTY . "')";
		}
    // No objects found and post type specific, so make sure query returns no posts
    else if (!empty($_REQUEST['object']))
    {
      $where .= ' AND true = false';
    }

		return $where;
  }

  /**
  * @desc Extend search for widgets
  *
  * @param string $where
  * @return string
  */
  private function extendSearchWhereSearchWidget($where, $returnArray = false)
  {
    $objectType     = $_REQUEST['object_type'];
    $fieldsSettings = YogFieldsSettingsAbstract::create($objectType);
    $tbl            = $this->db->postmeta;

    $query = array();
    $query[] = $this->db->posts . ".post_type = '" . $objectType . "'";

    // Determine parts of query for custom fields
    foreach ($fieldsSettings->getFields() as $metaKey => $options)
    {
      $requestKey = str_replace($objectType . '_', '', $metaKey);

      if (!empty($options['search']))
      {
        $selectSql = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $metaKey . "' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";

        switch ($options['search'])
        {
          // Exact search
          case 'exact':
            if (!empty($_REQUEST[$requestKey]))
            {
              if (!is_array($_REQUEST[$requestKey]))
                $_REQUEST[$requestKey] = array($_REQUEST[$requestKey]);

              $query[] = "(" . $selectSql . ") IN ('" . implode("', '", $_REQUEST[$requestKey]) . "')";
            }
            break;
          // Exact search on parent
          case 'parent-exact':
            if (!empty($options['parentKey']))
            {
              $metaKey    = $options['parentKey'];
              $selectSql  = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $metaKey . "' AND " . $tbl . ".post_id = " . $this->db->posts . ".post_parent";

              $sql  = $this->db->posts . '.post_parent IS NOT NULL AND ';
              $sql .= $this->db->posts . '.post_parent > 0 AND ';
              $sql .= "(" . $selectSql . ") IN ('" . implode("', '", $_REQUEST[$requestKey]) . "')";

              $query[] = '(' . $sql . ')';
            }
            break;
          // Range search
          case 'range':
            $min = empty($_REQUEST[$requestKey . '_min']) ? 0 : (int) str_replace('.', '', $_REQUEST[$requestKey . '_min']);
            $max = empty($_REQUEST[$requestKey . '_max']) ? 0 : (int) str_replace('.', '', $_REQUEST[$requestKey . '_max']);

            if ($min > 0 && $max > 0)
              $query[] = "((" . $selectSql . ") BETWEEN " . $min . " AND " . $max . ")";
            else if ($min > 0 && $max == 0)
              $query[] = "((" . $selectSql . ") >= " . $min . ")";
            else if ($min == 0 && $max > 0)
              $query[] = "((" . $selectSql . ") <= " . $max . " OR (" . $selectSql . ") IS NULL)";

            break;
          // Range search on Min / Max fields
          case 'minmax-range':
            $requestKey = str_replace(array('Min', 'Max'), '', $requestKey);

            $min        = empty($_REQUEST[$requestKey . '_min']) ? 0 : (int) str_replace('.', '', $_REQUEST[$requestKey . '_min']);
            $max        = empty($_REQUEST[$requestKey . '_max']) ? 0 : (int) str_replace('.', '', $_REQUEST[$requestKey . '_max']);

            $metaKey  = str_replace(array('Min', 'Max'), '', $metaKey);
            $minField = $metaKey . 'Min';
            $maxField = $metaKey . 'Max';

            $sqlMin   = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $minField . "' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";
            $sqlMax   = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $maxField . "' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";

            if ($min > 0 && $max > 0)
              $query[] = "(((" . $sqlMin . ") BETWEEN " . $min . " AND " . $max . ") OR ((" . $sqlMax . ") BETWEEN " . $min . " AND " . $max . "))";
            else if ($min > 0)
              $query[] = "(" . $min . " <= (" . $sqlMax . "))";
            else if ($max > 0)
              $query[] = "(" . $max . " >= (" . $sqlMax . "))";

            break;
        }
      }
    }

    // Handle price type search
    if (!empty($_REQUEST['PrijsType']) && is_array($_REQUEST['PrijsType']))
    {
      $metaKeys = array();

      if (in_array($objectType, array(POST_TYPE_NBPR, POST_TYPE_NBTY, POST_TYPE_BBPR)))
      {
        if (in_array('Koop', $_REQUEST['PrijsType']))
        {
          $metaKeys[] = $objectType . '_' . ($objectType == POST_TYPE_NBPR ? 'KoopAanneemSomMin' : 'KoopPrijsMin');
          $metaKeys[] = $objectType . '_' . ($objectType == POST_TYPE_NBPR ? 'KoopAanneemSomMax' : 'KoopPrijsMax');
        }

        if (in_array('Huur', $_REQUEST['PrijsType']))
        {
          $metaKeys[] = $objectType . '_HuurPrijsMin';
          $metaKeys[] = $objectType . '_HuurPrijsMax';
        }
      }
      else
      {
        if (in_array('Koop', $_REQUEST['PrijsType']))
         $metaKeys[] = $objectType . '_KoopPrijs';

        if (in_array('Huur', $_REQUEST['PrijsType']))
         $metaKeys[] = $objectType . '_HuurPrijs';
      }

      if (count($metaKeys) > 0)
      {
        $queryParts = array();

        foreach ($metaKeys as $metaKey)
        {
          $queryParts[] = 'EXISTS (SELECT true FROM ' . $tbl . ' WHERE ' . $tbl . '.meta_key = \'' . $metaKey . '\' AND ' . $tbl . '.meta_value IS NOT NULL AND ' . $tbl . '.meta_value != \'\' AND ' . $tbl . '.post_id = ' . $this->db->posts . '.ID)';
        }

        $query[] = '(' . implode(' OR ', $queryParts) . ')';
      }
    }

    // Handle price condition search (for BOG)
    if (!empty($_REQUEST['PrijsConditie']) && is_array($_REQUEST['PrijsConditie']) && $objectType == POST_TYPE_BOG)
    {
      $queryParts     = array();
      $buyConditions  = array_intersect($_REQUEST['PrijsConditie'], array('k.k.', 'v.o.n.'));
      $rentConditions = array_intersect($_REQUEST['PrijsConditie'], array('p.m.', 'p.j.', 'per vierkante meter p.j.'));

      if (count($buyConditions) > 0)
        $queryParts[] = "(SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $objectType . "_KoopPrijsConditie' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID) IN ('" . implode("', '", $buyConditions) . "')";

      if (count($rentConditions) > 0)
        $queryParts[] = "(SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $objectType . "_HuurPrijsConditie' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID) IN ('" . implode("', '", $rentConditions) . "')";

      if (count($queryParts) > 0)
        $query[] = '(' . implode(' OR ', $queryParts) . ')';
    }

    // Handle price search (koop + huur)
    if (!empty($_REQUEST['Prijs_min']) || !empty($_REQUEST['Prijs_max']))
    {
      $min      = empty($_REQUEST['Prijs_min']) ? 0 : (int) str_replace('.', '', $_REQUEST['Prijs_min']);
      $max      = empty($_REQUEST['Prijs_max']) ? 0 : (int) str_replace('.', '', $_REQUEST['Prijs_max']);

      if (in_array($objectType, array(POST_TYPE_NBPR, POST_TYPE_NBTY, POST_TYPE_BBPR)))
      {
        $koopMinField = ($objectType == POST_TYPE_NBPR) ? 'KoopAanneemSomMin' : 'KoopPrijsMin';
        $koopMaxField = ($objectType == POST_TYPE_NBPR) ? 'KoopAanneemSomMax' : 'KoopPrijsMax';
        $huurMinField = 'HuurPrijsMin';
        $huurMaxField = 'HuurPrijsMax';

        $koopSqlMin   = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $objectType . "_" . $koopMinField . "' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";
        $koopSqlMax   = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $objectType . "_" . $koopMaxField . "' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";
        $huurSqlMin   = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $objectType . "_" . $huurMinField . "' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";
        $huurSqlMax   = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $objectType . "_" . $huurMaxField . "' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";

        $sql  = '(';
          $sql .= '((' . $koopSqlMin . ') BETWEEN ' . $min . ' AND ' . $max . ')';
            $sql .= ' OR ';
          $sql .= '((' . $koopSqlMax . ') BETWEEN ' . $min . ' AND ' . $max . ')';
        $sql .= ')';
        $sql .= ' OR ';
        $sql .= '(';
          $sql .= '((' . $huurSqlMin . ') BETWEEN ' . $min . ' AND ' . $max . ')';
            $sql .= ' OR ';
          $sql .= '((' . $huurSqlMax . ') BETWEEN ' . $min . ' AND ' . $max . ')';
        $sql .= ')';

        if (!empty($sql))
          $query[] = '(' . $sql . ')';
      }
      else
      {
        $koopSql  = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $objectType . "_KoopPrijs' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";
        $huurSql  = "SELECT " . $tbl . ".meta_value FROM " . $tbl . " WHERE " . $tbl . ".meta_key = '" . $objectType . "_HuurPrijs' AND " . $tbl . ".post_id = " . $this->db->posts . ".ID";

        if ($min > 0 && $max > 0)
          $query[] = "(((" . $koopSql . ") BETWEEN " . $min . " AND " . $max . ") OR ((" . $huurSql . ") BETWEEN " . $min . " AND " . $max . "))";
        else if ($min > 0 && $max == 0)
          $query[] = "((" . $koopSql . ") >= " . $min . " OR (" . $huurSql . ") >= " . $min . ")";
        else if ($min == 0 && $max > 0)
          $query[] = "((" . $koopSql . ") <= " . $max . " OR (" . $huurSql . ") <= " . $max . " OR ((" . $koopSql . ") IS NULL AND (" . $huurSql . ") IS NULL))";
      }
    }
    
    // Filter NBvk/NBvh/BBvk/BBvh objects with a parent
    $yogNochildsSearchresults = get_option('yog_nochilds_searchresults');
    if ($objectType === POST_TYPE_WONEN && !empty($yogNochildsSearchresults))
      $query[] = 'post_parent = 0';

    if ($returnArray === true)
      return $query;

    // Update where query
    if (!empty($query))
      $where .= ' AND ' . implode(' AND ', $query);

    return $where;
  }

  /**
  * @desc Retrieve the lowest available price for a specific meta field
  *
  * @param mixed $metaKeys (string or array)
  * @param $params (optional, default array)
  * @return mixed
  */
  public function retrieveMinMetaValue($metaKeys, $params = array(), $extendWithRequest = false)
  {
    if (!is_array($metaKeys))
      $metaKeys = array($metaKeys);

    $postType = substr($metaKeys[0], 0, strpos($metaKeys[0], '_'));

    // Determine where parts
    $where    = array();
    $where[]  = $this->db->posts . ".post_type = '" . $postType . "'";
    $where    = array_merge($where, $this->determineGlobalMetaWhere($params, false, $extendWithRequest));

    $sql  = "SELECT DISTINCT (";
      $sql .= "SELECT MIN(CAST(meta_value  AS UNSIGNED INTEGER)) FROM " . $this->db->postmeta . " WHERE ";
        $sql .= "meta_key IN ('" . implode("', '", $metaKeys) . "') AND ";
        $sql .= $this->db->postmeta . ".post_id = " . $this->db->posts . ".ID";
      $sql .= ") AS value FROM " . $this->db->posts;
    $sql .= " WHERE " . implode(' AND ', $where);

    $results  = $this->db->get_results($sql);

    $min      = null;
    foreach ($results as $result)
    {
      if (empty($result->value))
      {
        $min = 0;
        break;
      }
      else if (is_null($min) || (int) $result->value < $min)
      {
        $min = (int) $result->value;
      }
    }

    return $min;
  }

  /**
  * @desc Retrieve the highest available value for a specific meta field
  *
  * @param mixed $metaKeys (string or array)
  * @param $params (optional, default array)
  * @return mixed
  */
  public function retrieveMaxMetaValue($metaKeys, $params = array())
  {
    if (!is_array($metaKeys))
      $metaKeys = array($metaKeys);

    // Determine where parts
    $where    = array();
    $where[]  = $this->db->postmeta . ".meta_key IN ('" . implode("', '", $metaKeys) . "')";
    $where[]  = $this->db->postmeta . ".meta_value != ''";
    $where    = array_merge($where, $this->determineGlobalMetaWhere($params));

    $sql  = "SELECT " . $this->db->postmeta . ".meta_value FROM " . $this->db->postmeta . " WHERE ";
    $sql .= implode(' AND ', $where) . ' ';
    $sql .= "ORDER BY CAST(meta_value AS UNSIGNED INTEGER) DESC LIMIT 1";

    return (int) $this->db->get_var($sql);
  }

  /**
  * @desc Retrieve all available values for a specfic meta field
  *
  * @param string $metaKey
  * @param $params (optional, default array)
  * @return array
  */
  public function retrieveMetaList($metaKey, $params = array())
  {
    // Determine where parts
    $where    = array();
    $where[]  = $this->db->postmeta . ".meta_key = '" . $metaKey . "'";
    $where[]  = $this->db->postmeta . ".meta_value != ''";
    $where    = array_merge($where, $this->determineGlobalMetaWhere($params));

    $sql  = "SELECT DISTINCT " . $this->db->postmeta . ".meta_value FROM " . $this->db->postmeta . " WHERE ";
    $sql .= implode(' AND ', $where) . ' ';
    $sql .= "ORDER BY meta_value";

    $results  = $this->db->get_results($sql);
    $values   = array();

    foreach ($results as $result)
    {
      $values[] = $result->meta_value;
    }

    return $values;
  }

  /**
  * @desc Determine global where for meta selection
  *
  * @param array $params
  * @param bool $relativeToMeta (optional, default true)
  * @return array
  */
  private function determineGlobalMetaWhere($params, $relativeToMeta = true)
  {
    $where        = array();
    $postIdField  = $relativeToMeta ? $this->db->postmeta . '.post_id' : $this->db->posts . '.ID';

    // Category based
    if (!empty($params['cat']))
      $where[]  = $postIdField . " IN (SELECT " . $this->db->term_relationships . ".object_id FROM " . $this->db->term_relationships . " WHERE " . $this->db->term_relationships . ".term_taxonomy_id = " . (int) $params['cat'] . ")";

    // Extend with object type
    if (!empty($params['object_type']))
      $where[] = 'EXISTS (SELECT true FROM ' . $this->db->posts . ' WHERE ID=' . $postIdField . ' AND post_type=\'' . $this->escape($params['object_type']) . '\')';

    // Extend with price condition
    if (!empty($params['HuurPrijsConditie']) && !empty($params['object_type']))
    {
      if (!is_array($params['HuurPrijsConditie']))
        $params['HuurPrijsConditie'] = array($params['HuurPrijsConditie']);

      $where[] = 'EXISTS (SELECT true FROM ' . $this->db->postmeta . ' AS meta2 WHERE meta2.post_id=' . $postIdField . ' AND meta2.meta_key=\'' . $this->escape($params['object_type']) . '_HuurPrijsConditie\' AND meta2.meta_value IN (\'' . implode('\',\'', $this->escape($params['HuurPrijsConditie'])) . '\'))';
    }

    return $where;
  }

  private function escape($value)
  {
    if (is_array($value))
    {
      $values = array();
      foreach ($value as $currentValue)
      {
        $values[] = $this->escape($currentValue);
      }

      return $values;
    }

    if (method_exists($this->db, '_real_escape'))
      return $this->db->_real_escape($value);
    else
      return addslashes($value);
  }
}