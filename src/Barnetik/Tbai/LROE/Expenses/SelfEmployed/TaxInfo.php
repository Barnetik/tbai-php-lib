<?php

namespace Barnetik\Tbai\LROE\Expenses\SelfEmployed;

use Barnetik\Tbai\LROE\Expenses\Shared\AbstractTaxInfo;
use DOMNode;
use DOMDocument;
use Barnetik\Tbai\ValueObject\Amount;
use InvalidArgumentException;

class TaxInfo extends AbstractTaxInfo
{
    const IRPF_VAT_GOOD_TYPE_IRPF_AND_VAT = 'I';
    const IRPF_VAT_GOOD_TYPE_IRPF_ONLY = 'R';
    const IRPF_VAT_GOOD_TYPE_NONE = 'N';

    const CONCEPT_TYPE_600 = '600';
    const CONCEPT_TYPE_601 = '601';
    const CONCEPT_TYPE_602 = '602';
    const CONCEPT_TYPE_606 = '606';
    const CONCEPT_TYPE_607 = '607';
    const CONCEPT_TYPE_608 = '608';
    const CONCEPT_TYPE_609 = '609';
    const CONCEPT_TYPE_620 = '620';
    const CONCEPT_TYPE_621 = '621';
    const CONCEPT_TYPE_622 = '622';
    const CONCEPT_TYPE_623 = '623';
    const CONCEPT_TYPE_624 = '624';
    const CONCEPT_TYPE_625 = '625';
    const CONCEPT_TYPE_626 = '626';
    const CONCEPT_TYPE_627 = '627';
    const CONCEPT_TYPE_628 = '628';
    const CONCEPT_TYPE_629 = '629';
    const CONCEPT_TYPE_631 = '631';
    const CONCEPT_TYPE_634 = '634';
    const CONCEPT_TYPE_639 = '639';
    const CONCEPT_TYPE_640 = '640';
    const CONCEPT_TYPE_641 = '641';
    const CONCEPT_TYPE_64201 = '64201';
    const CONCEPT_TYPE_64202 = '64202';
    const CONCEPT_TYPE_643 = '643';
    const CONCEPT_TYPE_644 = '644';
    const CONCEPT_TYPE_649 = '649';
    const CONCEPT_TYPE_65 = '65';
    const CONCEPT_TYPE_66 = '66';
    const CONCEPT_TYPE_67 = '67';
    const CONCEPT_TYPE_680 = '680';
    const CONCEPT_TYPE_681 = '681';
    const CONCEPT_TYPE_682 = '682';
    const CONCEPT_TYPE_69 = '69';

    const SE_OPERATION_EQUIVALENCE = 'E';
    const SE_OPERATION_SIMPLIFIED = 'S';
    const SE_OPERATION_NONE = 'N';

    private string $epigraph;
    private ?string $irpfOrVatAffectedGoodType = null;
    private ?string $conceptType = null;
    private ?string $reference = null;
    private bool $taxablePersonReversal;
    private ?string $simplifiedOrEquivalenceSurchargeOperation = null;
    private Amount $taxBase;
    private ?Amount $taxRate = null;
    private ?Amount $supportedTaxQuota = null;
    private ?Amount $deductibleTaxQuota = null;
    private ?Amount $equivalenceRate = null;
    private ?Amount $equivalenceQuota = null;
    private ?Amount $reagypCompensationPercent = null;
    private ?Amount $reagypCompensationAmount = null;
    private ?Amount $irpfExpensesAmount = null;
    private ?bool $withCollectionAndPaymentCriteria = null;

    private function __construct(string $epigraph, Amount $taxBase, bool $taxablePersonReversal)
    {
        $this->epigraph = $epigraph;
        $this->taxBase = $taxBase;
        $this->taxablePersonReversal = $taxablePersonReversal;
    }

    private static function validIrpfOrVatGoodTypeValues(): array
    {
        return [
            self::IRPF_VAT_GOOD_TYPE_IRPF_AND_VAT,
            self::IRPF_VAT_GOOD_TYPE_IRPF_ONLY,
            self::IRPF_VAT_GOOD_TYPE_NONE
        ];
    }

