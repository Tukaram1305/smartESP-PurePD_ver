#ifndef LEDS_H
#define LEDS_H
class leds
{
  typedef struct lvals
  {
    byte r,g,b;
  } lvals;


  public:
  leds(int r, int g, int b) : R_PIN(r), G_PIN(g), B_PIN(b) {}
  ~leds(){}
  
  lvals LVALS;

void ledInit()
{
  pinMode(R_PIN,OUTPUT);
  pinMode(G_PIN,OUTPUT);
  pinMode(B_PIN,OUTPUT);
  analogWrite(R_PIN,0);
  analogWrite(G_PIN,0);
  analogWrite(B_PIN,0);
  LVALS = {0,0,0};
}
void assign()
{
  analogWrite(R_PIN,LVALS.r);
  analogWrite(G_PIN,LVALS.g);
  analogWrite(B_PIN,LVALS.b);
}
  private:
  const int R_PIN,G_PIN,B_PIN;
  
};
#endif
