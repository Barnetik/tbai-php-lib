<?php

namespace Barnetik\Tbai\Xades;

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

class TicketBai extends XAdES
{
    const POLICY_IDENTIFIER = 'https://www.batuz.eus/fitxategiak/batuz/ticketbai/sinadura_elektronikoaren_zehaztapenak_especificaciones_de_la_firma_electronica_v1_0.pdf';
    const POLICY_DIGEST = 'Quzn98x3PMbSHwbUzaj5f5KOpiH0u8bvmwbbbNkO9Es=';
    // const POLICY_DOCUMENT_URL = 'https://www.batuz.eus/fitxategiak/batuz/ticketbai/sinadura_elektronikoaren_zehaztapenak_especificaciones_de_la_firma_electronica_v1_0.pdf.';
    const ALGORITHM = XMLSecurityDSig::SHA256;

    protected function getSignaturePolicyIdentifier()
    {
        $spi = new SignaturePolicyIdentifier(
            new SignaturePolicyId(
                new SigPolicyId(self::POLICY_IDENTIFIER),
                null,
                new SigPolicyHash(new DigestMethod(self::ALGORITHM), new DigestValue(self::POLICY_DIGEST)),
                new SigPolicyQualifiers(new SigPolicyQualifier(self::POLICY_IDENTIFIER))
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
                new ObjectIdentifier('urn:oid:1.2.840.10003.5.109.10'), // ObjectIdentifier
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
}
