<?php


use Bluerhinos\phpMQTT;

require("vendor/bluerhinos/phpmqtt/phpMQTT.php");


$mqttServer   = 'x2.revolusi-it.com';
$mqttPort     = 1883;
$mqttUsername = 'usm';
$mqttPassword = 'usmjaya001';
$mqttClientId = '';


$mqtt = new phpMQTT($mqttServer, $mqttPort, $mqttClientId);

if (!$mqtt->connect(true, NULL, $mqttUsername, $mqttPassword)) {
    exit(json_encode(["error" => "Failed to connect to MQTT server."]));
}


$topicTemperature = 'G.231.22.0045/temperature';
$topicHumidity    = 'G.231.22.0045/humidity';


$tempMessage   = $mqtt->subscribeAndWaitForMessage($topicTemperature, 0);
$humidityMessage = $mqtt->subscribeAndWaitForMessage($topicHumidity, 0);


$mqtt->close();


$dbServer   = "localhost";
$dbUsername = "root"; 
$dbPassword = "";    
$dbName     = "prak_iot_sensor_data";

$conn = new mysqli($dbServer, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}


$stmt = $conn->prepare("INSERT INTO readings (suhu, kelembapan) VALUES (?, ?)");
$stmt->bind_param("ss", $tempMessage, $humidityMessage);

if ($stmt->execute()) {
    $dbResponse = "Data inserted successfully.";
} else {
    $dbResponse = "Failed to insert data: " . $stmt->error;
}
$stmt->close();


$sql = "SELECT suhu AS temperature, kelembapan AS humidity, timestamp FROM readings ORDER BY timestamp DESC LIMIT 10";
$result = $conn->query($sql);

$sensorData = [];
while ($row = $result->fetch_assoc()) {
    $sensorData[] = $row;
}
$conn->close();


header('Content-Type: application/json');
$response = [
    "mqtt_data" => [
        "temperature" => $tempMessage,
        "humidity"    => $humidityMessage
    ],
    "db_message" => $dbResponse,
    "sensors_data" => $sensorData
];

echo json_encode($response);

?>
