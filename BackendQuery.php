<?php
    session_start();
    if(isset($_SESSION['begin'])) {
        if(time()-$_SESSION['begin'] > 300) {
            session_unset();
            session_destroy();
            $reply->status = "Bad";
            $reply->message = "Prohibited";
            echo json_encode($reply);
            return;
        }
    }
    else {
        $reply->status = "Bad";
        $reply->message = "Prohibited";
        echo json_encode($reply);
        return;
    }
    $_SESSION['begin'] = time();

    $rawPostData = $_POST["postData"];
    if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))) {
        $errorCode = socket_last_error();
        $reply->status = "Bad";
        $reply->message = "Socket error, ".socket_strerror($errorCode)." ".$errorCode;
        echo json_encode($reply);
        return;
    }
    socket_set_nonblock($sock);
    $outMessage = $rawPostData;
    if(!socket_sendto($sock, $outMessage, strlen($outMessage), 0, "127.0.0.1", 10520)) {
        $errorcode = socket_last_error();
        $reply->status = "Bad";
        $reply->message = "Socket error, ".socket_strerror($errorCode)." ".$errorCode;
        echo json_encode($reply);
        return;
    }
    for($i = 0; $i<20; $i++) {
        usleep(50000);
        $inMessage = socket_read($sock, 256*256);
        if($inMessage) {
            //$reply->status = "Good";
            //$reply->data = json_decode($inMessage, true);
            //echo json_encode($reply);
            echo $inMessage;
            return;
        }
    }
    //echo var_dump($inMessage);
    $reply->status = "Bad";
    $reply->message = "Timeout";
    echo json_encode($reply);
    return;
?>