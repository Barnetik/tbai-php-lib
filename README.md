# Tbai PHP lib

TicketBai sistema erabiltzeko PHP liburutegia
Librería para integrarse con el sistema TicketBai


## Egungo Funtzionalitateak / Funcionalidades actuales

 * Fakturen, fakturen zuzenketen eta hauen baliogabetzeen TicketBai formatudun XML-a sortu
 * Fakturak, fakturen zuzenketak eta hauen baliogabetzeak XaDES motako sinadurarekin sinatu
 * Fakturak, fakturen zuzenketak eta hauen baliogabetzeak EAEko hiru diputazioetako zerbitzuetara igorri. 
 * Bizkaiko kasuan (Batuz), pertsona fisikoentzako 140 modeloa ere badabil hiru kasuetarako (igorketa, zuzenketa eta baliogabetzeak)
 * Gipuzkoa eta Arabako __Zuzendu__ zerbitzuarekin integrazioa. Igorritako fakturen aldaketa eta zuzenketak baimentzen dituen zerbitzua. [@areinaNubeApp](https://github.com/areinaNubapp)-en ekarpenei esker.

----

 * Generar XML en formato TicketBai de facturas, facturas rectificativas y cancelaciones de factura
 * Firmar estos documentos con firma XaDES
 * Enviar estos documentos a los servicios de las tres haciendas forales de la CAV.
 * En el caso de Bizkaia (Batuz), es posible también emitir, rectificar y cancelar facturas para personas físicas utilizando el modelo 140.
 * Integración con el servicio __Zuzendu__ de Araba y Gipuzkoa que permite la subsanación y modificación de facturas emitidas. Gracias a las aportaciones de [@areinaNubeApp](https://github.com/areinaNubapp)


## Instalazioa / Instalación
```shell
composer require barnetik/ticketbai
```

## Erabilgarri dauden JSON dokumentuen definizioak / Definición de los documentos JSON disponibles
Hurrengo helbidean, dokumentuak sortzeko erabili daitezkeen JSON dokumentuen definizioak topatu ahal ditzazkezue:

Podéis encontrar las definiciones de los JSON disponibles para la generación de documentos en el siguiente enlace:

https://barnetik.github.io/tbai-php-lib/

## Erabilera adibideak / Ejemplos de uso


### Fakturaren sinaketa / Firma de una factura
[Adibide moduan erabiltzen dugun JSON fitxategia / JSON usado como ejemplo](./tests/Barnetik/Tbai/__files/tbai-sample.json)

```php
use Barnetik\Tbai\Fingerprint\Vendor;
use Barnetik\Tbai\PrivateKey;
use Barnetik\Tbai\TicketBai;

$license = 'LICENCIA_DESARROLLO';
$developerCif = 'CIF';
$appName = 'TBAI PHP APP';
$version = '1.0';

$certificatePath = '/path/to/certificate.p12';
$certificatePassword = 'myCertificatePassword';

// Where we want the signed document to be stored
$signedXmlPath = './signed.xml';

$ticketbai = TicketBai::createFromJson(
    new Vendor($license, $developerCif, $appName, $version),
    json_decode(file_get_contents('tbai-sample.json'), true)
);

$ticketbai->sign(
    PrivateKey::p12($certificatePath),
    $certificatePassword,
    $signedXmlPath
);


```

### Faktura bidaltzea / Envío de la factura
```php
// We have an endpoint for each province Araba, Bizkaia or Gipuzkoa
use Barnetik\Tbai\Api\Bizkaia\Endpoint as BizkaiaEndpoint;

/**
 * BizkaiaEndpoint(bool $dev = false, bool $debug = false)
 * For production usage $dev param must be false
 * $bizkaiaEndpoint = new BizkaiaEndpoint();
 */
$bizkaiaEndpoint = new BizkaiaEndpoint(true, true);
$result = $bizkaiaEndpoint->submitInvoice(
    $ticketbai,
    PrivateKey::p12($certificatePath),
    $certificatePassword,
);


if ($result->isDelivered()) {
    var_dump('SUCCESS!');
} else {
    var_dump($result->errorDataRegistry());
    var_dump($result->headers());
}
```

### Hurrengo faktura kateatzeko behar den sinadura zatia lortu  / Obtener el trozo de firma necesaria para encadenar la próxima factura
```php
$shortSignatureValue = $ticketbai->chainSignatureValue();
```

### QR kodea sortzea  / Creación del QR
```php
use Barnetik\Tbai\Qr;

/**
 * Qr(TicketBai $ticketBai, bool $dev = false)
 * For production usage $dev param must be false
 * $qr = new Qr($ticketbai);
 */
$qr = new Qr($ticketbai, true);


$qr->savePng('/path/to/qr.png');

// Get the code that must be shown over QR on any invoice
$tbaiIdentifier = $qr->ticketbaiIdentifier();

```
