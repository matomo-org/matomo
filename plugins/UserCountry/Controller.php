<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_UserCountry
 */

/**
 *
 * @package Piwik_UserCountry
 */
class Piwik_UserCountry_Controller extends Piwik_Controller 
{
	function index()
	{
		$view = Piwik_View::factory('index');
		
		$view->urlSparklineCountries = $this->getUrlSparkline('getLastDistinctCountriesGraph');
		$view->numberDistinctCountries = $this->getNumberOfDistinctCountries(true);
		
		$view->dataTableCountry = $this->getCountry(true);
		$view->dataTableContinent = $this->getContinent(true);
		$view->dataTableRegion = $this->getRegion(true);
		$view->dataTableCity = $this->getCity(true);
		
		echo $view->render();
	}
	
	function adminIndex()
	{
		Piwik::checkUserIsSuperUser();
		$view = Piwik_View::factory('adminIndex');
		
		$view->locationProviders = Piwik_UserCountry_LocationProvider::getAllProviderInfo(
			$newline = '<br/>', $includeExtra = true);
		$view->currentProviderId = Piwik_UserCountry_LocationProvider::getCurrentProviderId();
		$view->thisIP = Piwik_IP::getIpFromHeader();
		
		// check if there is a working provider (that isn't the default one)
		$view->isThereWorkingProvider = false;
		foreach ($view->locationProviders as $id => $provider)
		{
			if ($id != Piwik_UserCountry_LocationProvider_Default::ID
				&& $provider['status'] == Piwik_UserCountry_LocationProvider::INSTALLED)
			{
				$view->isThereWorkingProvider = true;
				break;
			}
		}

		$this->setBasicVariablesView($view);
		Piwik_Controller_Admin::setBasicVariablesAdminView($view);
		$view->menu = Piwik_GetAdminMenu();
		
		echo $view->render();
	}
	
	/**
	 * Sets the current LocationProvider type.
	 * 
	 * Input:
	 *   Requires the 'id' query parameter to be set to the desired LocationProvider's ID.
	 * 
	 * Output:
	 *   Nothing.
	 */
	public function setCurrentLocationProvider()
	{
		Piwik::checkUserIsSuperUser();
		if ($_SERVER["REQUEST_METHOD"] == "POST")
		{
			$this->checkTokenInUrl();
			
			$providerId = Piwik_Common::getRequestVar('id');
			$provider = Piwik_UserCountry_LocationProvider::setCurrentProvider($providerId);
			if ($provider === false)
			{
				throw new Exception("Invalid provider ID: '$providerId'.");
			}
			
			// make sure the tracker will use the new location provider
			Piwik_Common::regenerateCacheGeneral();
		}
	}
	
	/**
	 * Echo's a pretty formatted location using a specific LocationProvider.
	 * 
	 * Input:
	 *   The 'id' query parameter must be set to the ID of the LocationProvider to use.
	 * 
	 * Output:
	 *   The pretty formatted location that was obtained. Will be HTML.
	 */
	public function getLocationUsingProvider()
	{
		$providerId = Piwik_Common::getRequestVar('id');
		$provider = $provider = Piwik_UserCountry_LocationProvider::getProviderById($providerId);
		if ($provider === false)
		{
			throw new Exception("Invalid provider ID: '$providerId'.");
		}
		
		$location = $provider->getLocation(array('ip' => Piwik_IP::getIpFromHeader(),
												 'lang' => Piwik_Common::getBrowserLanguage(),
									 			 'disable_fallbacks' => true));
		$location = Piwik_UserCountry_LocationProvider::prettyFormatLocation(
			$location, $newline = '<br/>', $includeExtra = true);
		
		echo $location;
	}
	
	function getCountry( $fetch = false)
	{
		$view = $this->getStandardDataTableUserCountry(__FUNCTION__, "UserCountry.getCountry");
		$view->setLimit( 5 );
		$view->setColumnTranslation('label', Piwik_Translate('UserCountry_Country'));
		$view->setReportDocumentation(Piwik_Translate('UserCountry_getCountryDocumentation'));
		return $this->renderView($view, $fetch);
	}

	function getContinent( $fetch = false)
	{
		$view = $this->getStandardDataTableUserCountry(__FUNCTION__, "UserCountry.getContinent", 'graphVerticalBar');
		$view->disableSearchBox();
		$view->disableOffsetInformationAndPaginationControls();
		$view->setColumnTranslation('label', Piwik_Translate('UserCountry_Continent'));
		$view->setReportDocumentation(Piwik_Translate('UserCountry_getContinentDocumentation'));
		return $this->renderView($view, $fetch);
	}
	
	/**
	 * Echo's or returns an HTML view of the visits by region report.
	 * 
	 * @param bool $fetch If true, returns the HTML as a string, otherwise it is echo'd.
	 * @return string
	 */
	public function getRegion( $fetch = false )
	{
		$view = $this->getStandardDataTableUserCountry(__FUNCTION__, "UserCountry.getRegion");
		$view->setLimit(5);
		$view->setColumnTranslation('label', Piwik_Translate('UserCountry_Region'));
		$view->setReportDocumentation(Piwik_Translate('UserCountry_getRegionDocumentation').'<br/>'
			. $this->getGeoIPReportDocSuffix());
		return $this->renderView($view, $fetch);
	}
	
	/**
	 * Echo's or returns an HTML view of the visits by city report.
	 * 
	 * @param bool $fetch If true, returns the HTML as a string, otherwise it is echo'd.
	 * @return string
	 */
	public function getCity( $fetch = false )
	{
		$view = $this->getStandardDataTableUserCountry(__FUNCTION__, "UserCountry.getCity");
		$view->setLimit(5);
		$view->setColumnTranslation('label', Piwik_Translate('UserCountry_City'));
		$view->setReportDocumentation(Piwik_Translate('UserCountry_getCityDocumentation').'<br/>'
			. $this->getGeoIPReportDocSuffix());
		return $this->renderView($view, $fetch);
	}
	
	private function getGeoIPReportDocSuffix()
	{
		return Piwik_Translate('UserCountry_GeoIPDocumentationSuffix', array(
			'<a href="http://www.maxmind.com/?rId=piwik">',
			'</a>',
			'<a href="http://www.maxmind.com/en/city_accuracy?rId=piwik">',
			'</a>'
		));
	}
	
	protected function getStandardDataTableUserCountry( $currentControllerAction, 
												$APItoCall,
												$defaultDatatableType = null )
	{
		$view = Piwik_ViewDataTable::factory( $defaultDatatableType );
		$view->init( $this->pluginName, $currentControllerAction, $APItoCall );
		$view->disableExcludeLowPopulation();
	
		$this->setPeriodVariablesView($view);
		$this->setMetricsVariablesView($view);
		
		$view->enableShowGoals();
		
		return $view;
	}
	
	function getNumberOfDistinctCountries( $fetch = false)
	{
		return $this->getNumericValue('UserCountry.getNumberOfDistinctCountries');
	}

	function getLastDistinctCountriesGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph('UserCountry',__FUNCTION__, "UserCountry.getNumberOfDistinctCountries");
		$view->setColumnsToDisplay('UserCountry_distinctCountries');
		return $this->renderView($view, $fetch);
	}
}
