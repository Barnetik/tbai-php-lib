<?php

namespace Barnetik\Tbai\Xades;

use lyquidity\xmldsig\XMLSecurityDSig;

class Araba extends TicketBai
{
    const POLICY_IDENTIFIER = 'https://ticketbai.araba.eus/tbai/sinadura/';
    const POLICY_DIGEST = '4Vk3uExj7tGn9DyUCPDsV9HRmK6KZfYdRiW3StOjcQA=';
    const ALGORITHM = XMLSecurityDSig::SHA256;
}
