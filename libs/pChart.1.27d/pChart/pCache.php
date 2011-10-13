<?php
 /*
     pCache - Faster renderding using data cache
     Copyright (C) 2008 Jean-Damien POGOLOTTI
     Version  1.1.2 last updated on 06/17/08

     http://pchart.sourceforge.net

     This program is free software: you can redistribute it and/or modify
     it under the terms of the GNU General Public License as published by
     the Free Software Foundation, either version 1,2,3 of the License, or
     (at your option) any later version.

     This program is distributed in the hope that it will be useful,
     but WITHOUT ANY WARRANTY; without even the implied warranty of
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     GNU General Public License for more details.

     You should have received a copy of the GNU General Public License
     along with this program.  If not, see <http://www.gnu.org/licenses/>.

     Class initialisation :
      pCache($CacheFolder="Cache/")
     Cache management :
      IsInCache($Data)
      GetFromCache($ID,$Data)
      WriteToCache($ID,$Data,$Picture)
      DeleteFromCache($ID,$Data)
      ClearCache()
     Inner functions :
      GetHash($ID,$Data)
 */

 /* pCache class definition */
 class pCache
  {
   var $HashKey     = "";
   var $CacheFolder = "Cache/";

   /* Create the pCache object */
   function pCache($CacheFolder="Cache/")
    {
     $this->CacheFolder = $CacheFolder;
    }

   /* This function is clearing the cache folder */
   function ClearCache()
    {
     if ($handle = opendir($this->CacheFolder))
      {
       while (false !== ($file = readdir($handle)))
        {
         if ( $file != "." && $file != ".." )
          unlink($this->CacheFolder.$file);
        }
       closedir($handle);
      }
    }

   /* This function is checking if we have an offline version of this chart */
   function IsInCache($ID,$Data,$Hash="")
    {
     if ( $Hash == "" )
      $Hash = $this->GetHash($ID,$Data);

     if ( file_exists($this->CacheFolder.$Hash) )
      return(TRUE);
     else
      return(FALSE);
    }

   /* This function is making a copy of drawn chart in the cache folder */
   function WriteToCache($ID,$Data,$Picture)
    {
     $Hash     = $this->GetHash($ID,$Data);
     $FileName = $this->CacheFolder.$Hash;

     imagepng($Picture->Picture,$FileName);
    }

   /* This function is removing any cached copy of this chart */
   function DeleteFromCache($ID,$Data)
    {
     $Hash     = $this->GetHash($ID,$Data);
     $FileName = $this->CacheFolder.$Hash;

     if ( file_exists($FileName ) )
      unlink($FileName);
    }

   /* This function is retrieving the cached picture if applicable */
   function GetFromCache($ID,$Data)
    {
     $Hash     = $this->GetHash($ID,$Data);
     if ( $this->IsInCache("","",$Hash ) )
      {
       $FileName = $this->CacheFolder.$Hash;

       header('Content-type: image/png');
       @readfile($FileName);
       exit();
      }
    }

   /* This function is building the graph unique hash key */
   function GetHash($ID,$Data)
    {
     $mKey = "$ID";
     foreach($Data as $key => $Values)
      {
       $tKey = "";
       foreach($Values as $Serie => $Value)
        $tKey = $tKey.$Serie.$Value;
       $mKey = $mKey.md5($tKey);
      }
     return(md5($mKey));
    }
  }
?>