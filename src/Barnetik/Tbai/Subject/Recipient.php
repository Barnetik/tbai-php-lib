<?php

namespace Barnetik\Tbai\Subject;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\VatId;
use DOMDocument;
use DOMNode;
use InvalidArgumentException;

class Recipient implements TbaiXml
{
    protected VatId $vatId;
    protected string $name;
    protected string $countryCode;
    protected ?string $postalCode;
    protected ?string $address = null;

    private function __construct()
    {
    }

    public static function createNationalRecipient(VatId $vatId, string $name, ?string $postalCode = null): self
    {
        $recipient = new self();
        $recipient->vatId = $vatId;
        // $recipient->setVatId(self::VAT_ID_TYPE_IFZ, $vatId);
        $recipient->countryCode = 'ES';
        $recipient->name = $name;
        $recipient->postalCode = $postalCode;
        return $recipient;
    }

    public static function createGenericRecipient(VatId $vatId, string $name, ?string $postalCode = null, string $countryCode = 'ES'): self
    {
        $recipient = new self();
        $recipient->vatId = $vatId;

        $recipient->countryCode = $countryCode;
        $recipient->name = $name;
        $recipient->postalCode = $postalCode;
        return $recipient;
    }

    public function vatIdType(): string
    {
        return $this->vatId->type();
    }

    public function vatId(): VatId
    {
        return $this->vatId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function postalCode(): string
    {
        return (string) $this->postalCode;
    }

    public function countryCode(): string
    {
        return $this->countryCode;
    }

    protected function hasNifAsVatId(): bool
    {
        return $this->vatIdType() === VatId::VAT_ID_TYPE_NIF;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $recipient = $domDocument->createElement('IDDestinatario');
        if ($this->hasNifAsVatId()) {
            $recipient->appendChild(
                $domDocument->createElement('NIF', $this->vatId())
            );
        } else {
            $otherId = $domDocument->createElement('IDOtro');
            $otherId->append(
                $domDocument->createElement('CodigoPais', $this->countryCode()),
                $domDocument->createElement('IDType', $this->vatIdType()),
                $domDocument->createElement('ID', $this->vatId())
            );

            $recipient->appendChild(
                $otherId
            );
        }

        $recipient->appendChild(
            $domDocument->createElement('ApellidosNombreRazonSocial', $this->name)
        );

        if ($this->postalCode()) {
            $recipient->appendChild(
                $domDocument->createElement('CodigoPostal', $this->postalCode())
            );
        }

        if ($this->address) {
            $recipient->appendChild(
                $domDocument->createElement('Direccion', $this->address)
            );
        }
        return $recipient;
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'vatId' => [
                    'type' => 'string',
                ],
                'vatIdType' => [
                    'type' => 'string',
                    'enum' => VatId::validIdTypeValues()
                ],
                'name' => [
                    'type' => 'string',
                    'maxLength' => 20
                ],
                'postalCode' => [
                    'type' => 'string',
                    'maxLength' => 20
                ],
                // 'address' => [
                //     'type' => 'string',
                //     'maxLength': 250

                // ],
                'countryId' => [
                    'type' => 'string',
                    'maxLength' => 20
                ]
            ],
            'required' => ['vatId', 'name']
        ];
    }
}
