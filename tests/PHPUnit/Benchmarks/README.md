# Benchmark tests

Piwik comes with a system that can be used to benchmark certain Piwik processes. The benchmarking system relies both on PHPUnit and VisualPHPUnit.

##  Benchmarks & Fixtures 

Piwik's benchmarks are written as unit tests. Except, they don't setup the database by themselves.
Instead, there are several 'fixture' classes that do the setup. You can mix and match different
benchmarks with different fixtures to time piwik processes under differing circumstances.

For example, you can test how long it takes to generate reports for one site with ~230,000 visits
in one day, or you can test how long it takes to generate reports for 1,000 sites w/ 12 visits
each on one day, simply by changing the fixture.

##  Running Benchmarks 

To run a benchmark, first load VisualPHPUnit by pointing your browser to:

http://path/to/piwik/trunk/tests/lib/visualphpunit/

  * On the left you will see a list of files and directories. Click on the 'Benchmarks' directory
    to expand it. Then click on the 'Fixtures' directory to expand it.

  * Click one of the benchmarks to run (see the next section for a list of benchmarks).

  * Below the file listing is a section with the title 'GLOBALS'. In order to run a benchmark,
    you'll have to enter some information here.

  * Enter 'PIWIK_BENCHMARK_FIXTURE' in the left input. In the right input, pick one of the fixtures
    in the 'Fixtures' folder and enter it (w/o the .php extension). For example, you can enter
    'SqlDump' or 'ThousandSitesTwelveVisitsEachOneDay' (see the next section for a list of fixtures).

  * Click the 'Add' link in the 'GLOBALS' section. In the new row enter 'PIWIK_BENCHMARK_DATABASE'
    in the left input. On the right enter the name of a new database. This database will be created
    and saved so you don't have to re-setup the database next time you run a benchmark. If you
    plan on running the benchmark more than once, this can save a lot of time.

    NOTE: This option isn't required.

  * Now, click the 'Run Tests' link at the top of the page. This will run the test, which can take
    a long time based on how fast your machine is. When the test finishes, you'll see the following
    statistics:

    * Total Elapsed Time - the amount of time it took to run the test + setup the fixture + process
                           PHPUnit's result + etc.
    * Total Execution Time - the amount of time it took to run the test (this is an important
                             metric).
    * Peak Memory Use - The peak memory use for the test (this is an important metric).
    * Total Memory Delta - The memory delta of every test run, summed up.

NOTE: You cannot at present run more than one benchmark, so make sure you only select one.

##  Included Benchmarks and Fixtures 

These are the benchmarks currently written for Piwik:

  * Benchmarks/ArchivingProcessBenchmark.php

    This benchmark times the process Piwik uses to generate reports and calculate metrics.

  * Benchmarks/TrackerBenchmark.php

    This benchmark times how long it takes to track 12,500 pageviews in one bulk request.

These are the fixtures currently included with Piwik:

  * Benchmarks/Fixtures/OneSiteTwelveThousandVisitsOneYear.php

    This fixture adds one website and tracks twelve thousand visits over the course of
    a year (1,000 visits per month).

  * Benchmarks/Fixtures/ThousandSitesTwelveVisitsEachOneDay.php

    This fixture adds one thousand websites and tracks 12 visits each on one day.

  * Benchmarks/Fixtures/SqlDump.php

    This fixture downloads and loads an SQL dump. The SQL dump is for a database with one
    website with ~230,000 visits on one day. There are around ~2.3 pageviews per visit and
    each visit resulted in at least one conversion.

##  Benchmarking with git 

If you use git, you can use the benchmarking system to easily see if there are performance
regressions caused by your changes.

To do this, make sure you put your changes into a new git branch. You can create a new
branch by running:
    $ git checkout -b branch_name

Run a benchmark using the branch without changes ('master'). Load VisualPHPUnit in a new
tab and switch branches to the new branch. You can switch branches by running:
    $ git checkout branch_name

In the new tab run the benchmark again. You can now compare how long it took to run the
test w/o your changes and with your changes.

NOTE:
  - You don't need git to do this, but it's much easier w/ git.
  - It's a good idea to make sure the tests pass before benchmarking.
