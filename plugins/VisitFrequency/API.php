<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */

class Piwik_VisitFrequency_API extends Piwik_Apiable
{
	static private $instance = null;
	protected function __construct()
	{
		parent::__construct();
	}
	
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	public function getSummary( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $date, $period );
		$toFetch = array( 	
							'nb_visits_returning',
							'nb_actions_returning',
							'max_actions_returning',
							'sum_visit_length_returning',
							'bounce_count_returning',
				);
		$dataTable = $archive->getDataTableFromNumeric($toFetch);

		return $dataTable;
	}
	
}