    private static function validConceptTypeValues(): array
    {
        return [
            self::CONCEPT_TYPE_600,
            self::CONCEPT_TYPE_601,
            self::CONCEPT_TYPE_602,
            self::CONCEPT_TYPE_606,
            self::CONCEPT_TYPE_607,
            self::CONCEPT_TYPE_608,
            self::CONCEPT_TYPE_609,
            self::CONCEPT_TYPE_620,
            self::CONCEPT_TYPE_621,
            self::CONCEPT_TYPE_622,
            self::CONCEPT_TYPE_623,
            self::CONCEPT_TYPE_624,
            self::CONCEPT_TYPE_625,
            self::CONCEPT_TYPE_626,
            self::CONCEPT_TYPE_627,
            self::CONCEPT_TYPE_628,
            self::CONCEPT_TYPE_629,
            self::CONCEPT_TYPE_631,
            self::CONCEPT_TYPE_634,
            self::CONCEPT_TYPE_639,
            self::CONCEPT_TYPE_640,
            self::CONCEPT_TYPE_641,
            self::CONCEPT_TYPE_64201,
            self::CONCEPT_TYPE_64202,
            self::CONCEPT_TYPE_643,
            self::CONCEPT_TYPE_644,
            self::CONCEPT_TYPE_649,
            self::CONCEPT_TYPE_65,
            self::CONCEPT_TYPE_66,
            self::CONCEPT_TYPE_67,
            self::CONCEPT_TYPE_680,
            self::CONCEPT_TYPE_681,
            self::CONCEPT_TYPE_682,
            self::CONCEPT_TYPE_69,
        ];
    }

    private static function validSimplifiedOrEquivalenceOperationValues(): array
    {
        return [
            self::SE_OPERATION_EQUIVALENCE,
            self::SE_OPERATION_SIMPLIFIED,
            self::SE_OPERATION_NONE,
        ];
    }


    public function xml(DOMDocument $domDocument): DOMNode
    {
        $taxInfo = $domDocument->createElement('DetalleRentaIVA');

        $taxInfo->appendChild($domDocument->createElement('Epigrafe', $this->epigraph));

        $this->appendOptionalXml($taxInfo, $domDocument->createElement('BienAfectoIRPFYOIVA'), $this->irpfOrVatAffectedGoodType);
        $this->appendOptionalXml($taxInfo, $domDocument->createElement('Concepto'), $this->conceptType);
        $this->appendOptionalXml($taxInfo, $domDocument->createElement('ReferenciaBien'), $this->reference);

        $taxInfo->appendChild($domDocument->createElement('InversionSujetoPasivo', $this->taxablePersonReversal ? 'S' : 'N'));

        $this->appendOptionalXml($taxInfo, $domDocument->createElement('OperacionEnRecargoDeEquivalenciaORegimenSimplificado'), $this->simplifiedOrEquivalenceSurchargeOperation);

        $taxInfo->appendChild($domDocument->createElement('BaseImponible', (string)$this->taxBase));

        $this->appendOptionalXml($taxInfo, $domDocument->createElement('TipoImpositivo'), $this->taxRate);
        $this->appendOptionalXml($taxInfo, $domDocument->createElement('CuotaIVASoportada'), $this->supportedTaxQuota);
        $this->appendOptionalXml($taxInfo, $domDocument->createElement('CuotaIVADeducible'), $this->deductibleTaxQuota);
        $this->appendOptionalXml($taxInfo, $domDocument->createElement('TipoRecargoEquivalencia'), $this->equivalenceRate);
        $this->appendOptionalXml($taxInfo, $domDocument->createElement('CuotaRecargoEquivalencia'), $this->equivalenceQuota);
        $this->appendOptionalXml($taxInfo, $domDocument->createElement('PorcentajeCompensacionREAGYP'), $this->reagypCompensationPercent);
        $this->appendOptionalXml($taxInfo, $domDocument->createElement('ImporteCompensacionREAGYP'), $this->reagypCompensationAmount);
        $this->appendOptionalXml($taxInfo, $domDocument->createElement('ImporteGastoIRPF'), $this->irpfExpensesAmount);
        $this->appendOptionalXml($taxInfo, $domDocument->createElement('CriterioCobrosYPagos'), $this->withCollectionAndPaymentCriteria, self::TYPE_ENUM_SI_NO);

        return $taxInfo;
    }

    private function setIrpfOrVatAffectedGoodType(string $irpfOrVatAffectedGoodType): self
    {
        if (!in_array($irpfOrVatAffectedGoodType, self::validIrpfOrVatGoodTypeValues())) {
            throw new InvalidArgumentException('Wrong irpfOrVatAffectedGoodType value');
        }
        $this->irpfOrVatAffectedGoodType = $irpfOrVatAffectedGoodType;

        return $this;
    }

    private function setConceptType(string $conceptType): self
    {
        if (!in_array($conceptType, self::validConceptTypeValues())) {
            throw new InvalidArgumentException('Wrong conceptType value');
        }
        $this->conceptType = $conceptType;

        return $this;
    }

