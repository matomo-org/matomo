<?php
	
class Piwik_Plugin_VisitorInterest extends Piwik_Plugin
{	
	
	protected $timeGap = array(
			array(0, 0.5),
			array(0.5, 1),
			array(1, 2),
			array(2, 4),
			array(4, 6),
			array(6, 8),
			array(8, 11),
			array(11, 15),
			array(15)
		);
		
	protected $pageGap = array(
			array(1, 1),
			array(2, 2),
			array(3, 3),
			array(4, 4),
			array(5, 5),
			array(6, 7),
			array(8, 10),
			array(11, 14),
			array(15, 20),
			array(20)
		);
		
	public function __construct()
	{
	}

	public function getInformation()
	{
		$info = array(
			'name' => 'VisitorInterest',
			'description' => 'Several stats related to the visitor interest',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
		
		return $info;
	}
	
	function install()
	{
	}
	
	function uninstall()
	{
	}
	
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'ArchiveProcessing_Day.compute' => 'archiveDay'
		);
		return $hooks;
	}
	
	public function archiveDay( $notification )
	{
		$this->archiveProcessing = $notification->getNotificationObject();

		$recordName = 'VisitorInterest_timeGap';
		$tableTimegap = $this->getTableTimeGap();
		$record = new Piwik_Archive_Processing_Record_Blob_Array($recordName, $tableTimegap->getSerialized());
		
		$recordName = 'VisitorInterest_pageGap';
		$tablePagegap = $this->getTablePageGap();
		$record = new Piwik_Archive_Processing_Record_Blob_Array($recordName, $tablePagegap->getSerialized());
		
//		echo $tableTimegap;
//		echo $tablePagegap;
		
	}
	
	protected function getTablePageGap()
	{
		$select = array();
		foreach($this->pageGap as $gap)
		{
			if(count($gap) == 2)
			{
				$minGap = $gap[0];
				$maxGap = $gap[1];
				$gapName = "'$minGap-$maxGap'";
				$select[] = "sum(case when visit_total_actions between $minGap and $maxGap then 1 else 0 end) as $gapName ";
			}
			else
			{
				$minGap = $gap[0];
				$gapName = "'$minGap+'";
				$select[] = "sum(case when visit_total_actions > $minGap then 1 else 0 end) as $gapName ";
			}
		}		
		$toSelect = implode(" , ", $select);
		
		return $this->archiveProcessing->getSimpleDataTableFromSelect($toSelect, 'nb_visits');
	}
	
	protected function getTableTimeGap()
	{
		$select = array();
		foreach($this->timeGap as $gap)
		{
			if(count($gap) == 2)
			{
				$minGap = $gap[0] * 60;
				$maxGap = $gap[1] * 60;
				$gapName = "'$minGap-$maxGap'";
				$select[] = "sum(case when visit_total_time between $minGap and $maxGap then 1 else 0 end) as $gapName ";
			}
			else
			{
				$minGap = $gap[0] * 60;
				$gapName = "'$minGap+'";
				$select[] = "sum(case when visit_total_time > $minGap then 1 else 0 end) as $gapName ";
			}
		}		
		$toSelect = implode(" , ", $select);
		
		return $this->archiveProcessing->getSimpleDataTableFromSelect($toSelect, 'nb_visits');
	}
}