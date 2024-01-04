#include "ESP8266WiFi.h"
#include <espnow.h>
#include "leds.h"
#define WIFI_SSID "MojeWifi"
#define WIFI_PASS "12345678"

// moj MAC ESP Board MAC Address:  58:BF:25:D9:B3:BD
// leds(int red_pin, int green_pin, int blue_pin);
leds MyLeds(15,12,13);

void OnDataRecv(uint8_t * mac, uint8_t *incomingData, uint8_t len) {
  memcpy(&MyLeds.LVALS, incomingData, sizeof(MyLeds.LVALS));
  Serial.print("Dostalem bajtow: ");
  Serial.println(len);
  Serial.println("Wartosci: R="+String(MyLeds.LVALS.r)+", G="+String(MyLeds.LVALS.g)+", b="+String(MyLeds.LVALS.b));
  MyLeds.assign();
}

void startWIFI()
{
    WiFi.mode(WIFI_STA);
    Serial.printf("[WIFI] Connecting to %s ", WIFI_SSID);
    WiFi.begin(WIFI_SSID, WIFI_PASS);
    while (WiFi.status() != WL_CONNECTED) {
          Serial.print(".");
          delay(100);}
    Serial.printf("[WIFI] STATION Mode, SSID: %s, IP address: %s\n", WiFi.SSID().c_str(), WiFi.localIP().toString().c_str());
    Serial.print("ESP Board MAC Address:  ");
    Serial.println(WiFi.macAddress());
}
void setup() {

  Serial.begin(115200);
  pinMode(4,INPUT_PULLUP);
  startWIFI();
  MyLeds.ledInit();
    if (esp_now_init() != 0) {
    Serial.println("Error initializing ESP-NOW");
    return;}
    else Serial.println("ESP-NOW Status - OK");
  esp_now_set_self_role(ESP_NOW_ROLE_SLAVE);
  esp_now_register_recv_cb(OnDataRecv);
}

byte cycle=0;
void loop() {

}
