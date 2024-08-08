<?php

namespace Barnetik\Tbai;

use DOMNode;
use Barnetik\Tbai\Interfaces\Stringable;
use DOMDocument;
use JsonSerializable;
use lyquidity\xmldsig\XAdESException;
use SimpleXMLElement;
use lyquidity\xmldsig\XAdES;
use lyquidity\xmldsig\ResourceInfo;
use Barnetik\Tbai\Interfaces\TbaiXml;
use lyquidity\xmldsig\KeyResourceInfo;
use lyquidity\xmldsig\InputResourceInfo;
use Barnetik\Tbai\Xades\Araba as XadesAraba;
use lyquidity\xmldsig\CertificateResourceInfo;
use Barnetik\Tbai\Xades\Bizkaia as XadesBizkaia;
use Barnetik\Tbai\Xades\Gipuzkoa as XadesGipuzkoa;
use Barnetik\Tbai\Exception\InvalidTerritoryException;
use Barnetik\Tbai\Interfaces\TbaiSignable;
use Barnetik\Tbai\ValueObject\Date;
use Barnetik\Tbai\ValueObject\VatId;
use PBurggraf\CRC\CRC8\CRC8;

abstract class AbstractTicketBai implements TbaiXml, TbaiSignable, Stringable, JsonSerializable
{
    const TERRITORY_ARABA = '01';
    const TERRITORY_BIZKAIA = '02';
    const TERRITORY_GIPUZKOA = '03';

    protected string $territory;
    private ?string $signedXmlPath = null;

    public function __construct(string $territory)
    {
        if (!in_array($territory, self::validTerritories())) {
            throw new InvalidTerritoryException();
        }
        $this->territory = $territory;
    }

    abstract public function xml(DOMDocument $document): DOMNode;
    abstract public function toArray(): array;
    abstract public function issuerVatId(): VatId;
    abstract public function expeditionDate(): Date;

    protected static function validTerritories(): array
    {
        return [
            self::TERRITORY_ARABA,
            self::TERRITORY_BIZKAIA,
            self::TERRITORY_GIPUZKOA,
        ];
    }

    public function territory(): string
    {
        return $this->territory;
    }

    public function sign(PrivateKey $privateKey, string $password, string $signedFileStoragePath): void
    {
        if (!$this->isSigned()) {
            if ($privateKey->type() === PrivateKey::TYPE_P12) {
                openssl_pkcs12_read(
                    file_get_contents($privateKey->keyPath()),
                    $certData,
                    $password
                );
            } else {
                $certData['cert'] = file_get_contents($privateKey->certPath());
                $certData['pkey'] = openssl_get_privatekey(
                    file_get_contents($privateKey->keyPath()),
                    $password
                );
            }

            $xadesClass = $this->getXadesClassForTerritory();

            if (!file_exists(dirname($signedFileStoragePath))) {
                mkdir(dirname($signedFileStoragePath), 0777, true);
            }

            call_user_func(
                $xadesClass . '::signDocument',
                new InputResourceInfo(
                    /** @phpstan-ignore-next-line */
                    $this->dom(),
                    ResourceInfo::xmlDocument, // The source is a DOMDocument
                    dirname($signedFileStoragePath), // The location to save the signed document
                    basename($signedFileStoragePath), // The name of the file to save the signed document in,
                    null,
                    false // Enveloped signature
                ),
                new CertificateResourceInfo($certData['cert'], ResourceInfo::string | ResourceInfo::pem),
                new KeyResourceInfo($certData['pkey'], ResourceInfo::string | ResourceInfo::pem)
            );
            $this->signedXmlPath = $signedFileStoragePath;
        }
    }

    public function verifySignature(string $xml, string $signedFileStoragePath = null): bool
    {
        if (!$signedFileStoragePath) {
            $signedFileStoragePath = tempnam(sys_get_temp_dir(), 'signed-xml');
        }

        file_put_contents($signedFileStoragePath, $xml);

        try {
            ob_start();
            XAdES::verifyDocument($signedFileStoragePath);
            ob_end_clean();
            $this->signedXmlPath = $signedFileStoragePath;
        } catch (XAdESException $exception) {
            unlink($signedFileStoragePath);
            return false;
        }

        return true;
    }

    public function moveSignedXmlTo(string $newPath): void
    {
        rename($this->signedXmlPath, $newPath);
        $this->signedXmlPath = $newPath;
    }

    private function getXadesClassForTerritory(): string
    {
        switch ($this->territory) {
            case self::TERRITORY_ARABA:
                return XadesAraba::class;
            case self::TERRITORY_GIPUZKOA:
                return XadesGipuzkoa::class;
            case self::TERRITORY_BIZKAIA:
                return XadesBizkaia::class;
            default:
        }
        throw new InvalidTerritoryException();
    }

    public function base64Signed(): string
    {
        return base64_encode(file_get_contents($this->signedXmlPath()));
    }

    public function signatureValue(): string
    {
        $simpleXml = new SimpleXMLElement(file_get_contents($this->signedXmlPath));
        $namespaces = $simpleXml->getNamespaces(true);
        $ds = $simpleXml->children($namespaces['ds']);
        return (string)$ds->Signature->SignatureValue;
    }

    public function chainSignatureValue(): string
    {
        return substr($this->signatureValue(), 0, 100);
    }

    public function shortSignatureValue(): string
    {
        return substr($this->signatureValue(), 0, 13);
    }

    public function signedXmlPath(): string
    {
        return $this->signedXmlPath;
    }

    public function signed(): string
    {
        return file_get_contents($this->signedXmlPath);
    }

    public function isSigned(): bool
    {
        return (bool)$this->signedXmlPath;
    }

    public function dom(): DomDocument
    {
        $xml = new DOMDocument('1.0', 'utf-8');
        $domNode = $this->xml($xml);
        $xml->appendChild($domNode);
        return $xml;
    }

    public function __toString(): string
    {
        return $this->dom()->saveXml();
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }


    public function setSignedXmlPath(string $path): void
    {
        $this->signedXmlPath = $path;
    }

    public function ticketbaiIdentifier(): string
    {
        $code = sprintf(
            'TBAI-%s-%s-%s-',
            $this->issuerVatId(),
            $this->expeditionDate()->short(),
            $this->shortSignatureValue()
        );

        return $code . $this->crc8($code);
    }

    private function crc8(string $data): string
    {
        $crc8 = new CRC8();
        return str_pad(
            (string)$crc8->calculate($data),
            3,
            '0',
            STR_PAD_LEFT
        );
    }
}
