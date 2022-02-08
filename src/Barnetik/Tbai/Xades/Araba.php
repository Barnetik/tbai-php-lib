<?php

namespace Barnetik\Tbai\Xades;

use lyquidity\xmldsig\XMLSecurityDSig;

class Araba extends TicketBai
{
    const POLICY_IDENTIFIER = 'https://ticketbai.araba.eus/tbai/sinadura/';
    const POLICY_DIGEST = 'd69VEBc4ED4QbwnDtCA2JESgJiw+rwzfutcaSl5gYvM=';
    const ALGORITHM = XMLSecurityDSig::SHA256;
}
