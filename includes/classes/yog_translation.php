<?php
  /**
  * @desc YogTranslationAbstract
  * @author Kees Brandenburg - Yes-co Nederland
  */
  abstract class YogTranslationAbstract
  {
    /**
    * @desc Translate date
    *
    * @param string $date
    * @return string
    */
    protected function translateDate($date)
    {
      $matches = array();

      if (preg_match("/([\d]{4}-[\d]{1,2}-[\d]{1,2})T([\d]{1,2}:[\d]{1,2}:[\d]{1,2})/", $date, $matches))
        return $matches[1] . ' ' . $matches[2];

      return $date;
    }

    /**
    * @desc Translate bool to ja/nee
    *
    * @param bool $bool
    * @return string
    */
    protected function translateBool($bool)
    {
      return ($bool == true) ? 'ja' : 'nee';
    }

    abstract public function getPostType();
    abstract public function getPostData();
    abstract public function getMetaData();
  }