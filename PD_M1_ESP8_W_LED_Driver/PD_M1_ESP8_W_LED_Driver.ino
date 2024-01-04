#include <Arduino.h>
#ifdef ESP32
    #include <WiFi.h>
#else
    #include <ESP8266WiFi.h>
#endif
#define WIFI_SSID "RagnNet"
#define WIFI_PASS "azgard666"

#include <espnow.h>

// ESP Board MAC Address:  94:B9:7E:14:4C:77

#define LED_BL 2
uint16_t LED_VAL = 0; 

void OnDataRecv(uint8_t * mac, uint8_t *incomingData, uint8_t len) {
  memcpy(&LED_VAL, incomingData, sizeof(LED_VAL));
  Serial.print("Dostalem bajtow: ");
  Serial.println(len);
  Serial.print("PWM na: ");
  Serial.println(LED_VAL);
  analogWrite(LED_BL, LED_VAL);
}

void setup() 
{
  Serial.begin(115200);
  pinMode(LED_BL,OUTPUT);
  analogWriteResolution(10);
  analogWrite(LED_BL,0);
    
  wifiSetup();
  // Init ESP-NOW
  if (esp_now_init() != 0) {
    Serial.println("Error initializing ESP-NOW");
    return;
  }
  
  esp_now_set_self_role(ESP_NOW_ROLE_SLAVE);
  esp_now_register_recv_cb(OnDataRecv);
} // setup

void wifiSetup() {

    WiFi.mode(WIFI_STA);
    Serial.printf("[WIFI] Connecting to %s ", WIFI_SSID);
    WiFi.begin(WIFI_SSID, WIFI_PASS);
    while (WiFi.status() != WL_CONNECTED) {
        delay(100);
    }
    Serial.printf("[WIFI] STATION Mode, SSID: %s, IP address: %s\n", WiFi.SSID().c_str(), WiFi.localIP().toString().c_str());
    Serial.print("ESP Board MAC Address:  ");
    Serial.println(WiFi.macAddress());
}

void loop() 
{
  
}
