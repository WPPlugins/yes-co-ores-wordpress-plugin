<?php
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_3mcp.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_project_translation.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_project_wonen_translation.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_relation_translation.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_image_translation.php');
  require_once(YOG_PLUGIN_DIR . '/includes/classes/yog_dossier_translation.php');

  /**
  * @desc YogSynchronizationManager
  * @author Kees Brandenburg - Yes-co Nederland
  */
  class YogSynchronizationManager
  {
    private $systemLink;
    private $feedReader;
    private $db;
    private $uploadDir;
    private $warnings         = array();
    private $errors           = array();
    private $handledProjects  = array();

    /**
    * @desc Constructor
    *
    * @param YogSystemLink $systemLink
    * @return YogSynchronizationManager
    */
    public function __construct(YogSystemLink $systemLink)
    {
   		/*
   		* Require needed wordpress files
   		*/
    	// image.php is needed to use wp_generate_attachment_metadata()
   		if (file_exists(ABSPATH . 'wp-admin/includes/image.php'))
   			require_once(ABSPATH . 'wp-admin/includes/image.php');
   		// pluggable.php is needed because image.php requires it, but doesn't include it (WP >= 3.6.1)
   		if (file_exists(ABSPATH . 'wp-includes/pluggable.php'))
   			require_once(ABSPATH . 'wp-includes/pluggable.php');

      $this->systemLink = $systemLink;

      $this->feedReader = Yog3McpFeedReader::getInstance();
      $this->feedReader->read($systemLink->getCollectionUuid());

      global $wpdb;
      $this->db = $wpdb;

      // Determine upload directory
      $wpUploadDir = wp_upload_dir();
      if (!empty($wpUploadDir['basedir']) && is_writeable($wpUploadDir['basedir']))
        $this->uploadDir = $wpUploadDir['basedir'] . '/';
    }

    public function init()
    {
      add_action('init', array($this, 'doSync'));
    }

    public function doSync($return = false)
    {
      $syncRunning = get_option('yog-sync-running', false);

      if ($syncRunning === false || (date('U') - 900) > $syncRunning)
      {
        $syncStartedTimestamp = date('U');
        
        try
        {
          update_option('yog-sync-running', $syncStartedTimestamp);

          $this->syncRelations();
          $this->syncProjects();

          $response = array('status'   => 'ok',
                            'message' => 'Synchronisatie voltooid');
          
          if (count($this->handledProjects) > 0)
            $response['handledProjects'] = $this->handledProjects;

          if ($this->hasWarnings())
            $response['warnings'] = $this->getWarnings();

          if ($this->hasErrors())
          {
            $response['errors']   = $this->getErrors();
            $this->sendErrorMail($this->getErrors());
          }
        }
        catch (Exception $e)
        {
          header("HTTP/1.0 500 Internal Server Error");

          $response = array('status'  => 'error',
                            'message' => $e->getMessage());

          $this->sendErrorMail(array($e->getMessage()));
        }

        delete_option('yog-sync-running');
        if (!$this->hasErrors())
          update_option('yog-last-sync', $syncStartedTimestamp);
      }
      else
      {
        header("HTTP/1.0 429 Too Many Requests");

        $response = array('status' => 'warning',
                          'message' => 'synchronization already running, started at ' . date('Y-m-d H:i:s', $syncRunning));
      }
      
      if ($return === true)
      {
        return $response;
      }
      else
      {
        echo json_encode($response);
        exit;
      }
    }

    /**
    * @desc Synchronize relations
    *
    * @param void
    * @return void
    */
    public function syncRelations()
    {
      $existingRelationUuids  = $this->retrieveRelationUuidMapping();
      $relationEntityLinks    = $this->feedReader->getRelationEntityLinks();
      $processedRelationUuids = array();

      foreach ($relationEntityLinks as $relationEntityLink)
      {
        $uuid                     = $relationEntityLink->getUuid();
        $processedRelationUuids[] = $uuid;
        $publicationDlm           = strtotime($relationEntityLink->getDlm());

        // Check if relation allready exists
        $postType             = YogRelationTranslationAbstract::POST_TYPE;
        $existingRelation     = array_key_exists($uuid, $existingRelationUuids);
        $postId               = ($existingRelation) ? $existingRelationUuids[$uuid] : null;
        $postDlm              = $this->retrievePostDlm($postId, $postType);

        if ($publicationDlm > $postDlm)
        {
          $mcp3Relation         = $this->feedReader->retrieveRelationByLink($relationEntityLink);
          $translationRelation  = YogRelationTranslationAbstract::create($mcp3Relation, $relationEntityLink);

          // Insert / Update post
          if ($existingRelation)
            @wp_update_post(array_merge(array('ID' => $postId), $translationRelation->getPostData()));
          else
            $postId = @wp_insert_post($translationRelation->getPostData());

          // Store meta data
          $this->handlePostMetaData($postId, $postType, $translationRelation->getMetaData());

          // Update system link name (if needed)
          if ($mcp3Relation->getType() == 'office' && $this->systemLink->getName() == YogSystemLink::EMPTY_NAME)
          {
            $this->systemLink->setName($translationRelation->determineTitle());

            $systemLinkManager = new YogSystemLinkManager();
            $systemLinkManager->store($this->systemLink);
          }
        }
      }

      /* Cleanup old relations */
      $deleteRelationUuids = array_diff(array_flip($existingRelationUuids), $processedRelationUuids);

      foreach ($deleteRelationUuids as $uuid)
      {
        $postId = $existingRelationUuids[$uuid];
        wp_delete_post($postId);
      }
    }

    /**
     * Cleanup projects not linked to a 3mcp uuid
     *
     * @param void
     * @return void
     */
    private function cleanupNonLinkedProjects()
    {
      $sql = 'SELECT id, post_title FROM ' . $this->db->prefix . 'posts ';
      $sql .= 'WHERE post_type IN (\'' . POST_TYPE_WONEN . '\', \'' . POST_TYPE_BOG . '\') ';
      $sql .= 'AND NOT EXISTS (';
        $sql .= 'SELECT true FROM ' . $this->db->prefix . 'postmeta ';
        $sql .= 'WHERE ' . $this->db->prefix . 'posts.ID = ' . $this->db->prefix . 'postmeta.post_id ';
        $sql .= 'AND meta_key LIKE \'%_uuid\'';
      $sql .= ')';

      $results      = $this->db->get_results($sql);

      foreach ($results as $result)
      {
        $this->cleanupOldStuff((int) $result->id);
      }

      $this->warnings[] = 'Removed ' . count($results) . ' objects that were not linked to a 3mcp project.';

      return;
    }

    /**
     * Cleanup meta data for no longer existing
     */
    private function cleanupOldStuff($postId)
    {
      if (!is_int($postId))
        throw new Exception(__METHOD__ . '; Invalid post ID');

      $this->deletePostFiles($postId);
      wp_delete_post($postId);

      $tableName  = $this->db->prefix .'postmeta';
      $sql        = 'DELETE FROM ' . $tableName . ' WHERE post_id = ' . $postId;

      $this->db->get_results($sql);
    }

    /**
    * @desc Synchronize projects
    *
    * @param void
    * @return void
    */
    public function syncProjects()
    {
      // Register categories if needed
      $this->registerCategories();

      // Cleanup a specific post is specified
      if (!empty($_GET['force_cleanup']) && is_numeric($_GET['force_cleanup']))
        $this->cleanupOldStuff((int) $_GET['force_cleanup']);
      // Cleanup posts not linked to a 3mcp project
      else if (!empty($_GET['force_cleanup_unlinked']))
        $this->cleanupNonLinkedProjects();

      $existingProjectUuids       = $this->retrieveProjectUuidsMapping();
      $existingRelationUuids      = $this->retrieveRelationUuidMapping();
      $groupedProjectEntityLinks  = $this->feedReader->getProjectEntityLinks();
      $processedProjectUuids      = array();
      $syncFromTimestamp          = isset($_GET['force']) ? 0 : get_option('yog-last-sync', 0);

      // Set timezone to europe/amsterdam
      date_default_timezone_set('Europe/Amsterdam');

      foreach ($groupedProjectEntityLinks as $scenario => $projectEntityLinks)
      {
        foreach ($projectEntityLinks as $uuid => $projectEntityLink)
        {
          $postId = null;

          try
          {
            $processedProjectUuids[]  = $uuid;
            $publicationDlm           = strtotime(date('c', strtotime($projectEntityLink->getDlm())));

            // Determine post type
            $postType                 = $this->determinePostTypeByScenario($scenario);

            // Only process supported scenario's with publication dlm before sync from timestamp
            if (!is_null($postType) && $publicationDlm >= $syncFromTimestamp)
            {
              // Check if project already exists
              $existingProject          = array_key_exists($uuid, $existingProjectUuids);
              $postId                   = ($existingProject) ? $existingProjectUuids[$uuid] : null;
              $postDlm                  = $this->retrievePostDlm($postId, $postType);

              $forceSync = false;
              $futureDay = strtotime('+1 day'); // Take in account a small difference between server time settings

              // In case the post dlm is in the future, something might be wrong, do a sync
              if ($postDlm > $futureDay)
              {
                $this->errors[] = 'Project has a date in the future, sync is forced ' . $uuid . (is_null($postId) ? '' : ' (post ID: ' . $postId . ')') .  '.';
                $forceSync = true;
              }

              if ($publicationDlm > $postDlm || (isset($_GET['force']) && $postId == $_GET['force']) || $forceSync)
              {
                // Prevent timeouts by flushing content
                if (defined('YOG_PREVENT_TIMEOUTS') && YOG_PREVENT_TIMEOUTS == true)
                {
                  echo ' ';
                  flush();
                }

                $mcp3Project            = $this->feedReader->retrieveProjectByLink($projectEntityLink);
                $translationProject     = YogProjectTranslationAbstract::create($mcp3Project, $projectEntityLink);

                // Determine post data
                $postData = $translationProject->getPostData();
                // Add parent post id to post data if needed
                if ($translationProject->hasParentUuid())
                {
                  $parentUuid = $translationProject->getParentUuid();
                  if (!array_key_exists($parentUuid, $existingProjectUuids))
                    throw new YogException(__METHOD__ . '; Parent project with uuid ' . $parentUuid . ' not found', YogException::GLOBAL_ERROR);

                  $postData['post_parent'] = $existingProjectUuids[$parentUuid];
                }

                // Insert / Update post
                if ($existingProject)
                {
                  @wp_update_post(array_merge(array('ID' => $postId), $postData));
                }
                else
                {
                  $postId = @wp_insert_post($postData);
                  // Add to extisting projects array
                  $existingProjectUuids[$uuid] = $postId;
                }

                // Store meta data
                $this->handlePostMetaData($postId, $postType, $translationProject->getMetaData());

                // Store price to order by
                update_post_meta($postId, 'yog_price_order', $translationProject->determineSortPrice());

                // Handle linked relations
                $existingLinkedRelations  = array_intersect_key($translationProject->getRelationLinks(), $existingRelationUuids);
                $relations                = array();
                foreach ($existingLinkedRelations as $uuid => $role)
                {
                  $relations[$uuid] = array('rol' => $role, 'postId' => $existingRelationUuids[$uuid]);
                }
                update_post_meta($postId, $postType . '_Relaties', $relations);

                // Handle video
                $this->handleMediaLink($postId, $postType, 'Videos', $translationProject->getVideos());

                // Handle external documents
                $this->handleMediaLink($postId, $postType, 'Documenten', $translationProject->getExternalDocuments());

                // Handle links
                $this->handleMediaLink($postId, $postType, 'Links', $translationProject->getLinks());

                // Handle categories
                if (get_option('yog_cat_custom'))
                {
                  wp_set_object_terms($postId, $translationProject->getCategories(), 'yog_category', false);
                  wp_set_object_terms($postId, array(), 'category', false);
                }
                else
                {
                  wp_set_object_terms($postId, $translationProject->getCategories(), 'category', false);
                }

                // Handle tags
                wp_set_post_tags($postId, $translationProject->getTags(), false);

                // Handle images
                $this->handlePostImages($postId, $mcp3Project->getMediaImages());

                // Handle dossier items
                $this->handlePostDossier($postId, $mcp3Project->getDossierItems());

                // Store Yes-co ORES post dlm
                update_post_meta($postId, 'yog_post_dlm', date('U'));
                
                $this->handledProjects[] = $postId;
              }
            }
            else if (is_null($postType))
            {
              $this->warnings[] = 'Unsupported scenario ' . $scenario;
            }
          }
          catch (Exception $e)
          {
            $this->errors[] = 'Exception on project ' . $uuid . (is_null($postId) ? '' : ' (post ID: ' . $postId . ')') .  ': ' . $e->getMessage();
          }
        }
      }

      /* Cleanup old projects */
      $deleteProjectUuids = array_diff(array_flip($existingProjectUuids), $processedProjectUuids);

      foreach ($deleteProjectUuids as $uuid)
      {
        $postId = $existingProjectUuids[$uuid];

        $this->deletePostFiles($postId);
        wp_delete_post($postId);
      }

      // Check if there are project's with open house category that shouldn't have it anymore
      yog_cronUpdateOpenHouses();
    }

    /**
    * @desc Check if there are warnings
    *
    * @param void
    * @return bool
    */
    public function hasWarnings()
    {
      return count($this->warnings) > 0;
    }

    /**
    * @desc Get the warnings
    *
    * @param void
    * @return array
    */
    public function getWarnings()
    {
      return $this->warnings;
    }

    /**
    * @desc Check if there are errors
    *
    * @param void
    * @return bool
    */
    public function hasErrors()
    {
      return count($this->errors) > 0;
    }

    /**
    * @desc Get the errors
    *
    * @param void
    * @return array
    */
    public function getErrors()
    {
      return $this->errors;
    }

    /**
    * @desc Determine post type based on the scenario
    *
    * @param string $scenario
    * @return void (string or null)
    */
    private function determinePostTypeByScenario($scenario)
    {
      $postType                 = null;
      switch ($scenario)
      {
        case 'BBvk':
        case 'BBvh':
        case 'NBvk':
        case 'NBvh':
        case 'LIvk':
          $postType = POST_TYPE_WONEN;
          break;
        case 'BOvk':
        case 'BOvh':
          $postType = POST_TYPE_BOG;
          break;
        case 'NBpr':
          $postType = POST_TYPE_NBPR;
          break;
        case 'NBty':
          $postType = POST_TYPE_NBTY;
          break;
        case 'NBbn':
          $postType = POST_TYPE_NBBN;
          break;
        case 'BBpr':
          $postType = POST_TYPE_BBPR;
          break;
        case 'BBty':
          $postType = POST_TYPE_BBTY;
          break;
      }

      return $postType;
    }

    /**
    * @desc Store images for a post
    *
    * @param int $parentPostId
    * @param array $mcp3Images
    * @return void
    */
    private function handlePostImages($parentPostId, $mcp3Images)
    {
      if (!is_null($this->uploadDir))
      {
	      // Create projects directory (if needed)
	      if (!is_dir($this->uploadDir .'projecten/' .$parentPostId))
        {
	        if (!is_dir($this->uploadDir .'projecten'))
		        mkdir($this->uploadDir . 'projecten');

		      mkdir($this->uploadDir . 'projecten/' .$parentPostId);
        }
        
        // Determine prefered media size
        $mediaSize  = get_option('yog_media_size', 'medium');
        
        // Determine existing media
        $results              = $this->db->get_results("SELECT ID, post_content AS uuid FROM " . $this->db->prefix . "posts WHERE post_parent = " . $parentPostId . " AND post_type = '" . POST_TYPE_ATTACHMENT . "' AND post_content != '' AND post_mime_type IN ('image/jpeg', 'image/jpg', 'image/pjpeg')");
        $existingMediaMapping = array();

        if (is_array($results))
        {
          foreach ($results as $result)
          {
            $existingMediaMapping[$result->uuid] = $result->ID;
          }
        }

        $mainPhotoId          = get_post_meta($parentPostId, '_thumbnail_id', true);
        if (empty($mainPhotoId))
          $mainPhotoId        = null;
        $processedMediaUuids  = array();

        // Handle images
        foreach ($mcp3Images as $mcp3Image)
        {
          try
          {
            $uuid                   = $mcp3Image->getUuid();
            $processedMediaUuids[]  = $uuid;
            $imageLink              = $this->feedReader->getMediaLinkByUuid($uuid, $mediaSize);
            $publicationDlm         = strtotime(date('c', strtotime($imageLink->getDlm())));
            $existingMedia          = array_key_exists($uuid, $existingMediaMapping);
            $attachmentId           = ($existingMedia === true) ? $existingMediaMapping[$uuid] : null;
            $attachmenDlm           = $this->retrievePostDlm($attachmentId, POST_TYPE_ATTACHMENT);

            if (!$existingMedia || ($publicationDlm > $attachmenDlm) || (!empty($_GET['force']) && $_GET['force'] == $parentPostId))
            {
              $translationImage = YogImageTranslation::create($mcp3Image, $imageLink);

              $imageData = YogHttpManager::retrieveContent($imageLink->getUrl());

              if ($imageData !== false)
              {
                // Copy image
	              $destination    = $this->uploadDir .'projecten/' . $parentPostId . '/' . $uuid . '.jpg';
                file_put_contents($destination, $imageData);

                // Determine image data
                $imagePostData      = $translationImage->getPostData();
                if (!is_null($attachmentId))
                  $imagePostData['ID'] = $attachmentId;

                // Update / insert attachment
	              $attachmentId   = wp_insert_attachment($imagePostData, $destination, $parentPostId);
	              $attachmentMeta = wp_generate_attachment_metadata($attachmentId, $destination);
	              wp_update_attachment_metadata($attachmentId, $attachmentMeta);

                // Set meta data
                foreach ($translationImage->getMetaData() as $key => $value)
                {
                  if (!empty($value))
                    update_post_meta($attachmentId, POST_TYPE_ATTACHMENT . '_' . $key, $value);
                  else
                    delete_post_meta($attachmentId, POST_TYPE_ATTACHMENT . '_' . $key);
                }
              }
              else
              {
                $this->warnings[] = 'Failed to retrieve image data';
              }
            }

            // Is image the main image?
            if ($mcp3Image->getOrder() == 1)
              $mainPhotoId = $attachmentId;
          }
          catch (Exception $e)
          {
            $this->warnings[] = $e->getMessage();
          }
        }

        // Set main photo
        if (!is_null($mainPhotoId))
        {
        	if (function_exists('set_post_thumbnail'))
        		set_post_thumbnail($parentPostId, $mainPhotoId);
        	else
          	update_post_meta($parentPostId, '_thumbnail_id', $mainPhotoId);
        }
        else
        {
        	if (function_exists('delete_post_thumbnail'))
        		delete_post_thumbnail($parentPostId);
        	else
          	delete_post_meta($parentPostId, '_thumbnail_id');
        }

        /* Cleanup old media */
        $deleteMediaUuids = array_diff(array_flip($existingMediaMapping), $processedMediaUuids);

        foreach ($deleteMediaUuids as $uuid)
        {
          $attachmentId = $existingMediaMapping[$uuid];
          wp_delete_attachment($attachmentId, true);

          // Remove files
          if (!is_null($this->uploadDir) && is_dir($this->uploadDir .'projecten/' .$parentPostId))
          {
            $files = glob($this->uploadDir .'projecten/' .$parentPostId . '/' . $uuid . '*');
            if (is_array($files))
            {
              foreach ($files as $file)
              {
                if (is_file($file))
                {
                  if (!@unlink($file))
                    $this->warning[] = 'Unable to unlink ' . $file;
                }
              }
            }
          }
        }
      }
    }

    /**
    * @desc Store dossier items for a post
    *
    * @param int $parentPostId
    * @param array $mcp3DossierItems
    * @return void
    */
    private function handlePostDossier($parentPostId, $mcp3DossierItems)
    {
      if (!is_null($this->uploadDir))
      {
	      // Create projects directory (if needed)
	      if (!is_dir($this->uploadDir .'projecten/' .$parentPostId))
        {
	        if (!is_dir($this->uploadDir .'projecten'))
		        mkdir($this->uploadDir . 'projecten');

		      mkdir($this->uploadDir . 'projecten/' .$parentPostId);
        }

        $results                = $this->db->get_results("SELECT ID, post_content AS uuid FROM " . $this->db->prefix . "posts WHERE post_parent = " . $parentPostId . " AND post_type = '" . POST_TYPE_ATTACHMENT . "' AND post_content != '' AND post_mime_type NOT IN ('image/jpeg', 'image/jpg', 'image/pjpeg')");
        $existingDossierMapping = array();

        if (is_array($results))
        {
          foreach ($results as $result)
          {
            $existingDossierMapping[$result->uuid] = $result->ID;
          }
        }

        $processedDossierUuids  = array();
        $possibleMimeTypes      = explode(';',get_option('yog_dossier_mimetypes', 'application/pdf'));

        // Handle dossier items
        foreach ($mcp3DossierItems as $mcp3DossierItem)
        {
          try
          {
            $uuid                     = $mcp3DossierItem->getUuid();
            $dossierLink              = $this->feedReader->getDossierLinkByUuid($uuid);

            // Only handle dossier items (category document is kadastrale kaart)
            if ($dossierLink->getCategory() == 'dossier' && !in_array($dossierLink->getMimeType(), array('image/jpeg', 'image/jpg', 'image/pjpeg')))
            {
              $processedDossierUuids[]  = $uuid;
              $publicationDlm           = strtotime(date('c', strtotime($dossierLink->getDlm())));
              $existingDocument         = array_key_exists($uuid, $existingDossierMapping);
              $attachmentId             = ($existingDocument === true) ? $existingDossierMapping[$uuid] : null;
              $attachmenDlm             = $this->retrievePostDlm($attachmentId, POST_TYPE_ATTACHMENT);

              if (!$existingDocument || ($publicationDlm > $attachmenDlm) || (!empty($_GET['force']) && $_GET['force'] == $parentPostId))
              {
                $translationDossier = YogDossierTranslation::create($mcp3DossierItem, $dossierLink);
                $dossierData = YogHttpManager::retrieveContent($dossierLink->getUrl());

                if ($dossierData !== false)
                {
                  // Copy dossier item
                  $extension      = pathinfo($dossierLink->getUrl(), PATHINFO_EXTENSION);
                  $destination    = $this->uploadDir .'projecten/' . $parentPostId . '/' . $uuid  . (empty($extension) ? '' : '.' . $extension);
                  file_put_contents($destination, $dossierData);

                  // Determine image data
                  $dossierPostData  = $translationDossier->getPostData();
                  if (!is_null($attachmentId))
                    $dossierPostData['ID'] = $attachmentId;

                  // Update / insert attachment
                  $attachmentId   = wp_insert_attachment($dossierPostData, $destination, $parentPostId);

                  // Set meta data
                  foreach ($translationDossier->getMetaData() as $key => $value)
                  {
                    if (!empty($value))
                      update_post_meta($attachmentId, POST_TYPE_ATTACHMENT . '_' . $key, $value);
                    else
                      delete_post_meta($attachmentId, POST_TYPE_ATTACHMENT . '_' . $key);
                  }

                  // Add mime type to possible mime types
                  $possibleMimeTypes[] = $dossierLink->getMimeType();
                }
                else
                {
                  $this->warnings[] = 'Failed to retrieve dossier item data';
                }
              }
            }
          }
          catch (Exception $e)
          {
            $this->warnings[] = $e->getMessage();
          }
        }

        // Update possible mime types
        update_option('yog_dossier_mimetypes', implode(';', array_unique($possibleMimeTypes)));

        /* Cleanup old dossier items */
        $deleteDossierUuids = array_diff(array_flip($existingDossierMapping), $processedDossierUuids);

        foreach ($deleteDossierUuids as $uuid)
        {
          $attachmentId = $existingDossierMapping[$uuid];
          wp_delete_attachment($attachmentId, true);

          // Remove files
          if (!is_null($this->uploadDir) && is_dir($this->uploadDir .'projecten/' .$parentPostId))
          {
            $files = glob($this->uploadDir .'projecten/' .$parentPostId . '/' . $uuid . '*');
            if (is_array($files))
            {
              foreach ($files as $file)
              {
                if (is_file($file))
                {
                  if (!@unlink($file))
                    $this->warning[] = 'Unable to unlink ' . $file;
                }
              }
            }
          }
        }
      }
    }

    /**
    * @desc Delete post images
    *
    * @param int $postId
    * @return void
    */
    private function deletePostFiles($postId)
    {
      $postType = POST_TYPE_ATTACHMENT;

	    // Remove attachment links
	    $attachmentPostIds = $this->db->get_col("SELECT ID FROM " . $this->db->prefix . "posts WHERE post_parent = " . $postId . " AND post_type = '" . $postType . "' AND post_content != ''");
	    foreach ($attachmentPostIds as $attachmentPostId)
      {
		    wp_delete_attachment($attachmentPostId, true);
	    }

	    if (!is_null($this->uploadDir) && is_dir($this->uploadDir .'projecten/' .$postId))
	    {
	    	// Remove remaining files from projects/$postId folder
	    	$files = glob($this->uploadDir .'projecten/' .$postId . '/*');
	    	if (is_array($files))
	    	{
	    		foreach ($files as $file)
	    		{
	    			if (is_file($file))
	    			{
	    				if (!@unlink($file))
	    					$this->warning[] = 'Unable to unlink ' . $file;
	    			}
	    		}
	    	}

	    	// Unlink post directory
	    	if (!@rmdir($this->uploadDir .'projecten/' .$postId))
	    		$this->warning[] = 'Unable to rmdir ' . $this->uploadDir .'projecten/' .$postId;
	    }
    }

    /**
    * @desc Handle media links
    *
    * @param int $postId
    * @param string $postType
    * @param string $type
    * @param array $mediaLinks
    */
    private function handleMediaLink($postId, $postType, $type, $newMediaLinks)
    {
      $metaKey    = $postType . '_' . $type;

      // Retrieve already set media links
      $mediaLinks = get_post_meta($postId, $metaKey, true);
      if (!is_array($mediaLinks))
        $mediaLinks = array();

      // Remove all media links not added through WP admin
		  foreach ($mediaLinks as $uuid => $mediaLink)
      {
			  if (strpos($mediaLink['uuid'],'zelftoegevoegd') === false)
				  unset($mediaLinks[$mediaLink['uuid']]);
		  }

      // Add new media links to array
      $mediaLinks = array_merge($mediaLinks, $newMediaLinks);

      // Store media links
      update_post_meta($postId, $metaKey, $mediaLinks);
    }

    /**
    * @desc Retrieve post dlm
    *
    * @param mixed $postId
    * @return int
    */
    private function retrievePostDlm($postId, $postType)
    {
      $dlm = 0;

      if (is_numeric($postId))
      {
        // First try Yes-co ORES post dlm
        $dlm = get_post_meta($postId, 'yog_post_dlm', true);

        if (!empty($dlm) && is_numeric($dlm)) // Returned is already a timestamp in a string type
          return (int)$dlm;

        // Fallback to wordpress post dlm
        if (empty($dlm))
          $dlm = get_post_meta($postId, $postType . '_dlm', true); // Format returned is for exmaple 16-jan-2016

        $dlm = strtotime($dlm);
      }

      return $dlm;
    }

    /**
    * @desc Retrieve relation uuid mapping
    *
    * @param void
    * @return array
    */
    private function retrieveRelationUuidMapping()
    {
      $postType   = YogRelationTranslationAbstract::POST_TYPE;
      $metaKey    = $postType . '_' . $this->systemLink->getCollectionUuid() . '_uuid';
      $tableName  = $this->db->prefix .'postmeta';
      $results    = $this->db->get_results("SELECT post_id, meta_value AS uuid FROM " . $tableName . " WHERE meta_key = '" . $metaKey . "'");
      $uuids      = array();

      foreach ($results as $result)
      {
        $uuids[$result->uuid] = (int) $result->post_id;
      }

      return $uuids;
    }

    /**
    * @desc Retrieve project uuid mapping
    *
    * @param void
    * @return array
    */
    private function retrieveProjectUuidsMapping()
    {
      $collectionUuid = $this->systemLink->getCollectionUuid();
      $metaKeyWonen   = POST_TYPE_WONEN . '_' . $collectionUuid . '_uuid';
      $metaKeyBog     = POST_TYPE_BOG . '_' . $collectionUuid . '_uuid';
      $metaKeyNBpr    = POST_TYPE_NBPR . '_' . $collectionUuid . '_uuid';
      $metaKeyNBty    = POST_TYPE_NBTY . '_' . $collectionUuid . '_uuid';
      $metaKeyNBbn    = POST_TYPE_NBBN . '_' . $collectionUuid . '_uuid';
      $metaKeyBBpr    = POST_TYPE_BBPR . '_' . $collectionUuid . '_uuid';
      $metaKeyBBty    = POST_TYPE_BBTY . '_' . $collectionUuid . '_uuid';

      $tableName    = $this->db->prefix .'postmeta';
      $results      = $this->db->get_results("SELECT post_id, meta_value AS uuid FROM " . $tableName . " WHERE meta_key IN ('" . $metaKeyWonen . "', '" . $metaKeyBog . "', '" . $metaKeyNBpr . "', '" . $metaKeyNBty . "', '" . $metaKeyNBbn . "', '" . $metaKeyBBpr . "', '" . $metaKeyBBty . "') ORDER BY post_id DESC");
      $uuids        = array();

      foreach ($results as $result)
      {
        if (array_key_exists($result->uuid, $uuids))
        {
          $this->warnings[] = 'Duplicate UUID: ' . $result->uuid . '(Post ID: ' . $result->post_id . ', org post ID: ' . $uuids[$result->uuid] . ')';
        }

        $uuids[$result->uuid] = (int) $result->post_id;
      }

      return $uuids;
    }

    /**
    * Store meta data for a specific post
    *
    * @param int $postId
    * @param string $postType
    * @param array $metaData
    * @return void
    */
    private function handlePostMetaData($postId, $postType, $metaData)
    {
      // Add uuid / collection uuid mapping to meta data
      if (isset($metaData['uuid']))
        $metaData[$this->systemLink->getCollectionUuid() . '_uuid'] = $metaData['uuid'];

      // Retrieve current meta data
      $oldFields = get_post_custom_keys((int) $postId);
      if (empty($oldFields))
        $oldFields = array();

      // Insert new meta data
      $updatedFields = array();
      if (count($metaData) > 0)
      {
		    foreach ($metaData as $key => $val)
        {
          if (!empty($val))
          {
				    update_post_meta($postId, $postType . '_' . $key, $val);
            $updatedFields[] = $postType . '_' . $key;
          }
		    }
      }

      /* Cleanup old meta data */
      // Do not delete media link / relation fields
      $deleteFields = array_diff($oldFields, array($postType . '_Relaties', $postType . '_Links', $postType . '_Documenten', $postType . '_Videos'));
      // Do not delete updated fields
      $deleteFields = array_diff($deleteFields, $updatedFields);

      // Do not delete the relation latitude / longitude fields upon syncing (they are not available in
      // Yes-co yet so we need to prevent them from being deleted when edited manually in WordPress)
      $deleteFields = array_diff($deleteFields, array( 'relatie_Longitude', 'relatie_Latitude' ));

      if (is_array($deleteFields) && count($deleteFields) > 0)
      {
        foreach ($deleteFields as $deleteField)
        {
          delete_post_meta((int) $postId, $deleteField);
        }
      }
    }

    /**
    * @desc Register project categories if needed
    *
    * @param void
    * @return void
    */
    private function registerCategories()
    {
      $consumentId  = $this->createCategory('Consument',            'consument');
      $woonruimteId = $this->createCategory('Woonruimte',           'woonruimte',           $consumentId);
      $bogId        = $this->createCategory('Bedrijf',              'bedrijf');
      $nbId         = $this->createCategory('Nieuwbouw projecten',  'nieuwbouw-projecten');
      $nbprId       = $this->createCategory('Nieuwbouw project',    'nieuwbouw-project',    $nbId);
      $nbtyId       = $this->createCategory('Nieuwbouw type',       'nieuwbouw-type',       $nbId);
      $nbbnId       = $this->createCategory('Nieuwbouw bouwnummer', 'nieuwbouw-bouwnummer', $nbId);
      $complexId    = $this->createCategory('Complexen',            'complexen');
      $bbprId       = $this->createCategory('Complex',              'complex',              $complexId);
      $bbtyId       = $this->createCategory('Complex type',         'complex-type',         $complexId);

      $categoryIdMapping = array(
        'consument'             => $consumentId,
        'woonruimte'            => $woonruimteId,
        'bedrijf'               => $bogId,
        'nieuwbouw-projecten'   => $nbprId,
        'nieuwbouw-type'        => $nbtyId,
        'nieuwbouw-bouwnummer'  => $nbbnId,
      );

	    // Subcategories
	    $subcategories  = array($consumentId  => array( 'bestaand'            => 'Bestaand',
                                                      'nieuwbouw'           => 'Nieuwbouw',
                                                      'open-huis'           => 'Open huis',
                                                      'bouwgrond'           => 'Bouwgrond',
                                                      'parkeergelegenheid'  => 'Parkeergelegenheid',
                                                      'berging'             => 'Berging',
                                                      'standplaats'         => 'Standplaats',
                                                      'ligplaats'           => 'Ligplaats',
                                                      'verhuur'             => 'Verhuur',
                                                      'verkoop'             => 'Verkoop',
                                                      'verkochtverhuurd'    => 'Verkocht/verhuurd'),
                              $woonruimteId => array( 'appartement'         => 'Appartement',
                                                      'woonhuis'            => 'Woonhuis'),
                              $bogId        => array( 'bog-bestaand'          => 'Bestaand',
                                                      'bog-nieuwbouw'         => 'Nieuwbouw',
                                                      'bog-verkoop'           => 'Verkoop',
                                                      'bog-verhuur'           => 'Verhuur',
                                                      'bog-verkochtverhuurd'  => 'Verkocht/verhuurd',
                                                      'bedrijfsruimte'        => 'Bedrijfsruimte',
                                                      'bog-bouwgrond'         => 'Bouwgrond',
                                                      'horeca'                => 'Horeca',
                                                      'kantoorruimte'         => 'Kantoorruimte',
                                                      'winkelruimte'          => 'Winkelruimte'),
                              $nbprId       => array( 'nieuwbouw-project-verkoop'             => 'Verkoop',
                                                      'nieuwbouw-project-verhuur'             => 'Verhuur',
                                                      'nieuwbouw-project-verkochtverhuurd'    => 'Verkocht/verhuurd'),
                              $nbtyId       => array( 'nieuwbouw-type-verkoop'                => 'Verkoop',
                                                      'nieuwbouw-type-verhuur'                => 'Verhuur'),
                              $nbbnId       => array( 'nieuwbouw-bouwnummer-verkochtverhuurd' => 'Verkocht/verhuurd'),
                              $bbprId       => array( 'complex-verkoop'                       => 'Verkoop',
                                                      'complex-verhuur'                       => 'Verhuur'),
                              $bbtyId       => array( 'complex-type-verkoop'                  => 'Verkoop',
                                                      'complex-type-verhuur'                  => 'Verhuur')
                              );

      $this->registerNewThemeCategories($categoryIdMapping, $subcategories);

      // Create subcategories
      foreach ($subcategories as $parentId => $values)
      {
	      foreach ($values as $slug => $name)
        {
          $this->createCategory($name, $slug, $parentId);
	      }
      }
    }

    /**
     * @desc Method registerNewThemeCategories Allow the theme to influence creation of extra categories
     *
     * @param {Array} $categoryIdMapping
     * @param {Array} $subcategories
     * @return {Void}
     */
    private function registerNewThemeCategories($categoryIdMapping, &$subcategories)
    {
      $templateDir = get_template_directory();

      // Include the Theme's function directory
      if (file_exists($templateDir . '/functions.php'))
        require_once($templateDir . '/functions.php');

      // Execute the hook if provided in the functions.php
      if (function_exists('yog_plugin_register_new_categories'))
      {
        $extendCategories = yog_plugin_register_new_categories($categoryIdMapping);

        if (is_array($extendCategories))
        {
          foreach ($extendCategories as $categoryId => $values)
          {
            if (is_numeric($categoryId) && isset($subcategories[$categoryId]))
            {
              $currentValues = $subcategories[$categoryId];

              if (is_array($values))
              {
                $currentValues = array_merge($currentValues, $values);

                // Overwrite the current categories
                $subcategories[$categoryId] = $currentValues;
              }
            }
          }
        }
      }
    }

    /**
    * @desc Create a term (if not existing
    *
    * @param string $name
    * @param int $parentTermId (optional)
    * @return int
    */
    private function createCategory($name, $slug, $parentTermId = 0)
    {
      $categoryTaxonomy = (get_option('yog_cat_custom') ? 'yog_category' : 'category');
	    $term             = get_term_by('slug', $slug, $categoryTaxonomy, ARRAY_A);

	    if (!$term)
		    $term   = wp_insert_term($name, $categoryTaxonomy, array('description' => $name, 'parent' => $parentTermId, 'slug' => $slug));

      if ($term instanceOf WP_Error)
      {
        return (int) $term->error_data['term_exists'];
      }
      else
      {
        return (int) $term['term_id'];
      }
    }

    /**
     * Send error email to admin (or configured email address in YOG_ERRORS_RECIPIENT)
     * @param array $errors
     * @return void
     */
    private function sendErrorMail($errors)
    {
      if (!empty($_GET['force']))
        return;

      $emailTo = (defined('YOG_ERRORS_RECIPIENT') && strlen(trim(YOG_ERRORS_RECIPIENT)) > 0) ? YOG_ERRORS_RECIPIENT : 'wordpress-errors@yes-co.nl';
      $subject = 'WordPress synchronisation errors';

      $message  = 'Site title: ' . get_bloginfo('name') . "\r\n";
      $message .= 'Url: ' . get_bloginfo('url') . "\r\n";
      $message .= 'Errors:' . "\r\n";

      foreach ($errors as $error)
      {
        $message .= '- ' . $error . "\r\n";
      }

      wp_mail($emailTo, $subject, $message);
    }
  }