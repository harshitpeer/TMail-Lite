<?php
require_once './config.php';
require_once './PhpImap/__autoload.php';
session_start();
if(isset($_GET["action"])) {
    if($_GET["action"] == "clear") {
        session_destroy();
        header("Location: ./");
        die();
    }
}
if(isset($_SESSION["address"])) {
    $address = $_SESSION["address"];
} else {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 10; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    $address = $randomString."@".$config["domain"];
    $_SESSION["address"] = $address;
}
$mailbox = new PhpImap\Mailbox('{'.$config['host'].'/imap/ssl}INBOX', $config['user'], $config['pass'], __DIR__);
$toList = "TO ".$address;
$ccList = "CC ".$address;
$bccList = "BCC ".$address;
$mailIdsTo = $mailbox->searchMailbox($toList);
$mailIdsCc = $mailbox->searchMailbox($ccList);
$mailIdsBcc = $mailbox->searchMailbox($bccList);
$mailsIds = array_reverse(array_unique(array_merge($mailIdsTo,$mailIdsCc,$mailIdsBcc)));
if($unseen == 1) {
    $unseenIds = $mailbox->searchMailbox("UNSEEN");
    $mailsIds = array_intersect($mailsIds,$unseenIds);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $config['title']; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400" rel="stylesheet"> 
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
        <div class="title">Your MailBox <strong><?php echo $address; ?></strong> is ready to use. </div>
        <button style="width: 100%;" type="button" class="btn btn-info" onClick="window.location.reload();">Refresh</button>
        <br>
        <br>
        <?php 
        foreach ($mailsIds as $mailID) {
            $mail = $mailbox->getMail($mailID);
            ?>
            <div id="mail<?php echo $mailID; ?>">
                <button class="accordion"><?php echo $mail->subject ?><br>From : <?php echo $mail->fromName; ?>&lt;<?php echo $mail->fromAddress; ?>&gt;<span style="float: right"><?php echo $mail->date; ?></span></button>
                <div class="panel">  
                    <br>
                    <?php if ($mail->textHtml == "") { ?>
                    <div><?php echo $mail->textPlain; ?></div>
                    <?php } else { ?>
                    <div><?php echo $mail->textHtml; ?></div>
                    <?php } ?>
                    <br>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="container">
        <a href="?action=clear"><button type="button" class="btn btn-warning">Get New Email</button></a>
    </div>
    <script>
        $( function() {
            $( "#accordion" ).accordion();
          } );
    </script>
    <script>
    var acc = document.getElementsByClassName("accordion");
    var i;
    
    for (i = 0; i < acc.length; i++) {
      acc[i].onclick = function() {
        this.classList.toggle("active");
        var panel = this.nextElementSibling;
        if (panel.style.maxHeight){
          panel.style.maxHeight = null;
        } else {
          panel.style.maxHeight = panel.scrollHeight + "px";
        }
      }
    }
    </script>
</body>
</html>
    