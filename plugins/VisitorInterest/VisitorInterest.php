<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_VisitorInterest
 */

/**
 *
 * @package Piwik_VisitorInterest
 */
class Piwik_VisitorInterest extends Piwik_Plugin
{	
	public function getInformation()
	{
		$info = array(
			'name' => 'VisitorInterest',
			'description' => Piwik_Translate('VisitorInterest_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
		
		return $info;
	}

	function getListHooksRegistered()
	{
		$hooks = array(
			'ArchiveProcessing_Day.compute' => 'archiveDay',
			'ArchiveProcessing_Period.compute' => 'archivePeriod',
			'WidgetsList.add' => 'addWidgets',
			'Menu.add' => 'addMenu',
		);
		return $hooks;
	}
	
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

	function addWidgets()
	{
		Piwik_AddWidget( 'General_Visitors', 'VisitorInterest_WidgetLengths', 'VisitorInterest', 'getNumberOfVisitsPerVisitDuration');
		Piwik_AddWidget( 'General_Visitors', 'VisitorInterest_WidgetPages', 'VisitorInterest', 'getNumberOfVisitsPerPage');
	}
	
	function addMenu()
	{
		Piwik_RenameMenuEntry('General_Visitors', 'VisitFrequency_SubmenuFrequency', 
							  'General_Visitors', 'VisitorInterest_SubmenuFrequencyLoyalty' );
	}

	function postLoad()
	{
		Piwik_AddAction('template_headerVisitsFrequency', array('Piwik_VisitorInterest','headerVisitsFrequency'));
		Piwik_AddAction('template_footerVisitsFrequency', array('Piwik_VisitorInterest','footerVisitsFrequency'));
	}
	
	
	function archivePeriod( $notification )
	{
		$archiveProcessing = $notification->getNotificationObject();
		
		$dataTableToSum = array( 
				'VisitorInterest_timeGap',
				'VisitorInterest_pageGap',
		);
		$archiveProcessing->archiveDataTable($dataTableToSum);
	}
	
	public function archiveDay( $notification )
	{
		$this->archiveProcessing = $notification->getNotificationObject();

		$recordName = 'VisitorInterest_timeGap';
		$tableTimegap = $this->getTableTimeGap();
		$this->archiveProcessing->insertBlobRecord($recordName, $tableTimegap->getSerialized());
		
		$recordName = 'VisitorInterest_pageGap';
		$tablePagegap = $this->getTablePageGap();
		$this->archiveProcessing->insertBlobRecord($recordName, $tablePagegap->getSerialized());
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
				$plusEncoded = urlencode('+');
				$gapName = "'".$minGap.$plusEncoded."'";
				$select[] = "sum(case when visit_total_actions > $minGap then 1 else 0 end) as $gapName ";
			}
		}		
		$toSelect = implode(" , ", $select);
		
		return $this->archiveProcessing->getSimpleDataTableFromSelect($toSelect, Piwik_Archive::INDEX_NB_VISITS);
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
				
				$gapName = "'".$minGap."-".$maxGap."'";
				$select[] = "sum(case when visit_total_time between $minGap and $maxGap then 1 else 0 end) as $gapName ";
			}
			else
			{
				$minGap = $gap[0] * 60;
				$gapName = "'$minGap'";
				$select[] = "sum(case when visit_total_time > $minGap then 1 else 0 end) as $gapName ";
			}
		}		
		$toSelect = implode(" , ", $select);
		
		$table = $this->archiveProcessing->getSimpleDataTableFromSelect($toSelect, Piwik_Archive::INDEX_NB_VISITS);
		return $table;
	}
	
	static public function headerVisitsFrequency($notification)
	{
		$out =& $notification->getNotificationObject();
		$out = '<div id="leftcolumn">';
	}
	
	static public function footerVisitsFrequency($notification)
	{
		$out =& $notification->getNotificationObject();
		$out = '</div>
			<div id="rightcolumn">
			';
		$out .= Piwik_FrontController::getInstance()->fetchDispatch('VisitorInterest','index');
		$out .= '</div>';
	}
}

