<?php

namespace Barnetik\Tbai\Xades;

use lyquidity\xmldsig\XMLSecurityDSig;

class Bizkaia extends TicketBai
{
    const POLICY_IDENTIFIER = 'https://www.batuz.eus/fitxategiak/batuz/ticketbai/sinadura_elektronikoaren_zehaztapenak_especificaciones_de_la_firma_electronica_v1_1.pdf';
    const POLICY_DIGEST = 'K2baIY0fk8jbkPHkffk5F5C46O5VuzDwH21dAovjVRs=';
    const ALGORITHM = XMLSecurityDSig::SHA256;
}
