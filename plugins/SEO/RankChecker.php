<?php
/**
 * Functions below are adapted from GetRank - PageRank Script under GPL
 * http://www.getrank.org/free-pagerank-script/
 * 
* @copyright	Copyright (C) 2007 - 2010 GetRank.Org  All rights reserved.
* @license		GNU/GPL
* @package		Piwik_SEO_RankChecker
* @version :    2.0
*/
class Piwik_SEO_RankChecker 
{
    var $url;
	var $results = array();

    function __construct($url) 
	{
		$this->url = preg_replace('/http\:\/\//si', '', $url);
    }

    function getPage ($url) 
	{
		return Piwik_Http::sendHttpRequest($url, $timeout = 5, @$_SERVER['HTTP_USER_AGENT']);
    }

    function getPagerank () 
	{
    	$chwrite = $this->CheckHash($this->HashURL($this->url));
    	
		$url="http://toolbarqueries.google.com/search?client=navclient-auto&ch=".$chwrite."&features=Rank&q=info:".$this->url."&num=100&filter=0";			
        $data = $this->getPage($url);
        preg_match('#Rank_[0-9]:[0-9]:([0-9]+){1,}#si', $data, $p);
        $value = isset($p[1]) ? $p[1] : 0;
			
        return $value;
    }

    function getAlexaRank () 
	{
    	$url = $this->url;
        $xml = simplexml_load_file('http://data.alexa.com/data?cli=10&url=' . $url);
        return $xml->SD->POPULARITY['TEXT'];
    }
    
    function getDmoz () 
	{
        $url = ereg_replace('^www\.', '', $this->url);
        $url = "http://search.dmoz.org/cgi-bin/search?search=$url";
        $data = $this->getPage($url);
        if (ereg('<center>No <b><a href="http://dmoz\.org/">Open Directory Project</a></b> results found</center>', $data)) 
        {
            $value = false;
        } 
        else 
		{
            $value = true;
        }
        return $value;
    }
    
    function getYahooDirectory () 
	{
        $url = ereg_replace('^www\.', '', $this->url);
        $url = "http://search.yahoo.com/search/dir?p=$url";
        $data = $this->getPage($url);
        if (ereg('No Directory Search results were found\.', $data)) {
            $value = false;
        } else {
            $value = true;
        }
        return $value;
    }
    
    function getBacklinksGoogle () 
	{
        $url = $this->url;
        $url = 'http://www.google.com/search?q=link%3A'.urlencode($url);
        $data = $this->getPage($url);
        preg_match('/of about \<b\>([0-9\,]+)\<\/b\>/si', $data, $p);
        $value = isset($p[1]) ? $this->toInt($p[1]) : 0;
        return $value;

    }

    function getBacklinksYahoo () 
	{
        $url = $this->url;
        $url = 'http://siteexplorer.search.yahoo.com/search?p='.urlencode("http://$url");
        $data = $this->getPage($url);
        preg_match('/Inlinks \(([0-9\,]+)\)/si', $data, $p);
        $value = isset($p[1]) ? $this->toInt($p[1]) : 0;
        return $value;
    }
    
    function getAge () 
	{
        $url = ereg_replace('^www\.', '', $this->url);
        $url = "http://www.who.is/whois-com/ip-address/$url";
        $data = $this->getPage($url);
        preg_match('#Creation Date: ([a-z0-9-]+)#si', $data, $p);
        if(isset($p[1])) 
		{
            $value = time() - strtotime($p[1]);
            $value = Piwik::getPrettyTimeFromSeconds($value);
        } 
        else 
		{
            $value = false;
        }
        return $value;
    }

    function getIndexedYahoo () 
	{
        $url = $this->url;
        $url = 'http://siteexplorer.search.yahoo.com/search?p='.urlencode("http://$url");
		$data = $this->getPage($url);
		preg_match('/Pages \(([0-9,]{1,})\)/im', $data, $p);
        $value = isset($p[1]) ? $this->toInt($p[1]) : 0;
        return $value;
    }

    function toInt($string) 
	{
        return preg_replace('#[^0-9]#si', '', $string);
    }
    
    /**
     * Functions below are adapted from GetRank - PageRank Script under GPL
     * http://www.getrank.org/free-pagerank-script/
     */
    
    //--> for google Piwik_SEO_Ranks 
    function StrToNum($Str, $Check, $Magic) 
    { 
        $Int32Unit = 4294967296;  // 2^32 
    
        $length = strlen($Str); 
        for ($i = 0; $i < $length; $i++) 
    	{ 
            $Check *= $Magic;      
            //If the float is beyond the boundaries of integer (usually +/- 2.15e+9 = 2^31), 
            //  the result of converting to integer is undefined 
            //  refer to http://www.php.net/manual/en/language.types.integer.php 
            if ($Check >= $Int32Unit) 
    		{ 
                $Check = ($Check - $Int32Unit * (int) ($Check / $Int32Unit)); 
                //if the check less than -2^31 
                $Check = ($Check < -2147483648) ? ($Check + $Int32Unit) : $Check; 
            } 
            $Check += ord($Str{$i}); 
        } 
        return $Check; 
    } 
    
    /* 
    * Genearate a hash for a url 
    */ 
    function HashURL($String) 
    { 
        $Check1 = $this->StrToNum($String, 0x1505, 0x21); 
        $Check2 = $this->StrToNum($String, 0, 0x1003F); 
    
        $Check1 >>= 2;      
        $Check1 = (($Check1 >> 4) & 0x3FFFFC0 ) | ($Check1 & 0x3F); 
        $Check1 = (($Check1 >> 4) & 0x3FFC00 ) | ($Check1 & 0x3FF); 
        $Check1 = (($Check1 >> 4) & 0x3C000 ) | ($Check1 & 0x3FFF);    
        
        $T1 = (((($Check1 & 0x3C0) << 4) | ($Check1 & 0x3C)) <<2 ) | ($Check2 & 0xF0F ); 
        $T2 = (((($Check1 & 0xFFFFC000) << 4) | ($Check1 & 0x3C00)) << 0xA) | ($Check2 & 0xF0F0000 ); 
        
        return ($T1 | $T2); 
    } 
    
    //--> for google Piwik_SEO_Ranks 
    /* 
    * genearate a checksum for the hash string 
    */ 
    function CheckHash($Hashnum) 
    { 
        $CheckByte = 0; 
        $Flag = 0; 
    
        $HashStr = sprintf('%u', $Hashnum) ; 
        $length = strlen($HashStr); 
        
        for ($i = $length - 1;  $i >= 0;  $i --) 
    	{ 
            $Re = $HashStr{$i}; 
            if (1 === ($Flag % 2)) {              
                $Re += $Re;      
                $Re = (int)($Re / 10) + ($Re % 10); 
            } 
            $CheckByte += $Re; 
            $Flag ++;    
        } 
    
        $CheckByte %= 10; 
        if (0 !== $CheckByte) 
    	{ 
            $CheckByte = 10 - $CheckByte; 
            if (1 === ($Flag % 2) ) 
    		{ 
                if (1 === ($CheckByte % 2)) 
    			{ 
                    $CheckByte += 9; 
                } 
                $CheckByte >>= 1; 
            } 
        } 
    
        return '7'.$CheckByte.$HashStr; 
    } 
}
