<?php

  /**
  * @desc YogHttpManager
  * @author Stefan van Zanden - Yes-co Nederland
  */
  class YogHttpManager
  {
    /**
     * @desc Method url
     *
     * @param {String} $url
     * @param {Boolean} $authenticate
     * @return {String}
     */
    public static function retrieveContent($url, $authenticate = false)
    {
      // try and use callbacks in case it doesn't work
      $manager  = new self();
      $content  = $manager->retrieveContentByFileGetContents($url, $authenticate);

      if ($content === false)
      {
        $content = $manager->retrieveContentByCurl($url);

        if ($content === false)
        {
          $content = $manager->retrieveContentByWordpress($url);
        }
      }

      return $content;
    }

    /**
     * @desc Method retrieveContentByFileGetContents
     *
     * @param {String} $url
     * @param {Boolean} $authenticate
     * @return {Mixed}
     */
    private function retrieveContentByFileGetContents($url, $authenticate)
    {
      try
      {
        if (!ini_get('allow_url_fopen'))
          return false;

        if ($authenticate)
        {
	        // Forceer HTTP 1.0 IVM Authenticatie via url
	        ini_set('user_agent','MSIE 4\.0b2;');
        }

        $content = @file_get_contents($url);

        if ($content === false)
          return false;
      }
      catch (Exception $e)
      {
        return false;
      }

      return $content;
    }

    /**
     * @desc Method retrieveContentByCurl
     *
     * @param {String} $url
     * @return {Mixed}
     */
    private function retrieveContentByCurl($url)
    {
      try
      {
        if (!function_exists('curl_init'))
          return false;

        // Check for username / password
        $pos = strrpos($url, '@');
        if ($pos !== false)
        {
          $protocol = substr($url, 0, strpos($url, '://')) . '://';
          $userPwd  = str_replace($protocol, '', substr($url, 0, $pos));
          $url      = $protocol . substr($url, $pos + 1);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (!empty($userPwd))
          curl_setopt($ch, CURLOPT_USERPWD, $userPwd);

        $content  = curl_exec($ch);
        $info     = curl_getinfo($ch);

        curl_close($ch);

        if (!empty($info['http_code']) && !in_array($info['http_code'], array(200, 304)))
          return false;
      }
      catch (Exception $e)
      {
        return false;
      }

      return $content;
    }

    /**
     * @desc Method retrieveContentByWordpress
     *
     * @param {String} $url
     * @return {Mixed}
     */
    private function retrieveContentByWordpress($url)
    {
      try
      {
        $content = wp_remote_fopen($url);
      }
      catch (Exception $e)
      {
        return false;
      }

      return $content;
    }

  }