#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>

const char ssid[] = "MojeWifi";
const char pass[] = "12345678";

typedef struct vals 
{
  float fval;
  uint16_t ival;
} vals;

vals VALS;

void parseData(String rawData, vals* Vals)
{
    int len = rawData.length();
    int sepPos = rawData.indexOf('>');
    String bufFloat, bufInt;
    bufFloat = rawData.substring(0,sepPos); // od 0 do '<' - rozlacznie (konczy 1 char przed pozycja)
    bufInt = rawData.substring(sepPos+1,len);
    Vals->fval = bufFloat.toFloat();
    Vals->ival = bufInt.toInt();
}

void startWiFi()
{
    WiFi.mode(WIFI_STA);
    WiFi.begin(ssid, pass);
    while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.println("Lacze z WiFi..");
    }
}

void setup()
{
  Serial.begin(9600);
  delay(200);
  startWiFi();
}

String rawMessage = "";

void loop()
{
rawMessage="";
  while (Serial.available() > 0)
    {
      char c = Serial.read();
      rawMessage+=c;
      delay(20);
    }
    
  if (rawMessage!="") 
    {
      Serial.println(rawMessage);
      parseData(rawMessage, &VALS);
      Serial.print("Sparsowane: float= "+String(VALS.fval)+", int= "+String(VALS.ival)+"\n");

    // AKTYWNE POLACZENIE Z WIFI
    if(WiFi.status()== WL_CONNECTED){
      WiFiClient client;
      HTTPClient http;

      // Konkatenacja wartosci do zapytania HTTP
      String serverPath = "http://192.168.1.35/getCOData?BR_T="+String(VALS.fval,2)+"&BR_G="+String(VALS.ival);

      http.begin(client, serverPath.c_str());
      int httpResponseCode = http.GET(); // Wyslij do jednostki centralnej ESP32
      
      if (httpResponseCode>0) {
        Serial.print("HTTP Response code: ");
        Serial.println(httpResponseCode);
        String payload = http.getString();
        Serial.println(payload);
      }
      else {
        Serial.print("Error code: ");
        Serial.println(httpResponseCode);
      }
      // Zwolnij zasoby
      http.end();
    }
    // BRAK POLACZENIA WIFI - LACZ PONOWNIE . . .
    else {
            Serial.println("WiFi Disconnected");
            startWiFi();
         }
    } // Dostalem wiadomosc po sUART od NANO
}
