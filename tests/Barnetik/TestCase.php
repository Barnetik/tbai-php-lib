<?php

namespace Test\Barnetik;

use PHPUnit\Framework\TestCase as FrameworkTestCase;
use Test\Barnetik\Tbai\Mother\TicketBaiMother;

class TestCase extends FrameworkTestCase
{
    protected TicketBaiMother $ticketBaiMother;

    protected function setUp(): void
    {
        $this->ticketBaiMother = new TicketBaiMother;
    }

    protected function getFilesContents(string $filename): string
    {
        return file_get_contents(__DIR__ . '/Tbai/__files/' . $filename);
    }
}
