<?php 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

/* Config values  */
$usd_notes = 0; //USD or Notes Payment config variable(0:usd , 1:notes)
$amount_price = 9.95; // Amount to pay 
$tolerance = 0.1; // Payment tolerance 
$download_link = "https://website.com/productdownload.pdf"; // Product download url 
$confirmations_num = 0; //Number of confirmations required( 0=Fastest, payment has been sent but not confirmed to be valid, up to 1 minute 6=Slow, up to 6 minutes, transaction fully validated on the network )
$short_description = "Change this text to your own short description of the product"; //Enter a short description of the product you are selling 

/*    */
?>

<!DOCTYPE html>
<html>
<head>

    <title>DNotes Payment</title>
    
    <meta name="author" content="your name" />
    <meta name="description" content="" />
    <link rel="stylesheet" href="style.css" type="text/css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    
</head>
<body>
    <div id="page">
        <h1 class="header-text"><img src="header.png" alt="header"></h1>
        <div style="margin-left: calc((100% - 600px)/2);margin-top:4%;">
          <div id="loading"></div>
          <div style="display: inline-block;vertical-align: top;margin-top: 20px;">
            <p style="margin: 5px;font-size: 20px;font-weight: bold;">Pay with DNotes</p>
            <p style="margin: 5px;font-style: italic;font-size: 15px;"><?php echo $short_description;  ?></p>
          </div>
        </div>
        <div id="content">
            <div id="content-blogval">
                <?php
                  $address_array = array();
                  error_reporting(E_ALL);
                  ini_set('display_errors',1);

                  $filename_address = 'address.txt';
                  $eachlines_address = file($filename_address, FILE_IGNORE_NEW_LINES);
                  foreach($eachlines_address as $lines){
                      array_push($address_array , $lines);
                  }
                  $array_index = array_rand($address_array);
                  $dnotes_address = $address_array[$array_index];

                  $ch = curl_init();
                  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                  curl_setopt($ch, CURLOPT_URL, 'https://api.coinmarketcap.com/v2/ticker/184/');
                  $result = curl_exec($ch);
                  curl_close($ch);
                  
                  $result_json = json_decode($result);
                  $result_data = $result_json->data;
                  $result_quotes = $result_data->quotes;
                  $result_usd = $result_quotes->USD;
                  $usd_price = $result_usd->price;
                  $show_usdprice = round($usd_price , 3);

                  if($usd_notes =="0")
                  {
                    $send_mount = round(( $amount_price / $usd_price ) , 5);
                  }else{
                    $send_mount = $amount_price;
                  }

                  $unix_time = time();
                  $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                  $charactersLength = strlen($characters);
                  $randomString = '';
                  for ($i = 0; $i < 10; $i++) {
                      $randomString .= $characters[rand(0, $charactersLength - 1)];
                  }
                  $invoice_number = $unix_time.$randomString;

                  $send_address = $dnotes_address."+".$invoice_number;

              ?>
              <input type="hidden" id="send_value_address" value="<?php echo $send_address;  ?>" />
              <input type="hidden" id="send_mount" name="send_mount" value="<?php echo $send_mount;  ?>" />
              <input type="hidden" id="product_download_url" value="<?php echo $download_link;  ?>" />
              <input type="hidden" id="confirmations_num" value="<?php echo $confirmations_num;  ?>" />
              <input type="hidden" id="tolerance" value="<?php echo $tolerance;  ?>" />
			  <div style="font-size: 16px;margin-bottom: 15px;">
			  <?php if ($usd_notes==0) { ?><font style="font-weight: bold;">Amount:</font> $<?php echo $amount_price;  ?>
			  <?php } else { ?><font style="font-weight: bold;">Amount:</font> <?php echo $amount_price;  ?> DNotes
			  <?php } ?></div>
              <div style="font-size: 16px;margin-bottom: 15px;">
                  <font style="font-weight: bold;">Please send exactly: </font> 
                  <input style="width: 80px;font-size: 15px;border: 1px solid #fff;" type="text" id="copyAmount" value="<?php echo $send_mount; ?>" readonly>
                  <input type="button" value="Click to Copy" id="copy_amounty" class="btnsubmit-property">
              </div>
              <div style="font-size: 16px;margin-bottom: 15px;">
                  <font style="font-weight: bold;">To : </font>
                  <input style="width: 80%;font-size: 15px;border: 1px solid #fff;" type="text" id="copyTarget" value="<?php echo $send_address; ?>" readonly>
                  <input type="button" value="Click to Copy" id="copy_address" class="btnsubmit-property">
              </div>
              <div id="payment_state_btn"></div>
              <div style="margin-top:100px;">
                  <div id="loading_gif"></div>
                  <p class="state_checktext" id="state_checktext">Checking for Payment.</p>
                  <p style="width: 45%;display: inline-block;text-align: right;">1 DNote = <?php echo $show_usdprice; ?> USD</p>
              </div>
            </div>
        </div>


        <script>

            var check_flag = 0;
            document.getElementById("copy_address").addEventListener("click", function() {
                copyToClipboard(document.getElementById("copyTarget"));
            });

            function copyToClipboard(elem) {
                var targetId = "_hiddenCopyText_";
                var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
                var origSelectionStart, origSelectionEnd;
                if (isInput) {
                    target = elem;
                    origSelectionStart = elem.selectionStart;
                    origSelectionEnd = elem.selectionEnd;
                } else {
                    target = document.getElementById(targetId);
                    if (!target) {
                        var target = document.createElement("textarea");
                        target.style.position = "absolute";
                        target.style.left = "-9999px";
                        target.style.top = "0";
                        target.id = targetId;
                        document.body.appendChild(target);
                    }
                    target.textContent = elem.textContent;
                }
                var currentFocus = document.activeElement;
                target.focus();
                target.setSelectionRange(0, target.value.length);
                
                var succeed;
                try {
                    succeed = document.execCommand("copy");
                } catch(e) {
                    succeed = false;
                }
                
                if (currentFocus && typeof currentFocus.focus === "function") {
                    currentFocus.focus();
                }
                
                if (isInput) {
                    elem.setSelectionRange(origSelectionStart, origSelectionEnd);
                } else {
                    target.textContent = "";
                }
                return succeed;
            }

            document.getElementById("copy_amounty").addEventListener("click", function() {
                copyToClipboardAmount(document.getElementById("copyAmount"));
            });

            function copyToClipboardAmount(elem) {
                var targetId = "_hiddenCopyText_";
                var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
                var origSelectionStart, origSelectionEnd;
                if (isInput) {
                    target = elem;
                    origSelectionStart = elem.selectionStart;
                    origSelectionEnd = elem.selectionEnd;
                } else {
                    target = document.getElementById(targetId);
                    if (!target) {
                        var target = document.createElement("textarea");
                        target.style.position = "absolute";
                        target.style.left = "-9999px";
                        target.style.top = "0";
                        target.id = targetId;
                        document.body.appendChild(target);
                    }
                    target.textContent = elem.textContent;
                }
                var currentFocus = document.activeElement;
                target.focus();
                target.setSelectionRange(0, target.value.length);
                
                var succeed;
                try {
                    succeed = document.execCommand("copy");
                } catch(e) {
                    succeed = false;
                }
                
                if (currentFocus && typeof currentFocus.focus === "function") {
                    currentFocus.focus();
                }
                
                if (isInput) {
                    elem.setSelectionRange(origSelectionStart, origSelectionEnd);
                } else {
                    target.textContent = "";
                }
                return succeed;
            }

            function showProductUrl()
            {
              var btnNode = document.getElementById("payment_state_btn");
              btnNode.innerHTML = '';
              btnNode.innerHTML = '<input type="submit" value="Payment Complete! Click HERE to download " id="download_product" class="download-btnprop">';
              document.getElementById("download_product").addEventListener("click", function() {
                  var download_url = document.getElementById("product_download_url").value;
                  window.open( download_url , '_blank' );
              });
              check_flag = 1;
            }

            var countDownDate = new Date().getTime();
            var confirmations_num = document.getElementById("confirmations_num").value;
            var tolerance = document.getElementById("tolerance").value;
            if(tolerance)
              tolerance = tolerance;
            else
              tolerance = 0.1;
            var x = setInterval(function() {
                
                if(check_flag == "0")
                {
                  var send_value_address = document.getElementById("send_value_address").value;
                  var send_mount = document.getElementById("send_mount").value;

                  var post_response = $.ajax({type: "GET", url: "https://abe.dnotescoin.com/chain/DNotes/q/invoice/<?php echo $dnotes_address; ?>+<?php echo $invoice_number; ?>", async: false}).responseText;
                  var res_data = post_response.split(",");
                  console.log(res_data[0]);
                  console.log(res_data[1]);

                  var limit_price = send_mount - tolerance;
                  if( (res_data[0] > limit_price) && (res_data[1] >= confirmations_num) )
                  {
                    showProductUrl();
                    document.getElementById("loading_gif").style.background = "none";
                    var state_checktext = document.getElementById("state_checktext");
                    state_checktext.innerHTML = '';
                    state_checktext.innerHTML = 'Payment Successful';
                  }
                }

            }, 10000);
        </script>
    </div>
</body>
</html>
