<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\ScheduledReports\tests\Fixtures;

use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Db;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugin\Report;
use Piwik\Plugins\ScheduledReports\API as APIScheduledReports;
use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\ReportRenderer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\XssTesting;

class ReportSubscription extends Fixture
{
    public $dateTime = '2013-01-23 01:23:45';
    public $idSite = 1;

    private $xssTesting;

    public function __construct()
    {
        $this->xssTesting = new XssTesting();
    }

    public function setUp()
    {
        if (!Fixture::siteCreated($this->idSite)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }

        Fixture::createSuperUser(false);

        self::setUpScheduledReports($this->idSite);

        APIScheduledReports::getInstance()->addReport(
            $this->idSite,
            $this->xssTesting->forTwig('scheduledreport'),
            'month',
            0,
            ScheduledReports::EMAIL_TYPE,
            ReportRenderer::HTML_FORMAT,
            ['ExampleAPI_xssReportforTwig', 'ExampleAPI_xssReportforAngular'],
            array(
                ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_ONLY,
                ScheduledReports::EMAIL_ME_PARAMETER => true,
                ScheduledReports::ADDITIONAL_EMAILS_PARAMETER => ['any@matomo.org']
            )
        );
        APIScheduledReports::getInstance()->addReport(
            $this->idSite,
            $this->xssTesting->forAngular('scheduledreport'),
            'month',
            0,
            ScheduledReports::EMAIL_TYPE,
            ReportRenderer::HTML_FORMAT,
            ['ExampleAPI_xssReportforTwig', 'ExampleAPI_xssReportforAngular'],
            array(ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_ONLY)
        );

        Db::query("DELETE FROM " . Common::prefixTable('report_subscriptions'));
        for ($idReport = 1; $idReport <= 5; $idReport++) {
            Db::query("INSERT INTO " . Common::prefixTable('report_subscriptions') . "(idreport, token, email) VALUES ($idReport, 'mycustomtoken$idReport', 'any@matomo.org')");
        }
    }

    public function provideContainerConfig()
    {
        return [
            'observers.global' => \DI\add([
                ['Report.addReports', function (&$reports) {
                    $report = new XssReport();
                    $report->initForXss('forTwig');
                    $reports[] = $report;

                    $report = new XssReport();
                    $report->initForXss('forAngular');
                    $reports[] = $report;
                }],
                ['Dimension.addDimensions', function (&$instances) {
                    $instances[] = new XssDimension();
                }],
                ['API.Request.intercept', function (&$result, $finalParameters, $pluginName, $methodName) {
                    if ($pluginName != 'ExampleAPI' && $methodName != 'xssReportforTwig' && $methodName != 'xssReportforAngular') {
                        return;
                    }

                    if (!empty($_GET['forceError']) || !empty($_POST['forceError'])) {
                        throw new \Exception("forced exception");
                    }

                    $dataTable = new DataTable();
                    $dataTable->addRowFromSimpleArray([
                        'label' => $this->angularXssLabel,
                        'nb_visits' => 10,
                    ]);
                    $dataTable->addRowFromSimpleArray([
                        'label' => $this->twigXssLabel,
                        'nb_visits' => 15,
                    ]);
                    $result = $dataTable;
                }],
            ]),
        ];
    }
}

class XssReport extends Report
{
    private $xssType;

    protected function init()
    {
        parent::init();

        $this->metrics        = array('nb_visits');
        $this->order = 10;

        $action = Common::getRequestVar('actionToWidgetize', false) ?: Common::getRequestVar('action', false);
        if ($action == 'xssReportforTwig') {
            $this->initForXss('forTwig');
        } else if ($action == 'xssReportforAngular') {
            $this->initForXss('forAngular');
        }
    }

    public function initForXss($type)
    {
        $this->xssType = $type;

        $xssTesting = new XssTesting();
        $this->dimension      = new XssDimension();
        $this->dimension->initForXss($type);
        $this->name           = $xssTesting->$type('reportname');
        $this->documentation  = $xssTesting->$type('reportdoc');
        $this->categoryId = $xssTesting->$type('category');
        $this->subcategoryId = $xssTesting->$type('subcategory');
        $this->processedMetrics = [new XssProcessedMetric($type)];
        $this->module = 'ExampleAPI';
        $this->action = 'xssReport' . $type;
        $this->id = 'ExampleAPI.xssReport' . $type;
    }
}

class XssDimension extends VisitDimension
{
    public $type = Dimension::TYPE_NUMBER;

    private $xssType;

    public function initForXss($type)
    {
        $xssTesting = new XssTesting();

        $this->xssType = $type;
        $this->nameSingular = $xssTesting->$type('dimensionname');
        $this->columnName = 'xsstestdim';
        $this->category = $xssTesting->$type('category');
    }

    public function getId()
    {
        return 'XssTest.XssDimension.' . $this->xssType;
    }
}

class XssProcessedMetric extends ProcessedMetric
{
    /**
     * @var string
     */
    private $xssType;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $docs;

    public function __construct($type)
    {
        $xssTesting = new XssTesting();

        $this->xssType = $type;
        $this->name = $xssTesting->$type('processedmetricname');
        $this->docs = $xssTesting->$type('processedmetricdocs');
    }

    public function getName()
    {
        return 'xssmetric';
    }

    public function getTranslatedName()
    {
        return $this->name;
    }

    public function getDocumentation()
    {
        return $this->docs;
    }

    public function compute(Row $row)
    {
        return 5;
    }

    public function getDependentMetrics()
    {
        return [];
    }
}

