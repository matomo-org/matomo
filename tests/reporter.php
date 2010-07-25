<?php
class HtmlTimerReporter extends HtmlReporter
{
    function HtmlTimerReporter($intro = '') {
        $this->HtmlReporter();
        $this->timer = new Piwik_Timer;
        $this->intro = $intro;
    }

    function paintHeader($test_name) {
        ob_start();

        $this->sendNoCacheHeaders();
        print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
        print "<html>\n<head>\n<title>$test_name</title>\n";
        print "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" .
                $this->_character_set . "\" />\n";
        print "<style type=\"text/css\">\n";
        print $this->_getCss() . "\n";
        print "</style>\n";
        print "</head>\n<body>\n";
        print "<h1>$test_name</h1>\n";

	print $this->intro;

        ob_flush();
        flush();
    }

    function paintFooter($test_name) {
        $colour = ($this->getFailCount() + $this->getExceptionCount() > 0 ? "red" : "green");
        print "<div style=\"";
        print "padding: 8px; margin-top: 1em; background-color: $colour; color: white;";
        print "\">";
        print $this->getTestCaseProgress() . "/" . $this->getTestCaseCount();
        print " test cases complete:\n";
        print "<strong>" . $this->getPassCount() . "</strong> passes, ";
        print "<strong>" . $this->getFailCount() . "</strong> fails and ";
        print "<strong>" . $this->getExceptionCount() . "</strong> exceptions.";
        print "<br/> ";
        print $this->timer;
        print " - 	";
        print $this->timer->getMemoryLeak();
        print "</div>\n";
        print "</body>\n</html>\n";

	@header('Content-type: text/html; charset=' . $this->_character_set, true);
	@header('Content-disposition: ', true);
        ob_end_flush();
    }
}
