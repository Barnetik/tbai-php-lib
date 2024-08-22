<?php

namespace Barnetik\Tbai\LROE\Expenses\SelfEmployed;

use Barnetik\Tbai\LROE\Expenses\Shared\AbstractTaxInfo;
use DOMNode;
use DOMDocument;
use Barnetik\Tbai\ValueObject\Amount;
use InvalidArgumentException;

class Income extends AbstractTaxInfo
{
    const MIN_LINE = 1;
    const MAX_LINE = 99;

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

    private string $epigraph;
    private string $conceptType;
    private int $line;
    private ?string $goodReference = null;
    private Amount $irpfExpenseAmount;
    private ?bool $withCollectionAndPaymentCriteria = null;

    private function __construct(string $epigraph, string $conceptType, int $line, Amount $irpfExpenseAmount)
    {
        $this->epigraph = $epigraph;
        $this->setConceptType($conceptType);
        $this->setLine($line);
        $this->irpfExpenseAmount = $irpfExpenseAmount;
    }

    private function setConceptType(string $conceptType): self
    {
        if (!in_array($conceptType, self::validConceptTypeValues())) {
            throw new InvalidArgumentException('Wrong conceptType value');
        }
        $this->conceptType = $conceptType;

        return $this;
    }

    private function setLine(int $line): self
    {
        if ($line < self::MIN_LINE || $line > self::MAX_LINE) {
            throw new InvalidArgumentException('Income line number is out of bounds');
        }
        $this->line = $line;

        return $this;
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

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $taxInfo = $domDocument->createElement('Renta');

        $taxInfo->appendChild($domDocument->createElement('Epigrafe', $this->epigraph));
        $taxInfo->appendChild($domDocument->createElement('Concepto', $this->conceptType));
        $taxInfo->appendChild($domDocument->createElement('Linea', (string) $this->line));

        if ($this->goodReference) {
            $taxInfo->appendChild($domDocument->createElement('ReferenciaBien', $this->goodReference));
        }

        $taxInfo->appendChild($domDocument->createElement('ImporteGastoIRPF', $this->irpfExpenseAmount));

        if (!is_null($this->withCollectionAndPaymentCriteria)) {
            $taxInfo->appendChild($domDocument->createElement('CriterioCobrosYPagos', $this->withCollectionAndPaymentCriteria ? 'S' : 'N'));
        }

        return $taxInfo;
    }


    public static function createFromJson(array $jsonData): self
    {
        $income = new self($jsonData['epigraph'], $jsonData['conceptType'], $jsonData['line'], new Amount($jsonData['irpfExpenseAmount']));

        if (isset($jsonData['goodReference'])) {
            $income->goodReference = (string)$jsonData['goodReference'];
        }

        if (isset($jsonData['withCollectionAndPaymentCriteria'])) {
            $income->withCollectionAndPaymentCriteria = (bool) $jsonData['withCollectionAndPaymentCriteria'];
        }

        return $income;
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
                'line' => [
                    'type' => 'integer',
                    'minimum' => self::MIN_LINE,
                    'maximum' => self::MAX_LINE,
                    'description' => 'Número de línea'
                ],
                'goodReference' => [
                    'type' => 'string',
                    'maxLength' => 10,
                    'description' => 'Referencia del bien'
                ],
                'irpfExpenseAmount' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Importe de gasto de IRPF (2 decimales)'
                ],
                'withCollectionAndPaymentCriteria' => [
                    'type' => 'boolean',
                    'description' => 'Criterio de Cobros y Pagos'
                ],
            ],
            'required' => ['epigraph', 'conceptType', 'line', 'irpfExpenseAmount']
        ];
    }

    public function toArray(): array
    {
        return [
            'epigraph' => $this->epigraph,
            'conceptType' => $this->conceptType,
            'goodReference' => $this->goodReference,
            'line' => $this->line,
            'irpfExpenseAmount' => (string) $this->irpfExpenseAmount,
            'withCollectionAndPaymentCriteria' => $this->withCollectionAndPaymentCriteria,
        ];
    }
}
