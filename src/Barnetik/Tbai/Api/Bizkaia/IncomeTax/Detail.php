<?php

namespace Barnetik\Tbai\Api\Bizkaia\IncomeTax;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Amount;
use DOMDocument;
use DOMNode;

class Detail implements TbaiXml
{
    private ?Amount $incomeTaxAmount = null;
    private string $epigraph;
    private bool $distinctVatBase = false;
    private bool $withCollectionAndPaymentCriteria = false;

    private function __construct(string $epigraph, Amount $incomeTaxAmount = null)
    {
        $this->epigraph = $epigraph;
        if ($incomeTaxAmount) {
            $this->incomeTaxAmount = $incomeTaxAmount;
            $this->distinctVatBase = true;
        }
    }

    public static function create(string $epigraph, Amount $incomeTaxAmount = null): self
    {
        $incomeTaxDetail = new self($epigraph, $incomeTaxAmount);
        $incomeTaxDetail->withCollectionAndPaymentCriteria = false;
        return $incomeTaxDetail;
    }

    public static function createWithCollectionAndPaymentCriteria(string $epigraph, Amount $incomeTaxAmount = null): self
    {
        $incomeTaxDetail = new self($epigraph, $incomeTaxAmount);
        $incomeTaxDetail->withCollectionAndPaymentCriteria = true;
        return $incomeTaxDetail;
    }

    public function setWithCollectionAndPaymentCriteria(bool $value = true): self
    {
        $this->withCollectionAndPaymentCriteria = $value;
        return $this;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $incomeTaxDetail = $domDocument->createElement('DetalleRenta');
        $incomeTaxDetail->appendChild($domDocument->createElement('Epigrafe', $this->epigraph));
        if ($this->distinctVatBase) {
            $incomeTaxDetail->appendChild($domDocument->createElement('IngresoAComputarIRPFDiferenteBaseImpoIVA', 'S'));
            $incomeTaxDetail->appendChild($domDocument->createElement('ImporteIngresoIRPF', $this->incomeTaxAmount));
        }
        $incomeTaxDetail->appendChild($domDocument->createElement('CriterioCobrosYPagos', $this->withCollectionAndPaymentCriteria ? 'S' : 'N'));
        return $incomeTaxDetail;
    }

    public static function createFromJson(array $jsonData): self
    {
        $epigraph = $jsonData['epigraph'];
        $incomeTaxAmount = null;
        if (isset($jsonData['incomeTaxAmount']) && $jsonData['incomeTaxAmount'] !== '') {
            $incomeTaxAmount = new Amount($jsonData['incomeTaxAmount']);
        }
        $withCollectionAndPaymentCriteria = $jsonData['collectionAndPaymentCriteria'] ?? false;

        $incomeTaxDetail = self::create($epigraph, $incomeTaxAmount);
        $incomeTaxDetail->withCollectionAndPaymentCriteria = $withCollectionAndPaymentCriteria;
        return $incomeTaxDetail;
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'epigraph' => [
                    'type' => 'string',
                    'description' => 'PFEZ jardueraren epigrafe zenbakia - Epígrafe de la actividad a la que está asociado el IRPF'
                ],
                'incomeTaxAmount' => [
                    'type' => 'string',
                    'description' => 'PFEZ sarrera zenbatekoa (2 dezimalekin). Epigrafe bat baino gehiago zehazten bada, derrigorezkoa - Importe del ingreso IRPF (con 2 decimales). Obligatorio si la factura lleva asociado más de un epígrafe'

                ],
                'collectionAndPaymentCriteria' => [
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Faktura Kobrantzen eta ordainketen irizpidera atxikita badago - Si la factura está acogida al criterio de Cobros y Pagos'
                ],
            ],
            'required' => ['epigraph']
        ];
    }

    public function toArray(): array
    {
        return [
            'epigraph' => $this->epigraph,
            'incomeTaxAmount' => (string)$this->incomeTaxAmount,
            'collectionAndPaymentCriteria' => $this->withCollectionAndPaymentCriteria,
        ];
    }
}
