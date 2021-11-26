<?php
include ('./vendor/autoload.php');
use Barnetik\Tbai\TicketBai;

echo json_encode(TicketBai::docJson(), JSON_PRETTY_PRINT);