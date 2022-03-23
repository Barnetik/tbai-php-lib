<?php

namespace Barnetik\Tbai\Xades\xmldsig\xml;

use lyquidity\xmldsig\xml\CertDigest;
use lyquidity\xmldsig\xml\CertV2;
use lyquidity\xmldsig\xml\DigestMethod;
use lyquidity\xmldsig\xml\DigestValue;
use lyquidity\xmldsig\xml\SigningCertificateV2 as XmlSigningCertificateV2;
use lyquidity\OCSP\Ocsp;

class SigningCertificateV2 extends XmlSigningCertificateV2
{
    public static function fromCertificate($certificate, $issuer = null, $algorithm = self::defaultAlgorithm, $uri = null)
    {
        // Add the digest
        $digest = base64_encode(hash($algorithm, (new \lyquidity\Asn1\Der\Encoder())->encodeElement($certificate), true));

        list($certificate, $certificateInfo, $ocspResponderUrl, $issuerCertBytes, $issuerCertificate) = array_values(Ocsp::getCertificate($certificate, $issuer));

        $reflection = new \ReflectionClass('\lyquidity\xmldsig\XMLSecurityDSig');
        $algorithm = $reflection->getConstant(strtoupper($algorithm));

        return new SigningCertificateV2(
            new CertV2(
                new CertDigest(
                    new DigestMethod($algorithm),
                    new DigestValue($digest)
                ),
                null,
                $uri
            ),
        );
    }
}
