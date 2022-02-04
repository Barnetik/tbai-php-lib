<?php

namespace Barnetik\Tbai\Xades;

use Barnetik\Tbai\Xades\xmldsig\xml\SigningCertificateV2;
use lyquidity\xmldsig\XAdES;
use lyquidity\xmldsig\XAdES_SBR;
use lyquidity\xmldsig\xml\Generic;
use lyquidity\xmldsig\xml\Transforms;
use lyquidity\xmldsig\XMLSecurityDSig;
use lyquidity\xmldsig\xml\SigPolicyHash;
use lyquidity\xmldsig\xml\AttributeNames;
use lyquidity\xmldsig\xml\DataObjectFormat;
use lyquidity\xmldsig\xml\DigestMethod;
use lyquidity\xmldsig\xml\DigestValue;
use lyquidity\xmldsig\xml\ObjectIdentifier;
use lyquidity\xmldsig\xml\SignaturePolicyId;
use lyquidity\xmldsig\xml\SignaturePolicyIdentifier;
use lyquidity\xmldsig\xml\SignedDataObjectProperties;
use lyquidity\xmldsig\xml\SigPolicyId;
use lyquidity\xmldsig\xml\SigPolicyQualifier;
use lyquidity\xmldsig\xml\SigPolicyQualifiers;
use lyquidity\OCSP\CertificateLoader;
use lyquidity\xmldsig\xml\QualifyingProperties;
use lyquidity\xmldsig\xml\SignatureProductionPlace;
use lyquidity\xmldsig\xml\SignatureProductionPlaceV2;
use lyquidity\xmldsig\xml\SignedProperties;
use lyquidity\xmldsig\xml\SignedSignatureProperties;
use lyquidity\xmldsig\xml\SignerRole;
use lyquidity\xmldsig\xml\SignerRoleV2;
use lyquidity\xmldsig\xml\SigningCertificateV2 as XmlSigningCertificateV2;
use lyquidity\xmldsig\xml\SigningTime;

class TicketBai extends XAdES
{
    const POLICY_IDENTIFIER = '';
    const POLICY_DIGEST = '';
    // const POLICY_DOCUMENT_URL = 'https://www.batuz.eus/fitxategiak/batuz/ticketbai/sinadura_elektronikoaren_zehaztapenak_especificaciones_de_la_firma_electronica_v1_0.pdf.';
    const ALGORITHM = XMLSecurityDSig::SHA256;

    protected function getSignaturePolicyIdentifier()
    {
        $spi = new SignaturePolicyIdentifier(
            new SignaturePolicyId(
                new SigPolicyId(static::POLICY_IDENTIFIER),
                null,
                new SigPolicyHash(new DigestMethod(static::ALGORITHM), new DigestValue(static::POLICY_DIGEST)),
                new SigPolicyQualifiers(new SigPolicyQualifier(static::POLICY_IDENTIFIER))
            )
        );

        return $spi;
    }

    /**
     * Overridden in a descendent instance to provide a jurisdiction specific data
     * @param string $referenceId The id that will be added to the signed info reference
     * @return SignedDataObjectProperties
     */
    protected function getSignedDataObjectProperties($referenceId = null)
    {
        $sdop = new SignedDataObjectProperties(
            new DataObjectFormat(
                $this->fileBeingSigned->isFile()  // File reference
                    ? basename($this->fileBeingSigned->resource)
                    : ($this->fileBeingSigned->isXmlDocument()
                        ? ($this->fileBeingSigned->resource->baseURI
                            ? $this->fileBeingSigned->resource->baseURI
                            : $this->fileBeingSigned->saveFilename)
                        : ($this->fileBeingSigned->isString()
                            ? $this->fileBeingSigned->saveFilename
                            : $this->fileBeingSigned->resource)),
                null,
                'text/xml', // MimeType
                null, // Encoding
                "#$referenceId"
            ),
            null, // CommitmentTypeIndication
            null, // AllDataObjectsTimeStamp
            null, // IndividualDataObjectsTimeStamp
            null
        );

        return $sdop;
    }

    protected function createQualifyingProperties(
        $signatureId,
        $certificate = null,
        $signatureProductionPlace = null,
        $signerRole = null,
        $signaturePropertiesId = null,
        $referenceId = null,
        $signedPropertiesId = self::SignedPropertiesId
    ) {
        $loader = new CertificateLoader();
        $certs = CertificateLoader::getCertificates($certificate);
        $cert = null;
        $issuer = null;
        if ($certs) {
            $cert = $loader->fromString(reset($certs));
            if (next($certs))
                $issuer = $loader->fromString(current($certs));
        } else {
            $cert = $loader->fromFile($certificate);
        }

        $signingCertificate = SigningCertificateV2::fromCertificate($cert, $issuer);

        $qualifyingProperties = new QualifyingProperties(
            new SignedProperties(
                new SignedSignatureProperties(
                    new SigningTime(),
                    null, // signingCertificate
                    $signingCertificate, /**  @phpstan-ignore-line */
                    $this->getSignaturePolicyIdentifier(),
                    $signatureProductionPlace instanceof SignatureProductionPlace ? $signatureProductionPlace : null,
                    $signatureProductionPlace instanceof SignatureProductionPlaceV2 ? $signatureProductionPlace : null,
                    $signerRole instanceof SignerRole ? $signerRole : null,
                    $signerRole instanceof SignerRoleV2 ? $signerRole : null,
                    $signaturePropertiesId
                ),
                $this->getSignedDataObjectProperties($referenceId),
                $signedPropertiesId
            ),
            null,
            $signatureId
        );

        return $qualifyingProperties;
    }
}
