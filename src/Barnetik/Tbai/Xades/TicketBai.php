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
    const POLICY_IDENTIFIER = 'http://ticketbai.eus/politicafirma';
    const POLICY_DIGEST = 'lX1xDvBVAsPXkkJ7R07WCVbAm9e0H33I1sCpDtQNkbc=';
    const POLICY_DOCUMENT_URL = 'https://www.euskadi.eus/contenidos/informacion/ticketbai/es_14815/adjuntos/TicketBAI_Politica_firma_v_1_0.pdf';
    const ALGORITHM = XMLSecurityDSig::SHA256;

    protected function getSignaturePolicyIdentifier()
    {
        // $policyDoc = $this->getXmlDocument($this->getPolicyDocument());

        // // Load the policy document
        // $sbrPolicy = Generic::fromNode($policyDoc);

        // // Get the transforms
        // /** @var Transforms */
        // $transforms = $sbrPolicy->getObjectFromPath(
        //     array("SignaturePolicy", "Transforms"),
        //     "Unable to locate <Transforms> in the SBR policy document"
        // );

        // $transforms->parent = null;

        // Use the traverse function to set the prefix to null on this an all descendents
        // $transforms->traverse(function ($node) {
        //     $node->prefix = null;
        // });

        // $transforms = new Transforms();

        // Create the policy object
        $spi = new SignaturePolicyIdentifier(
            new SignaturePolicyId(
                new SigPolicyId(self::POLICY_IDENTIFIER),
                null,
                new SigPolicyHash(new DigestMethod(self::ALGORITHM), new DigestValue(self::POLICY_DIGEST)),
                null // No qualifiers
            )
        );

        return $spi;
    }

    //     /**
    //      * Its expected this will be overridden in a descendent class
    //      * @var string $policyIdentifier
    //      * @return string A path or URL to the policy document
    //      */
    //     public function getPolicyDocument($policyIdentifier = null)
    //     {
    //         $this->policyIdentifier = $policyIdentifier ?? self::policyIdentifier;

    //         if ($this->policyIdentifier == self::policyIdentifier)
    //             return self::policyDocumentUrl;
    //         else
    //             throw new \Exception("The policy identifier '$policyIdentifier' is not supported");
    //     }
}
