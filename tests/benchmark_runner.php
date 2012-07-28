<?php
require_once 'config_test.php';
require_once 'simpletest/unit_tester.php';
require_once 'simpletest/mock_objects.php';
require_once 'simpletest/collector.php';
require_once 'simpletest/default_reporter.php';

// if SCRIPT_NAME points to benchmark_runner.php, load every benchmark group in benchmarks folder
if (preg_match("/benchmark_runner.php$/", $_SERVER['SCRIPT_NAME']))
{
	foreach(Piwik::globr(PIWIK_INCLUDE_PATH.'/tests/benchmarks', '*.benchmark.php') as $file)
	{
		require_once $file;
	}
}

function display_runner( $benchmarkGroups )
{
?>
<html>
<head>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
	<link rel="stylesheet" href="http://twitter.github.com/bootstrap/assets/css/bootstrap.css" type="text/css" media="screen"></link>
	<title>Benchmarks | Piwik</title>
</head>
<body>
	<style>
		td.elapsed {
			font-weight: bold;
		}
		td.status {
			font-style: italic;
		}
		
		a.run-link {
			font-style: italic;
			font-size: .8em;
		}
		
		.benchmark-name {
			font-weight: bold;
			font-style: italic;
		}
		
		.elapsed,.status,.memory {
			width: 128px;
		}
		
		.run-link-cell {
			width: 64px;
		}
		
		.error {
			font-weight: bold;
			color: red;
		}
		
		section {
			margin-bottom: 2em;
		}
		
		section>div>h3 {
			margin-bottom: 1em;
		}
		
		.page-header {
			margin-bottom: 2em;
		}
		
		#message {
			font-size: 1.2em;
			font-style: italic;
			margin-bottom: 2em;
		}
	</style>
	
	<script type="text/javascript">
		function onBenchmarkFinished(error, row, result) {
			var cells = $(row).children();
			if (error)
			{
				$(cells[2]).html('<span class="error">ERROR</span>');
				$('#message>p').text("ERROR: " + (error.stack || error.message)).addClass('error');
			}
			else
			{
				$(cells[2]).html('Done');
				$(cells[3]).html(result.memory);
				$(cells[4]).html(result.elapsed + 's');
				if (result.result)
				{
					$('#message>p').text('Got result: ' + JSON.stringify(result.result)).removeClass('error');
				}
				else
				{
					$('#message>p').text('Got no result.').removeClass('error');
				}
			}
		}
		
		$(document).ready(function () {
			var baseUrl = window.location.search || '?';
			if (baseUrl !== '?') {
				baseUrl += '&';
			}
			$('.run-link').click(function (e) {
				var row = $(this).parent().parent(),
					cells = $(row).children(),
					table = $(row).parent().parent(),
					klass = $(table).attr('_groupName'),
					method = $('.benchmark-name>span', row).html();
				
				$(cells[2]).html('Running');
				$(cells[3]).html('-');
				$(cells[4]).html('-');
				
				$.ajax({
					type: 'POST',
					url: baseUrl + 'class=' + klass + '&method=' + method + '&action=run',
					async: true,
					error: function (xhr, status, thrown) {
						if (thrown)
						{
							onBenchmarkFinished(thrown, row);
						}
						else
						{
							onBenchmarkFinished(new Error(status || 'Unknown error.'), row);
						}
					},
					success: function (data) {
						try
						{
							objData = JSON.parse(data);
						}
						catch (e)
						{
							onBenchmarkFinished({message: "Invalid data returned from server: " + data}, row);
							return;
						}
						
						if (objData.error)
						{
							onBenchmarkFinished({message: objData.error}, row);
						}
						else
						{
							onBenchmarkFinished(null, row, objData);
						}
					}
				});
				
				e.preventDefault();
				return false;
			});
		});
	</script>
	
	<div class="container">
		<div class="row page-header">
			<h1 class="span12">Benchmarks</h1>
		</div>
		<div class="row" id="message">
			<p class="span11 offset1">
				Click 'run' to run a benchmark.<br/><br/>
				Note: For non-tracker tests, please supply the 'useLocalTracking=true' query parameter.
				Otherwise, the setup for most tests can take up to 40min.
			</p>
		</div>
		<?php foreach ($benchmarkGroups as $groupName => $benchmarks): ?>
		<section class="row">
			<div class="span12">
				<h3><?php echo $groupName; ?></h3>
				<table _groupName="<?php echo $groupName; ?>" class="table table-striped span12 table-bordered">
					<tr>
						<th>&nbsp;</th>
						<th>Name</th>
						<th>Status</th>
						<th>Memory Usage</th>
						<th>Elapsed</th>
					</tr>
					<?php foreach ($benchmarks as $benchmark): ?>
					<tr>
						<td class="run-link-cell"><a href="#" class="run-link">Run</a></td>
						<td class="benchmark-name"><span><?php echo $benchmark->name; ?></span></td>
						<td class="status">Not Running</td>
						<td class="memory">-</td>
						<td class="elapsed">-</td>
					</tr>
					<?php endforeach; ?>
				</table>
			</div>
		</section>
		<?php endforeach; ?>
	</div>
</body>
</html>
<?php
}

function run_test( $klass, $method )
{
	try
	{
		// create instance
		$reflector = new ReflectionClass($klass);
		$instance = $reflector->newInstanceArgs();
		
		// run setUp
		$instance->setUp();
		
		// start timing
		$start_time = microtime(true);
		$start_memory = memory_get_usage();
		
		// run test
		$result = $reflector->getMethod($method)->invoke($instance);
		
		// end timing
		$end_time = microtime(true);
		$end_memory = memory_get_usage();
		
		// run tearDown
		$instance->tearDown();
		
		// return results
		echo json_encode(array(
			'start_time' => round($start_time, 4),
			'end_time' => round($end_time, 4),
			'elapsed' => round($end_time - $start_time, 4),
			'memory' => Piwik::getPrettySizeFromBytes($end_memory - $start_memory, $unit = null, $precision = 2),
			'result' => $result
		));
	}
	catch (Exception $ex)
	{
		echo json_encode(array(
			'error' => $ex->getMessage(),
			'trace' => $ex->getTraceAsString()
		));
	}
}

register_shutdown_function('benchmark_autorun');
function benchmark_autorun()
{
	$benchmarkGroups = array();
	
	// add all benchmarks
	foreach (get_declared_classes() as $klass)
	{
		$reflector = new ReflectionClass($klass);
		if (preg_match("/.benchmark.php$/", $reflector->getFileName()))
		{
			foreach ($reflector->getMethods() as $method)
			{
				if (preg_match("/^test_/", $method->name) === 0)
				{
					continue;
				}
			
				$benchmarkGroups[$klass][] = $method;
			}
		}
	}
	
	if (!isset($_GET['action']))
	{
		$_GET['action'] = 'display_runner';
	}
	
	switch ($_GET['action'])
	{
		case "run":
			run_test($_GET['class'], $_GET['method']);
			break;
		default:
			display_runner($benchmarkGroups);
			break;
	}
}

