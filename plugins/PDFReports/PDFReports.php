<?php
class Piwik_PDFReports extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'Pdf Export Plugin',
			'description' => Piwik_Translate('PDFReports_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
	}
	public function getListHooksRegistered()
	{
		return array( 
				'TopMenu.add' => 'addTopMenu',
				'TaskScheduler.getScheduledTasks' => 'getScheduledTasks',
				'AssetManager.getJsFiles' => 'getJsFiles',
		);
	}

	function getJsFiles( $notification )
	{
		$jsFiles = &$notification->getNotificationObject();
		$jsFiles[] = "plugins/PDFReports/templates/pdf.js";
	}
	
	function getScheduledTasks ( $notification )
	{
		$tasks = &$notification->getNotificationObject();
		$tasks[] = new Piwik_ScheduledTask ( $this, 'dailySchedule', new Piwik_ScheduledTime_Daily() );
		$tasks[] = new Piwik_ScheduledTask ( $this, 'weeklySchedule', new Piwik_ScheduledTime_Weekly() );
		$tasks[] = new Piwik_ScheduledTask ( $this, 'monthlySchedule', new Piwik_ScheduledTime_Monthly() );
	}
	
	function dailySchedule()
	{
		$this->generateAndSendScheduledReports('day');
	}
	
	function weeklySchedule()
	{
		$this->generateAndSendScheduledReports('week');
	}
	
	function monthlySchedule()
	{
		$this->generateAndSendScheduledReports('month');
	}
	
	function generateAndSendScheduledReports($period)
	{
		// Select all reports to generate
		$reportsToGenerate = Piwik_PDFReports_API::getInstance()->getReports($idSite = false, $period);
		
		// For each, generate the file and send the message with the attached report
		foreach($reportsToGenerate as $report)
		{
			list($outputFilename, $prettyDate, $websiteName) = 
											Piwik_PDFReports_API::getInstance()->generateReport(
													$report['idreport'], 
													Piwik_Date::now()->toString('Y-m-d'),
													$report['idsite'],
													$outputType = Piwik_PDFReports_API::OUTPUT_PDF_SAVE_ON_DISK
													);

			$emails = self::getEmailsFromString($report['additional_emails']);
			if($report['email_me'] == 1)
			{		
				$emails[] = Piwik::getCurrentUserEmail();
			}
			$this->sendReportEmail($emails, $outputFilename, $prettyDate, $websiteName, $report);
		}
	}
	
	function sendReportEmail($emails, $outputFilename, $prettyDate, $websiteName, $report)
	{
		$periods = self::getPeriodToFrequency();
		$message  = Piwik_Translate('PDFReports_EmailHello');
		$message .= "\n" . Piwik_Translate('PDFReports_PleaseFindAttachedFile', array($periods[$report['period']], $websiteName));
		$subject = "Reports " . $websiteName . " - ".$prettyDate;

		$mail = new Piwik_Mail();
		$mail->setSubject($subject);
		$mail->setBodyText($message);
		foreach ($emails as $email)
		{
			$mail->addTo($email);
		}
		
		$fromEmailName = Piwik_Translate('PDFReports_PiwikReports');
		$fromEmailAddress = Zend_Registry::get('config')->General->noreply_email_address;
		$mail->setFrom($fromEmailAddress, $fromEmailName);
		
		if(!file_exists($outputFilename))
		{
			throw new Exception("The PDF file wasn't found in $outputFilename");
		}
		$filename = basename($outputFilename);
		$handle = fopen($outputFilename, "r");
		$contents = fread($handle, filesize($outputFilename));
		fclose($handle);
		$mail->createAttachment(	$contents, 
									'application/pdf', 
									Zend_Mime::DISPOSITION_INLINE, 
									Zend_Mime::ENCODING_BASE64, 
									$filename
		);
		
		// Update flag in DB
		Zend_Registry::get('db')->update( Piwik_Common::prefixTable('pdf'), 
					array( 'ts_last_sent' => Piwik_Date::now()->getDatetime() ),
					"idreport = " . $report['idreport']
		);	

		$mail->send();
		
		// Remove PDF file
		unlink($outputFilename);
	}
		
    function addTopMenu()
    {
    	Piwik_AddTopMenu( 'PDFReports_PDF', array('module' => 'PDFReports', 'action' => 'index'), true, 13);
    }
	
    function install()
	{
		$queries[] = "
                CREATE TABLE ".Piwik_Common::prefixTable('pdf')." (
					idreport INT(11) NOT NULL AUTO_INCREMENT,
					idsite INTEGER(11) NOT NULL,
					login VARCHAR(100) NOT NULL,
					description VARCHAR(255) NOT NULL,
					period VARCHAR(10) NULL,
					email_me TINYINT NULL,
					additional_emails VARCHAR(255) NULL,
					reports TEXT NOT NULL,
					ts_created TIMESTAMP NULL,
					ts_last_sent TIMESTAMP NULL,
					deleted tinyint(4) NOT NULL default '0',
					PRIMARY KEY (idreport)
				) DEFAULT CHARSET=utf8";
        try {
        	foreach($queries as $query)
        	{
        		Piwik_Exec($query);
        	}
		}
		catch(Exception $e) {
    		if(!Zend_Registry::get('db')->isErrNo($e, '1050'))
			{
				throw $e;
			}
		}
	}

	static public function getPeriodToFrequency()
	{
		$periods = array(
			'day' => Piwik_Translate('General_Daily'),
			'week' => Piwik_Translate('General_Weekly'),
			'month' => Piwik_Translate('General_Monthly'),
		);
		return $periods;
	}

	static public function getEmailsFromString($additionalEmails)
	{
		if(empty($additionalEmails))
		{
			return array();
		}
		$additionalEmails = explode(',', trim($additionalEmails));
		$additionalEmails = array_filter($additionalEmails, 'strlen');
		return $additionalEmails;
	}
}