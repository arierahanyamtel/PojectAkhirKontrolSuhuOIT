#include <EEPROM.h>
#include <ESP8266WiFi.h>
#include <PubSubClient.h>
#include <LiquidCrystal_I2C.h>
#include <Wire.h>
#include "DHT.h"


#define lampu0 D0
#define lampu3 0
#define lampu4 2
#define lampu6 12
#define lampu7 13
#define lampu8 15
#define DHTPIN D5
#define DHTTYPE DHT11


const char* ssid = "ONECARE_wifi";
const char* password = "duasaudara";
const char* revolusi_server = "x2.revolusi-it.com";
const char* user = "usm";
const char* pass = "usmjaya001";
const char* topik_temperature = "G.231.22.0045/temperature"; 
const char* topik_humidity = "G.231.22.0045/humidityy";   
const char* topik_control = "G.231.22.0045/control";

WiFiClient espClient;
PubSubClient client(espClient);
LiquidCrystal_I2C lcd(0x27, 16, 2);
DHT dht(DHTPIN, DHTTYPE);


bool statusLampu6 = false;
bool statusLampu7 = false;
bool statusLampu8 = false;
bool kontrolManual = false; 

void reconnect() {
    while (!client.connected()) {
        Serial.print("Menghubungkan ke revolusi Server: ");
        Serial.println(revolusi_server);
        if (client.connect("G.231.22.0045", "usm", "usmjaya001")) {
            Serial.println("Terhubung ke revolusi");
            
            client.subscribe(topik_control);
        } else {
            Serial.print("Gagal, rc=");
            Serial.print(client.state());
            Serial.println(" Coba lagi dalam 5 detik...");
            delay(5000);
        }
    }
}



void callback(char* topic, byte* payload, unsigned int length) {
    String pesan = "";
    for (int i = 0; i < length; i++) {
        pesan += (char)payload[i];
    }
    
    Serial.print("Pesan diterima pada topik ");
    Serial.print(topic);
    Serial.print(": ");
    Serial.println(pesan);
    
    if (String(topic) == topik_control) {
        kontrolManual = true; 
        if (pesan == "D1_ON") {
            digitalWrite(lampu6, HIGH);
            statusLampu6 = true;
            Serial.println("Lampu D1 ON (Manual)");
        } else if (pesan == "D1_OFF") {
            digitalWrite(lampu6, LOW);
            statusLampu6 = false;
            Serial.println("Lampu D1 OFF (Manual)");
        } else if (pesan == "D2_ON") {
            digitalWrite(lampu7, HIGH);
            statusLampu7 = true;
            Serial.println("Lampu D2 ON (Manual)");
        } else if (pesan == "D2_OFF") {
            digitalWrite(lampu7, LOW);
            statusLampu7 = false;
            Serial.println("Lampu D2 OFF (Manual)");
        } else if (pesan == "D3_ON") {
            digitalWrite(lampu8, HIGH);
            statusLampu8 = true;
            Serial.println("Lampu D3 ON (Manual)");
        } else if (pesan == "D3_OFF") {
            digitalWrite(lampu8, LOW);
            statusLampu8 = false;
            Serial.println("Lampu D3 OFF (Manual)");
        } else if (pesan == "AUTO") {
            kontrolManual = false; 
            Serial.println("Mode otomatis aktif");
        } else {
            Serial.println("Perintah tidak valid");
        }
    }
}

void konek_wifi() {
    WiFi.begin(ssid, password);
    Serial.print("Menghubungkan ke WiFi");
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nWiFi connected");
}

void setup() {
    Serial.begin(9600);

    konek_wifi();
    client.setServer(revolusi_server, 1883);
    client.setCallback(callback);

    
    pinMode(lampu0, OUTPUT);
    pinMode(lampu3, OUTPUT);
    pinMode(lampu4, OUTPUT);
    pinMode(lampu6, OUTPUT);
    pinMode(lampu7, OUTPUT);
    pinMode(lampu8, OUTPUT);

    digitalWrite(lampu6, LOW);
    digitalWrite(lampu7, LOW);
    digitalWrite(lampu8, LOW);

    Wire.begin();
    lcd.begin(16, 2);
    lcd.backlight();
    dht.begin();
}

void loop() {
    if (WiFi.status() != WL_CONNECTED) {
        konek_wifi();
    }
    if (!client.connected()) {
        reconnect();
    }
    client.loop();

    float h = dht.readHumidity();
    float t = dht.readTemperature();

    if (isnan(h) || isnan(t)) {
        Serial.println("Gagal membaca sensor DHT!");
        delay(2000);
        return;
    }

   
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Suhu: " + String(t) + "C");
    lcd.setCursor(0, 1);
    lcd.print("Kelembaban: " + String(h) + "%");

      
    String dataSuhu = String(t);  
    String dataHumidity = String(h);  

    client.publish("G.231.22.0045/humidity", dataHumidity.c_str());  
    
    Serial.println("Temperature: " + dataSuhu);
    Serial.println("Humidity: " + dataHumidity);

    
    client.publish("G.231.22.0045/temperature", dataSuhu.c_str());  

    
    if (!kontrolManual) {
        if (t >= 29 && t < 30) {
            digitalWrite(lampu6, LOW);
            digitalWrite(lampu7, HIGH);
            digitalWrite(lampu8, LOW);
        } else if (t >= 30 && t < 31) {
            digitalWrite(lampu6, LOW);
            digitalWrite(lampu7, LOW);
            digitalWrite(lampu8, HIGH);
        } else if (t >= 31) {
            digitalWrite(lampu6, LOW);
            digitalWrite(lampu7, HIGH);
            digitalWrite(lampu8, HIGH);
        } else {
            digitalWrite(lampu6, HIGH);
            digitalWrite(lampu7, LOW);
            digitalWrite(lampu8, LOW);
        }
    }

    delay(5000); 
}
