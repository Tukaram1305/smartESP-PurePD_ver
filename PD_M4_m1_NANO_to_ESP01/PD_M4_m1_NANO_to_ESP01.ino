#include<SoftwareSerial.h>
SoftwareSerial espUART(2, 3);  //RX, TX - przez -1k-out(3v3)-2k-GND
#include <OneWire.h>
#include <DallasTemperature.h>
#define ONE_WIRE_BUS 5

OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature sensors(&oneWire);

unsigned long valDelMs = 0;
int valDel = 5000;
unsigned long dangerMs = 0;
int dangerDel = 200;
void setup()
{
  Serial.begin(115200);
  espUART.begin(9600);
  sensors.begin();
  pinMode(6,OUTPUT);
}

// Zmienne dla wartosci
uint16_t gasAVal = 0;
float COTempVal = 0.0;

// Funkcja odpowiedzialna za sygnalizacje diody LED
void notifyDanger(uint16_t gas)
{
  if (gas <= 200 && millis() > dangerMs+dangerDel)
  {
    digitalWrite(6,LOW);
    dangerMs = millis();
  }
  else if ((gas > 200 && gas <=250) && millis() > dangerMs+dangerDel)
  {
    digitalWrite(6,!digitalRead(6));
    dangerDel = 500;
    dangerMs = millis();
  }
  else if ((gas > 250 && gas <=300) && millis() > dangerMs+dangerDel)
  {
    digitalWrite(6,!digitalRead(6));
    dangerDel = 250;
    dangerMs = millis();
  }
  else if (gas > 300 && millis() > dangerMs+dangerDel)
  {
    digitalWrite(6,!digitalRead(6));
    dangerDel = 100;
    dangerMs = millis();
  }

}

void loop()
{
  if (millis()>valDelMs+valDel)
  {
  valDelMs = millis();
  sensors.requestTemperatures();
  gasAVal = analogRead(A3);
  COTempVal = sensors.getTempCByIndex(0);
  
  Serial.print("Podaje dane: Gas= "+String(gasAVal)+", Temp= "+String(COTempVal,2)+"\n");

  // Wyslij dane przez software UART do ESP-01s
  espUART.print(String(COTempVal,2)+">"+String(gasAVal));
  espUART.flush();
  }

  notifyDanger(gasAVal);
}
