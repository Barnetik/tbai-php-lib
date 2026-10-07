<?php

namespace Barnetik\Tbai\Xades;

use lyquidity\xmldsig\XMLSecurityDSig;

class Gipuzkoa extends TicketBai
{
    const POLICY_IDENTIFIER = 'https://www.gipuzkoa.eus/ticketbai/sinadura';
    const POLICY_DIGEST = '4LDJbY5hqHHHX858s9QV1P8yVGzo6H23P/iNRRv+PnQ=';
    const ALGORITHM = XMLSecurityDSig::SHA256;
}
