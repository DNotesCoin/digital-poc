<?php 
      $call_url = "https://explorer.dnotescoin.com/chain/DNotes/q/invoice/".$_GET["pay"]."+".$_GET["invoice"];
      $dh = curl_init();
      curl_setopt($dh, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($dh, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($dh, CURLOPT_URL, $call_url);
      $result = curl_exec($dh);
      curl_close($dh);
      echo $result;		  
?>