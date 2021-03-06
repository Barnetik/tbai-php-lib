<?php

namespace Barnetik\Tbai\Xades;

use lyquidity\xmldsig\XMLSecurityDSig;

class Gipuzkoa extends TicketBai
{
    const POLICY_IDENTIFIER = 'https://www.gipuzkoa.eus/ticketbai/sinadura';
    const POLICY_DIGEST = 'vSe1CH7eAFVkGN0X2Y7Nl9XGUoBnziDA5BGUSsyt8mg=';
    const ALGORITHM = XMLSecurityDSig::SHA256;
}
