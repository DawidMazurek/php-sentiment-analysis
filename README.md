# php-sentiment-analysis

## Proof of concept for text message emotional analysis, as part of project built during short hackaton. Detecting sentiment using keywords grouped by emotions

### How to run
* composer install
* php analysis.php

```
Message: Witam. Dziękuję za szybką przesyłkę. Wszystko dobrze działa i synek jest zadowolony.
Detected emotion: happiness
```

First run takes about 3 minutes, because machine needs to learn how to reconise emotions. Further runs use serialized cache, so write acces do `/cache` is required

Dictionary is located in `/data/nawl-analysis-2-cols.csv`, which is build using a publication of Nencki Institute, on a research over Polish language emotional words, which results can be found at http://exp.lobi.nencki.gov.pl/nawl-analysis 
