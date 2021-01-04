<?php
    session_start();

    $rawPostData = $_POST["postData"];
    $postData = json_decode($rawPostData, true);
    if(!is_array($postData)) {
		$reply->status = "Bad";
		$reply->message = "data is not an array";
		echo json_encode($reply);
		return;
    }
    if(!array_key_exists("Command", $postData)) {
		$reply->status = "Bad";
		$reply->message = "data has no Command";
		echo json_encode($reply);
		return;
	}
	$command = $postData["Command"];
    if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))) {
        $errorCode = socket_last_error();
        $reply->status = "Bad";
        $reply->message = "Socket error, ".socket_strerror($errorCode)." ".$errorCode;
        echo json_encode($reply);
        return;
    }
    socket_set_nonblock($sock);
    if($command == "Login") {
        if(!array_key_exists("Username", $postData) || !array_key_exists("Password", $postData)) {
            $reply->status = "Bad";
            $reply->message = "Missing username or password";
            echo json_encode($reply);
        }
        else {
            $jOutMessage["Command"] = "CheckMatch";
            $jOutMessage["Username"] = $postData["Username"];
            $jOutMessage["Password"] = $postData["Password"];
            $outMessage = json_encode($jOutMessage);
            if(!socket_sendto($sock, $outMessage, strlen($outMessage), 0, "127.0.0.1", 9090)) {
                $errorcode = socket_last_error();
                $reply->status = "Bad";
                $reply->message = "Socket error, ".socket_strerror($errorCode)." ".$errorCode;
                echo json_encode($reply);
                return;
            }
            for($i = 0; $i<20; $i++) {
                usleep(50000);
                $inRawMessage = socket_read($sock, 256*256);
                if($inRawMessage) {
                    $inMessage = json_decode($inRawMessage, true);
                    if(array_key_exists("Status", $inMessage) && array_key_exists("Match", $inMessage)) {
                        $status = $inMessage["Status"];
                        $match = $inMessage["Match"];
                        if($status == "Good" && $match) {
                            $reply->status = "Good";
                            //Create session variables
                            $_SESSION['valid'] = true;
                            $_SESSION['begin'] = time();
                            echo json_encode($reply);
                        }
                        else {
                            $reply->status = "Bad";
                            echo json_encode($reply);
                        }
                        return;
                    }
                }
            }
            //echo var_dump($inMessage);
            $reply->status = "Bad";
            $reply->message = "Timeout";
            echo json_encode($reply);
            return;
        }
    }
    else if($command == "Logout") {
        session_unset();
        session_destroy();
        return;
    }
    else if($command == "ChangeUsername" || $command == "ChangePassword") {
        $outMessage = $rawPostData;
        if(!socket_sendto($sock, $outMessage, strlen($outMessage), 0, "127.0.0.1", 9090)) {
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
    }
    else {
        $reply->status = "Bad";
        $reply->message = "Unknown command";
        echo json_encode($reply);
        return;
    }
?>