<?php
class Piwik_VisitsSummary_API extends Piwik_Apiable
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
	
	public function get( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $date, $period );
			
		$toFetch = array( 	'max_actions',
							'nb_uniq_visitors', 
							'nb_visits',
							'nb_actions', 
							'sum_visit_length',
							'bounce_count',
						);
		$dataTable = $archive->getDataTableFromNumeric($toFetch);

		return $dataTable;
	}
	
}