    private function setSimplifiedOrEquivalenceSurchargeOperation(string $simplifiedOrEquivalenceSurchargeOperation): self
    {
        if (!in_array($simplifiedOrEquivalenceSurchargeOperation, self::validSimplifiedOrEquivalenceOperationValues())) {
            throw new InvalidArgumentException('Wrong simplifiedOrEquivalenceSurchargeOperation value');
        }
        $this->simplifiedOrEquivalenceSurchargeOperation = $simplifiedOrEquivalenceSurchargeOperation;

        return $this;
    }

    public static function createFromJson(array $jsonData): self
    {
        $taxInfo = new self($jsonData['epigraph'], new Amount($jsonData['taxBase']), (bool)($jsonData['taxablePersonReversal'] ?? false));

        if (isset($jsonData['irpfOrVatAffectedGoodType'])) {
            $taxInfo->setIrpfOrVatAffectedGoodType($jsonData['irpfOrVatAffectedGoodType']);
        }

        if (isset($jsonData['conceptType'])) {
            $taxInfo->setConceptType($jsonData['conceptType']);
        }

        if (isset($jsonData['reference'])) {
            $taxInfo->reference = (string)$jsonData['reference'];
        }

        if (isset($jsonData['simplifiedOrEquivalenceSurchargeOperation'])) {
            $taxInfo->setSimplifiedOrEquivalenceSurchargeOperation($jsonData['simplifiedOrEquivalenceSurchargeOperation']);
        }

        if (isset($jsonData['taxRate'])) {
            $taxInfo->taxRate = new Amount($jsonData['taxRate'], 3, 2);
        }

        if (isset($jsonData['supportedTaxQuota'])) {
            $taxInfo->supportedTaxQuota = new Amount($jsonData['supportedTaxQuota']);
        }

        if (isset($jsonData['deductibleTaxQuota'])) {
            $taxInfo->deductibleTaxQuota = new Amount($jsonData['deductibleTaxQuota']);
        }

        if (isset($jsonData['equivalenceRate'])) {
            $taxInfo->equivalenceRate = new Amount($jsonData['equivalenceRate'], 3, 2);
        }

        if (isset($jsonData['equivalenceQuota'])) {
            $taxInfo->equivalenceQuota = new Amount($jsonData['equivalenceQuota']);
        }

        if (isset($jsonData['reagypCompensationPercent'])) {
            $taxInfo->reagypCompensationPercent = new Amount($jsonData['reagypCompensationPercent'], 3, 2);
        }

        if (isset($jsonData['reagypCompensationAmount'])) {
            $taxInfo->reagypCompensationAmount = new Amount($jsonData['reagypCompensationAmount']);
        }

        if (isset($jsonData['irpfExpensesAmount'])) {
            $taxInfo->irpfExpensesAmount = new Amount($jsonData['irpfExpensesAmount']);
        }

        if (isset($jsonData['withCollectionAndPaymentCriteria'])) {
            $taxInfo->withCollectionAndPaymentCriteria = (bool) $jsonData['withCollectionAndPaymentCriteria'];
        }

        return $taxInfo;
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'epigraph' => [
                    'type' => 'string',
                    'maxLength' => 7,
                    'description' => 'Epígrafe'
                ],
                'irpfOrVatAffectedGoodType' => [
                    'type' => 'string',
                    'enum' => self::validIrpfOrVatGoodTypeValues(),
                    'description' => '
Tipo de afección de IVA y/o IRPF del bien:
  * I: Adquisición de un bien de inversión a efectos del IVA y afecto a la actividad en el IRPF
  * R: Adquisición de un bien afecto a la actividad en el IRPF, pero que no es bien de inversión a efectos del IVA
  * N: Adquisición de un bien que no se considera ni bien de inversión a efectos del IVA ni bien afecto a la actividad en el IRPF
                    '
                ],
                'conceptType' => [
                    'type' => 'string',
                    'enum' => self::validConceptTypeValues(),
                    'description' => '
Concepto de los bienes adquiridos
  * 600: Compras de mercaderías
  * 601: Compras de materias primas
  * 602: Compras de otros aprovisionamientos
  * 606: Descuentos sobre compras por pronto pago
  * 607: Trabajos realizados por otras empresas
  * 608: Devoluciones de compras y operaciones similares
  * 609: Rappels por compras
  * 620: Gastos en investigación y desarrollo del ejercicio
  * 621: Arrendamientos y cánones
  * 622: Reparaciones y conservación
  * 623: Servicios de profesionales independientes
  * 624: Transportes
  * 625: Primas de seguros
  * 626: Servicios bancarios y similares
  * 627: Publicidad, propaganda y relaciones públicas
  * 628: Suministros
  * 629: Otros servicios
  * 631: Otros servicios
  * 634: Ajustes negativos en la imposición indirecta
  * 639: Ajustes positivos en la imposición indirecta
  * 640: Sueldos y salarios
  * 641: Indemnizaciones
  * 64201: Seguridad social a cargo de la empresa: autónomos
  * 64202: Seguridad social a cargo de la empresa: empleados
  * 643: Retribuciones a largo plazo mediante sistemas de aportación definida
  * 644: Retribuciones a largo plazo mediante sistemas de prestación definida
  * 649: Otros gastos sociales
  * 65: Otros gastos de gestión
  * 66: Gastos financieros
  * 67: Gastos excepcionales
  * 680: Amortización del inmovilizado intangible
  * 681: Amortización del inmovilizado material
  * 682: Amortización de las inversiones inmobiliarias
  * 69: Pérdidas por deterioro y otras dotaciones
                '
                ],
                'reference' => [
                    'type' => 'string',
                    'maxLength' => 10,
                    'description' => 'Referencia del bien'
                ],
                'taxablePersonReversal' => [
                    'type' => 'boolean',
                    'description' => 'Inversión del sujeto pasivo',
                    'default' => false
                ],
                'simplifiedOrEquivalenceSurchargeOperation' => [
                    'type' => 'string',
                    'enum' => self::validSimplifiedOrEquivalenceOperationValues(),
                    'description' => '
Operación en recargo de equivalencia o regimen simplificado:
    * E: Operación en recargo de equivalencia
    * S: Operación en régimen simplificado
    * N: Operación que no está ni en recargo de equivalencia ni en régimen simplificado
                    '
                ],
                'taxBase' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Base imponible (2 decimales)'
                ],
                'taxRate' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,3}(\.\d{0,2})?$',
                    'description' => 'Tipo impositivo (2 decimales)'
                ],
                'supportedTaxQuota' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Cuota IVA Soportada (2 decimales)'
                ],
                'deductibleTaxQuota' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Cuota IVA Deducible (2 decimales)'
                ],
                'equivalenceRate' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,3}(\.\d{0,2})?$',
                    'description' => 'Tipo de recargo de equivalencia (2 decimales)'
                ],
                'equivalenceQuota' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Cuota de recargo de equivalencia (2 decimales)'
                ],
                'reagypCompensationPercent' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,3}(\.\d{0,2})?$',
                    'description' => 'Porcentaje compensación REAGYP (2 decimales)'
                ],
                'reagypCompensationAmount' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Importe de compensación REAGYP (2 decimales)'
                ],
                'irpfExpensesAmount' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Importe de gasto IRPF (2 decimales)'
                ],
                'withCollectionAndPaymentCriteria' => [
                    'type' => 'boolean',
                    'description' => 'Criterio de Cobros y Pagos'
                ],
            ],
            'required' => ['epigraph', 'taxBase']
        ];
    }

    public function toArray(): array
    {
        return [
            'epigraph' => $this->epigraph,
            'taxBase' => (string)$this->taxBase,
            'irpfOrVatAffectedGoodType' => $this->irpfOrVatAffectedGoodType,
            'conceptType' => $this->conceptType,
            'reference' => $this->reference,
            'taxablePersonReversal' => $this->taxablePersonReversal,
            'simplifiedOrEquivalenceSurchargeOperation' => $this->simplifiedOrEquivalenceSurchargeOperation,
            'taxRate' => $this->taxRate ? (string) $this->taxRate : null,
            'supportedTaxQuota' => $this->supportedTaxQuota ? (string) $this->supportedTaxQuota : null,
            'deductibleTaxQuota' => $this->deductibleTaxQuota ? (string) $this->deductibleTaxQuota : null,
            'equivalenceRate' => $this->equivalenceRate ? (string) $this->equivalenceRate : null,
            'equivalenceQuota' => $this->equivalenceQuota ? (string) $this->equivalenceQuota : null,
            'reagypCompensationPercent' => $this->reagypCompensationPercent ? (string) $this->reagypCompensationPercent : null,
            'reagypCompensationAmount' => $this->reagypCompensationAmount ? (string) $this->reagypCompensationAmount : null,
            'irpfExpensesAmount' => $this->irpfExpensesAmount ? (string) $this->irpfExpensesAmount : null,
            'withCollectionAndPaymentCriteria' => $this->withCollectionAndPaymentCriteria,
        ];
    }
}
