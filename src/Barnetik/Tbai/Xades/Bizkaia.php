<?php

namespace Barnetik\Tbai\Xades;

use lyquidity\xmldsig\XMLSecurityDSig;

class Bizkaia extends TicketBai
{
    const POLICY_IDENTIFIER = 'https://www.batuz.eus/fitxategiak/batuz/ticketbai/sinadura_elektronikoaren_zehaztapenak_especificaciones_de_la_firma_electronica_v1_0.pdf';
    const POLICY_DIGEST = 'Quzn98x3PMbSHwbUzaj5f5KOpiH0u8bvmwbbbNkO9Es=';
    // const POLICY_DOCUMENT_URL = 'https://www.batuz.eus/fitxategiak/batuz/ticketbai/sinadura_elektronikoaren_zehaztapenak_especificaciones_de_la_firma_electronica_v1_0.pdf.';
    const ALGORITHM = XMLSecurityDSig::SHA256;
}
