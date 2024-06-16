<?php

namespace Barnetik\Tbai\Invoice\Breakdown;

use Barnetik\Tbai\ValueObject\Amount;
use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMDocument;
use DOMNode;
use DOMXPath;
use InvalidArgumentException;

class AbstractNotSubjectBreakdownItem implements TbaiXml
{
    const NOT_SUBJECT_REASON_RL = 'RL';
    const NOT_SUBJECT_REASON_LOCATION_RULES = 'RL';
    const NOT_SUBJECT_REASON_OTHER = 'OT';
    const NOT_SUBJECT_REASON_OT = 'OT';
    const NOT_SUBJECT_REASON_VT = 'VT';
    const NOT_SUBJECT_REASON_IE = 'IE';

    private string $notSubjectReason;
    private Amount $amount;

    final public function __construct(Amount $amount, string $reason)
    {
        $this->amount = $amount;
        $this->setNotSubjectReason($reason);
    }

    private static function validNotSubjectReasons(): array
    {
        return [
            static::NOT_SUBJECT_REASON_RL,
            static::NOT_SUBJECT_REASON_OT,
            static::NOT_SUBJECT_REASON_VT,
            static::NOT_SUBJECT_REASON_IE,
        ];
    }

    /**
     * @return static
     */
    private function setNotSubjectReason(string $reason)
    {
        if (!in_array($reason, self::validNotSubjectReasons())) {
            throw new InvalidArgumentException('Subject reason is not valid');
        }
        $this->notSubjectReason = $reason;

        return $this;
    }

    public function amount(): string
    {
        return $this->amount;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $notSubjectDetail = $domDocument->createElement('DetalleNoSujeta');

        $notSubjectDetail->appendChild($domDocument->createElement('Causa', $this->notSubjectReason));
        $notSubjectDetail->appendChild($domDocument->createElement('Importe', $this->amount));

        return $notSubjectDetail;
    }

    /**
     * @return static
     */
    public static function createFromXml(DOMXPath $xpath, DOMNode $contextNode)
    {
        $amount = new Amount($xpath->evaluate('string(Importe)', $contextNode));
        $reason = $xpath->evaluate('string(Causa)', $contextNode);

        return new static($amount, $reason);
    }

    /**
     * @return static
     */
    public static function createFromJson(array $jsonData)
    {
        $amount = new Amount($jsonData['amount']);
        $reason = $jsonData['reason'];
        return new static($amount, $reason);
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'amount' => [
                    'type' => 'string',
                    'description' => 'Zenbatekoa (2 dezimalekin) - Importe (2 decimales)'
                ],
                'reason' => [
                    'type' => 'string',
                    'enum' => self::validNotSubjectReasons(),
                    'description' => '
Kargapean ez egoteko arrazoia - Causa no sujeción:
  * RL: Kargapean ez kokapen arauak direla eta - No sujeto por reglas de localización
  * OT: Kargapean ez 7., 14. art, Beste batzuk - No sujeto art. 7, 14, Otros
  * VT: Ez subjektua, hirugarrenen kontura egindako salmentak (BEZaren eta PFEZaren ondorioetarako konputagarria ez den zenbatekoa) - No sujeto, ventas realizadas por cuenta de terceros (importe no computable a efectos de IVA ni de IRPF)
  * IE: Lokalizazio-arauak direla-eta ZALen subjektua ez dena, baina atzerriko zerga, IPS/IGIC edo EBko beste estatu kide bateko BEZa eragiten duena - No sujeto en el TAI por reglas de localización, pero repercute impuesto extranjero, IPS/IGIC o IVA de otro estado miembro UE
'
                ],
            ],
            'required' => ['amount', 'reason']
        ];
    }

    public function toArray(): array
    {
        return [
            'amount' => (string)$this->amount,
            'reason' => $this->notSubjectReason,
        ];
    }
}
