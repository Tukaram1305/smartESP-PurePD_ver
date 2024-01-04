//Markowiak Pawel AsyncWebSerwer i HTTP KLIENT na ESP32DEVKIT1
#include "WiFi.h"
#include "FS.h"
#include "SPIFFS.h" // format pamieci: |-bez_OTA-|-2Mb_PROGRAM-|-2Mb _SPIFFS-|
#include <AsyncTCP.h>
#include "ESPAsyncWebServer.h"
#include <HTTPClient.h>
#include <NTPClient.h>
#include <WiFiUdp.h>
#include "ThingSpeak.h"
#include <esp_now.h>
#include <Wire.h>
#include <SPI.h>
#include <Adafruit_BMP280.h>
#include <AHT10.h>

#define BUZZER_PIN 15
const uint16_t GAS_TRESHOLD = 220;  // prog aktywacji alarmu LPG/DYM
const uint16_t CO_FAILSAFE = 15000; // brak odpowiedzi od modulu CO przez n sekund
//Moj adres MAC: 24:62:AB:DC:FD:68

#include "WiFiCredentials.h"

// adresy MAC odbiorcow
uint8_t m1switch220[] = {0x94, 0xB9, 0x7E, 0x14, 0x6A, 0x4A};   // esp-01s - relay
uint8_t m2oneLEDstrip[] = {0x94, 0xB9, 0x7E, 0x14, 0x4C, 0x77}; // esp-01s - 1-LED
uint8_t m3RGBLEDstrip[] = {0x58, 0xBF, 0x25, 0xD9, 0xB3, 0xBD}; // esp8266 witty - RGB LED
esp_now_peer_info_t m1switch220PeerInf;
esp_now_peer_info_t m2oneLEDstripPeerInf;
esp_now_peer_info_t m3RGBLEDstripPeerInf;

typedef struct BR_SENSORS 
{
  float TempCO;
  uint16_t GasAnalog;
} BR_SENSORS;

BR_SENSORS BRsensorsData = {.TempCO = 0.0, .GasAnalog = 0};

  typedef struct RBGledVals
  {
    byte r,g,b;
  } RBGledVals;

RBGledVals rgbLedVals = {.r=0, .g=0, .b=0};

typedef struct SENSORS
{
  float BMP_TEMP, BMP_PRESS, AHT_TEMP, AHT_HUM;
} SENSORS;

SENSORS sensorsData = {.BMP_TEMP=0.f, .BMP_PRESS=0.f, .AHT_TEMP=0.f, .AHT_HUM=0.f};
// ograniczenia walidacji sensorow

const float T_L_LIM = -50, T_U_LIM = 100, P_L_LIM = 800, P_U_LIM = 1150, H_L_LIM = 1, H_U_LIM = 101;

enum GAS_LEVEL { SENSOR_PROBLEM, GL_OK, GL_LOW, GL_MEDIUM, GL_HIGH };

// callback kiedy wyslano dane
void OnDataSent(const uint8_t *mac_addr, esp_now_send_status_t status) {
  Serial.print("\r\nStatus przesylki esp-now:\t");
  Serial.println(status == ESP_NOW_SEND_SUCCESS ? "Dostarczono" : "Nie dostarczono!");
}

WiFiClient  client;
String myStatus = "";

 // obiekty UDP i NTP (czas)
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "pool.ntp.org");

Adafruit_BMP280 bmp; // czujnik BMP280 na bazowym adresie 
Adafruit_Sensor *bmp_temp = bmp.getTemperatureSensor();
Adafruit_Sensor *bmp_pressure = bmp.getPressureSensor();
sensors_event_t temp_event, pressure_event;

AHT10 aht10(AHT10_ADDRESS_0X38); // czujnik AHT10 na adresie i2c 0x38

// Timery dla przechowywania czasu pomiedzy wydarzeniami
unsigned long TCW_timer =0;
unsigned long ThgSpk_timer =0;
unsigned long CO_timer =0;
unsigned long alarmTimerMs =0;
 
AsyncWebServer server(80);

uint16_t TCW_delay = 60; // bazowy czas pomiedzy zbiorem danych THP - 60 min
uint16_t ThgSpk_delay = 10; // bazowy czas pomiedzy wysylaniem do TS - 10 min
const uint16_t MSnaMin = 60000;

bool dataReadyToPOST = false;

// SWITCH ESP8266 SW1
byte SWITCH_1_STATE;
// Tasma LED 1 way White
int OneWayLED_W1;

// http GET/POST wiadomosc
String inputMessage;

String rec;
String form;

// Funkcje - deklaracje
    // glowne
void startWIFI();
String getNTPTime();
void alarm(int sigOnMs, int sigOffMs);
bool validateData();
void getSensorsData();
bool ESPNOW_INIT();
void GATHER_THP(const uint16_t DELAY);
void POST_THINGSPEAK(const uint16_t DELAY);
void GAS_SAFETY_HANDLER();
    // zarzadzanie SPIFFS
void readFile(fs::FS &fs, const char * path);
uint16_t getDataNums(fs::FS &fs, const char * path);
void readAndSend(fs::FS &fs, const char * path);
void writeFile(fs::FS &fs, const char * path, const char * message);
void appendFile(fs::FS &fs, const char * path, const char * message);

// Funkcje - definicje
#include "FS_Functions.h"
#include "myFunctions.h"

void setup(){
  Serial.begin(115200);
  pinMode(BUZZER_PIN,OUTPUT);
  digitalWrite(BUZZER_PIN,HIGH);
  
  if (!bmp.begin(0x76)) {Serial.println("Problem z czujnikiem BMP280!");}
  /* Ustawienie czujnika BMP280 */
  bmp.setSampling(Adafruit_BMP280::MODE_NORMAL,     /*  Mode. */
                  Adafruit_BMP280::SAMPLING_X2,     /* Temp. oversampling */
                  Adafruit_BMP280::SAMPLING_X16,    /* Pressure oversampling */
                  Adafruit_BMP280::FILTER_X16,      /* Filtering. */
                  Adafruit_BMP280::STANDBY_MS_500); /* Standby time. */

 if (!aht10.begin()) {Serial.println("Problem z czujnikiem AHT10!");}

 if(!SPIFFS.begin()) {Serial.println("Blad przy montowaniu systemu plikow SPIFFS!");}
 
 startWIFI();

 // Inicjalizuj klienta NTP
 timeClient.begin();
 timeClient.setTimeOffset(3600); // offset dla GMT+1 (Polska) +1G zmiana czasu

 #include "asyncws.h"
 ThingSpeak.begin(client);

 // Init ESP-NOW
 if( ESPNOW_INIT() == false) {Serial.println("Blad pczy inicjalizacji ESP-NOW!"); }
}

void loop(){
// Asynchroniczny webserwer ustawi flage aby wywolac funckje sprawdzajaca czy sa dane
// do wyslania - przy wywolaniu z z poziomu AWS spowoduje zablokowanie rdzenia (0) i wyrzuci blad watchdog
if (dataReadyToPOST){ readAndSend(SPIFFS, "/dane.txt"); dataReadyToPOST = false; }

GATHER_THP(TCW_delay);          // opoznienie pobierania danych dla skladowania (domyslnie) 60 min

POST_THINGSPEAK(ThgSpk_delay);  // opoznienie aktualizacji ThingSpeak (domyslnie) 10 min

GAS_SAFETY_HANDLER();

if (WiFi.status() != WL_CONNECTED) startWIFI();
} // loop
