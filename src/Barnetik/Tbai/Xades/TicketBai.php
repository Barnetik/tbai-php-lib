<?php

namespace Barnetik\Tbai\Xades;

use lyquidity\xmldsig\XAdES;
use lyquidity\xmldsig\XAdES_SBR;
use lyquidity\xmldsig\xml\Generic;
use lyquidity\xmldsig\xml\Transforms;
use lyquidity\xmldsig\XMLSecurityDSig;
use lyquidity\xmldsig\xml\SigPolicyHash;
use lyquidity\xmldsig\xml\AttributeNames;
use lyquidity\xmldsig\xml\DigestMethod;
use lyquidity\xmldsig\xml\DigestValue;
use lyquidity\xmldsig\xml\SignaturePolicyId;
use lyquidity\xmldsig\xml\SignaturePolicyIdentifier;
use lyquidity\xmldsig\xml\SigPolicyId;

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
                null
            )
        );

        return $spi;
    }
}
