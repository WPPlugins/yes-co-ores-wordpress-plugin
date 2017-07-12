<?php
class YogChecks
{
	/**
	 * Check for errors
	 *
	 * @param void
	 * @return array
	 */
	public static function checkForErrors()
	{
		$errors   = array();

		// Upload folder writable
		$uploadDir = wp_upload_dir();

		if (!empty($uploadDir['error']))
			$errors[] = $uploadDir['error'];
		else if (!is_writeable($uploadDir['basedir']))
			$errors[] = 'De upload map van uw WordPress installatie is beveiligd tegen schrijven. Dat betekent dat er geen afbeelingen van de objecten gesynchroniseerd kunnen worden. Stel onderstaande locatie zo in, dat deze beschreven kan worden door de webserver. <br /><i><b>' . $uploadDir['basedir'] .'</b></i>';

		// PHP version check
		if (!version_compare(PHP_VERSION, '5.2.1', '>='))
			$errors[] = 'PHP versie ' . PHP_VERSION . ' is gedetecteerd, de plugin vereist minimaal PHP versie 5.2.1. Neem contact op met je hosting provider om de PHP versie te laten upgraden';

		// Lib XML check
		if (!extension_loaded('libxml'))
			$errors[] = 'De php librairy <b>libxml</b> is niet geinstalleerd. Neem contact op met je hosting provider om libxml te laten installeren';

		// allow_url_fopen / CURL check
		if (!ini_get('allow_url_fopen') && !function_exists('curl_init'))
			$errors[] = 'De php setting <b>allow_url_fopen</b> staat uit en de php librairy <b>CURL</b> is niet geinstalleerd. Voor de synchronisatie is 1 van deze 2 noodzakelijk. Neem contact op met je hosting provider hierover.';

		// Wordpress version
		global $wp_version;
		if ((float) $wp_version < 3.1)
			$errors[] = 'Wordpress versie ' . $wp_version . ' is gedetecteerd, voor deze plugin is Wordpress versie 3.1 of hoger vereist. Upgrade wordpress naar een nieuwere versie';

		return $errors;
	}

	/**
	 * Check for warnings
	 *
	 * @param void
	 * @return array
	 */
	public static function checkForWarnings()
	{
		$warnings = array();

		// Single huis template check
    if (locate_template('single-huis.php') == '')
			$warnings[] = 'Het ingestelde thema heeft op dit moment geen \'single-huis.php\' template. Er zal een alternatieve methode gebruikt worden voor het tonen van de Wonen object details.';

		// Single bedrijf template check
    if (locate_template('single-bedrijf.php') == '')
			$warnings[] = 'Het ingestelde thema heeft op dit moment geen \'single-bedrijf.php\' template. Er zal een alternatieve methode gebruikt worden voor het tonen van de BOG object details.';

		// Single NBpr template check
    if (locate_template('single-yog-nbpr.php') == '')
			$warnings[] = 'Het ingestelde thema heeft op dit moment geen \'single-yog-nbpr.php\' template. Er zal een alternatieve methode gebruikt worden voor het tonen van de Nieuwbouw Project details.';

		// Single NBpr template check
    if (locate_template('single-yog-nbty.php') == '')
			$warnings[] = 'Het ingestelde thema heeft op dit moment geen \'single-yog-nbty.php\' template. Er zal een alternatieve methode gebruikt worden voor het tonen van de Nieuwbouw type details.';

    // Single BBpr template check
    if (locate_template('single-yog-bbpr.php') == '')
      $warnings[] = 'Het ingestelde thema heeft op dit moment geen \'single-yog-bbpr.php\' template. Er zal een alternatieve methode gebruikt worden voor het tonen van de Bestaande bouw complexen.';

    // Single BBpr template check
    if (locate_template('single-yog-bbty.php') == '')
      $warnings[] = 'Het ingestelde thema heeft op dit moment geen \'single-yog-bbty.php\' template. Er zal een alternatieve methode gebruikt worden voor het tonen van de Bestaande bouw complex types.';
    
		// PHP version check
		if (version_compare(PHP_VERSION, '5.2.1', '>=') && !version_compare(PHP_VERSION, '5.5', '>='))
			$warnings[] = 'PHP versie ' . PHP_VERSION . ' is gedetecteerd, voor deze php versie worden geen (beveiligings) updates meer uitgebracht. We raden je aan om contact op te nemen met je hosting provider om de PHP versie te laten upgraden. Er is ook garantie dat deze plugin blijft functioneren met deze php versie.';

		return $warnings;
	}

	/**
	 * Get wordpress settings
	 *
	 * @Param void
	 * @return array
	 */
	public static function getSettings()
	{
    if (!function_exists('get_plugin_data'))
      require_once(ABSPATH . 'wp-admin/includes/plugin.php' );

		$settings   = array();

		// Wordpress version
		global $wp_version;
		$settings['Wordpress version'] = $wp_version;

		// Plugin version
    $pluginData = get_plugin_data(dirname(__FILE__) . '/../../yesco-og.php');
		$settings['Plugin version'] = $pluginData['Version'];

		// PHP version
		$settings['PHP version'] = PHP_VERSION;

		// allow_url_fopen
		$settings['allow_url_fopen'] = (ini_get('allow_url_fopen')) ? 'enabled' : 'disabled';

    // Server date/time
    $settings['current date/time']  = date('c');

    if (function_exists('mysql_get_client_info'))
      $settings['mysql_version'] = mysql_get_client_info();

    // Max execution time
    $settings['max_execution_time'] = ini_get('max_execution_time');

		// CURL
		$settings['CURL'] = function_exists('curl_init') ? 'enabled' : 'disabled';

    // Wordpress settings
    $settings['Custom categories enabled']  = (get_option('yog_cat_custom') ? 'true' : 'false');
    $settings['3mcp version']               = get_option('yog_3mcp_version');
    $settings['Skip extra texts']           = (get_option('yog_noextratexts') ? 'true' : 'false');
    $settings['Synchronisation disabled']   = (get_option('yog_sync_disabled') ? 'true' : 'false');

    // Last sync
    $lastSync = get_option('yog-last-sync');
    if (!empty($lastSync))
      $settings['Last sync']  = date('c', $lastSync);

		return $settings;
	}
}