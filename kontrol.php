<?php
use Bluerhinos\phpMQTT;

require("vendor/bluerhinos/phpmqtt/phpMQTT.php");


$server = 'x2.revolusi-it.com';
$port = 1883;
$username = 'usm';
$password = 'usmjaya001';
$clientId = "G.231.22.0045_" . uniqid();
$topic = 'G.231.22.0045/control';


$mqtt = new phpMQTT($server, $port, $clientId);


$lampCommand = $_POST['lamp'] ?? $_GET['lamp'] ?? null;


if ($topic && $lampCommand) {
    
        
        if ($mqtt->connect(true, NULL, $username, $password)) {
            
            $mqtt->publish($topic, $lampCommand, 0, true);
            $mqtt->close();

            
            echo json_encode([
                'status' => 'SUCCESS',
                'message' => 'Perintah berhasil dikirim',
                'lamp' => $lampCommand
            ]);
        } else {
            
            echo json_encode([
                'status' => 'ERROR',
                'message' => 'Gagal menghubungkan ke broker MQTT'
            ]);
        }
    } else {
        
        echo json_encode([
            'status' => 'ERROR',
            'message' => 'Parameter lamp tidak valid'
        ]);
    }

?>
