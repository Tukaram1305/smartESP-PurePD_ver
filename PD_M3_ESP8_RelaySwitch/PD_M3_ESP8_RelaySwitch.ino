#include <Arduino.h>
#ifdef ESP32
    #include <WiFi.h>
#else
    #include <ESP8266WiFi.h>
#endif
#include <espnow.h>

// Wifi ESP Board MAC Address:  94:B9:7E:14:6A:4A

const char* ssid = "MojeWifi";
const char* password = "12345678"; 

#define SERIAL_BAUDRATE     115200
#define REL_SW              0

byte SW_STATE = 0;

void OnDataRecv(uint8_t * mac, uint8_t *incomingData, uint8_t len) {
  SW_STATE = (byte)(*incomingData);
  Serial.print("Bytes received: ");
  Serial.println(len);
  Serial.print("Message: ");
  Serial.println(SW_STATE);

  digitalWrite(REL_SW, SW_STATE);
  
  Serial.println(String(ID_RELAY)+" change state to: "+String(!SW_STATE));
}

void wifiSetup() {
    WiFi.mode(WIFI_STA);
    Serial.printf("[WIFI] Connecting to %s ", ssid);
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        Serial.print(".");
        delay(100);
    }
    Serial.println();
    Serial.printf("[WIFI] STATION Mode, SSID: %s, IP address: %s\n", WiFi.SSID().c_str(), WiFi.localIP().toString().c_str());
    Serial.print("ESP Board MAC Address:  ");
    Serial.println(WiFi.macAddress());
    delay(100);

}

void setup() {
    Serial.begin(SERIAL_BAUDRATE);
    pinMode(REL_SW, OUTPUT);
    digitalWrite(REL_SW, HIGH);
    wifiSetup();

    if (esp_now_init() != 0) {
      Serial.println("Error initializing ESP-NOW");
      return;
    }
  
  esp_now_set_self_role(ESP_NOW_ROLE_SLAVE);
  esp_now_register_recv_cb(OnDataRecv);
}

void loop() {

}
