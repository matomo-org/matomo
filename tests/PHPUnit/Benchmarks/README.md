# Benchmark tests

Piwik comes with a system that can be used to benchmark certain Piwik processes. The benchmarking system relies both on PHPUnit.

##  Benchmarks & Fixtures

Piwik's benchmarks are written as unit tests. Except, they don't setup the database by themselves.
Instead, there are several 'fixture' classes that do the setup. You can mix and match different
benchmarks with different fixtures to time piwik processes under differing circumstances.

For example, you can test how long it takes to generate reports for one site with ~230,000 visits
in one day, or you can test how long it takes to generate reports for 1,000 sites w/ 12 visits
each on one day, simply by changing the fixture.

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

Run a benchmark using the branch without changes ('master').

Then compare both benchmarks.

NOTE:
  - You don't need git to do this, but it's much easier w/ git.
  - It's a good idea to make sure the tests pass before benchmarking.
