<?php

namespace Barnetik\Tbai\LROE\Expenses;

use Barnetik\Tbai\Exception\InvalidVatRegimeException;
use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Amount;
use DOMDocument;
use DOMNode;
use InvalidArgumentException;
use OutOfBoundsException;

abstract class AbstractData implements TbaiXml
{
    const VAT_REGIME_01 = '01';
    const VAT_REGIME_02 = '02';
    const VAT_REGIME_03 = '03';
    const VAT_REGIME_04 = '04';
    const VAT_REGIME_05 = '05';
    const VAT_REGIME_07 = '07';
    const VAT_REGIME_08 = '08';
    const VAT_REGIME_09 = '09';
    const VAT_REGIME_12 = '12';
    const VAT_REGIME_13 = '13';

    private string $description;
    private Amount $total;
    private array $vatRegimes = [];

    protected function __construct(string $description, Amount $total, array $vatRegimes)
    {
        $this->description = $description;
        $this->total = $total;
        $this->setVatRegimes($vatRegimes);
    }

    private function setVatRegimes(array $vatRegimes): static
    {
        if (!sizeof($vatRegimes)) {
            throw new InvalidArgumentException('Empty vat regimes is not allowed');
        }

        foreach ($vatRegimes as $vatRegime) {
            $this->addVatRegime($vatRegime);
        }

        return $this;
    }

    public function addVatRegime(string $vatRegime): static
    {
        if (!in_array($vatRegime, static::validVatRegimes())) {
            throw new InvalidVatRegimeException();
        }

        if (sizeof($this->vatRegimes) >= 3) {
            throw new OutOfBoundsException('Too many VAT Regimes provided');
        }

        $this->vatRegimes[] = $vatRegime;
        return $this;
    }

    protected static function validVatRegimes(): array
    {
        return [
            static::VAT_REGIME_01,
            static::VAT_REGIME_02,
            static::VAT_REGIME_03,
            static::VAT_REGIME_04,
            static::VAT_REGIME_05,
            static::VAT_REGIME_07,
            static::VAT_REGIME_08,
            static::VAT_REGIME_09,
            static::VAT_REGIME_12,
            static::VAT_REGIME_13,
        ];
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $data = $domDocument->createElement('DatosFactura');

        $data->appendChild(
            $domDocument->createElement(
                'DescripcionOperacion',
                htmlspecialchars($this->description, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8')
            )
        );

        $vatRegimeKeys = $domDocument->createElement('Claves');
        foreach ($this->vatRegimes as $vatRegime) {
            $keyId = $domDocument->createElement('IDClave');
            $keyId->appendChild(
                $domDocument->createElement('ClaveRegimenIvaOpTrascendencia', $vatRegime)
            );

            $vatRegimeKeys->appendChild($keyId);
        }
        $data->appendChild($vatRegimeKeys);

        $data->appendChild(
            $domDocument->createElement('ImporteTotalFactura', $this->total)
        );

        return $data;
    }

    public function total(): Amount
    {
        return $this->total;
    }

    public static function createFromJson(array $jsonData): static
    {
        $description = $jsonData['description'];
        $total = new Amount($jsonData['total']);
        $vatRegimes = $jsonData['vatRegimes'];

        /** @phpstan-ignore-next-line */
        $invoiceData = new static($description, $total, $vatRegimes);

        return $invoiceData;
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'description' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 250,
                ],
                'total' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Zenbatekoa guztira (2 dezimalekin) - Importe total (2 decimales)'
                ],
                'vatRegimes' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                        'enum' => static::validVatRegimes(),
                        'description' => '
 * 01: Erregimen orokorreko eragiketa eta hurrengo balioetan jaso gabe dagoen beste edozein kasu - Operación de régimen general y cualquier otro supuesto que no esté recogido en los siguientes valores
 * 02: Esportazioa - Exportación
 * 03: Erabilitako ondasunen, arte objektuen, zaharkinen eta bilduma objektuen araudi berezia aplikatzen zaien eragiketak - Operaciones a las que se aplique el régimen especial de bienes usados, objetos de arte, antigüedades y objetos de colección
 * 04: Inbertsio urrearen araubide berezia - Régimen especial del oro de inversión
 * 05: Bidaia-agentzien araubide berezia - Régimen especial de las agencias de viajes
 * 07: Kutxa-irizpidearen araubide berezia - Régimen especial del criterio de caja
 * 08: Ekoizpen, Zerbitzu eta Inportazioaren gaineko Zergari / Kanarietako Zeharkako Zerga Orokorrari lotutako eragiketak - Operaciones sujetas al IPSI/IGIC (Impuesto sobre la Producción, los Servicios y la Importación / Impuesto General Indirecto Canario)
 * 09: Besteren izenean eta kontura ari diren bidai agentziek egindako zerbitzuen fakturazioa(Fakturazio Araudiko 3. xedapen gehigarria) - Facturación de las prestaciones de servicios de agencias de viaje que actúan como mediadoras en nombre y por cuenta ajena (disposición adicional 3ª del Reglamento de Facturación)
 * 12: Negozio lokala errentatzeko eragiketak, atxikipenari lotu gabeak - Operaciones de arrendamiento de local de negocio no sujetos a retención
 * 13: Negozio lokala errentatzeko eragiketak, atxikipenari lotuak eta lotu gabeak - Operaciones de arrendamiento de local de negocio sujetas y no sujetas a retención
                        '
                    ],
                    'minItems' => 1,
                    'maxItems' => 3,
                    'description' => 'Gakoak: BEZaren araubideen eta zerga-ondorioak dauzkaten eragiketak - Claves de regímenes de IVA y operaciones con trascendencia tributaria'
                ],
            ],
            'required' => ['description', 'total', 'vatRegimes']
        ];
    }

    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'total' => (string)$this->total,
            'vatRegimes' => $this->vatRegimes,
        ];
    }
}
