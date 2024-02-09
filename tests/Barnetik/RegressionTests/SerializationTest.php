<?php

namespace Test\Barnetik\RegressionTests;

use Barnetik\Tbai\TicketBai;
use Test\Barnetik\TestCase;

class SerializationTest extends TestCase
{
    public function test_gh19_serialization_returns_correct_selfEmployed(): void
    {
        $json = json_decode($this->getFilesContents('tbai-sample.json'), true);
        $json['self_employed'] = true;
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $ticketbaiArray = $ticketbai->toArray();
        $this->assertArrayHasKey('selfEmployed', $ticketbaiArray);
        $this->assertEquals(true, $ticketbaiArray['selfEmployed']);

        $json = json_decode($this->getFilesContents('tbai-sample.json'), true);
        $json['self_employed'] = false;
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $ticketbaiArray = $ticketbai->toArray();
        $this->assertArrayHasKey('selfEmployed', $ticketbaiArray);
        $this->assertEquals(false, $ticketbaiArray['selfEmployed']);

        $json = json_decode($this->getFilesContents('tbai-sample.json'), true);
        $json['selfEmployed'] = true;
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $ticketbaiArray = $ticketbai->toArray();
        $this->assertArrayHasKey('selfEmployed', $ticketbaiArray);
        $this->assertEquals(true, $ticketbaiArray['selfEmployed']);

        $json = json_decode($this->getFilesContents('tbai-sample.json'), true);
        $json['selfEmployed'] = false; //This has priority over self_employed
        $json['self_employed'] = true;
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $ticketbaiArray = $ticketbai->toArray();
        $this->assertArrayHasKey('selfEmployed', $ticketbaiArray);
        $this->assertEquals(false, $ticketbaiArray['selfEmployed']);
    }

    public function test_gh19_serialization_batuzIncomeTaxes_is_correctly_handled(): void
    {
        $json = json_decode($this->getFilesContents('tbai-sample-self-employed.json'), true);
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $ticketbaiArray = $ticketbai->toArray();
        $this->assertArrayHasKey('selfEmployed', $ticketbaiArray);
        $this->assertEquals(true, $ticketbaiArray['selfEmployed']);

        $json = json_decode($this->getFilesContents('tbai-sample-self-employed.json'), true);
        unset($json['batuzIncomeTaxes']);
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $ticketbaiArray = $ticketbai->toArray();
        $this->assertArrayHasKey('batuzIncomeTaxes', $ticketbaiArray);
        $this->assertEmpty($ticketbaiArray['batuzIncomeTaxes']);

        $json = json_decode($this->getFilesContents('tbai-sample.json'), true);
        $json['batuzIncomeTaxes'] = [];
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $ticketbaiArray = $ticketbai->toArray();
        $this->assertArrayHasKey('batuzIncomeTaxes', $ticketbaiArray);
        $this->assertEmpty($ticketbaiArray['batuzIncomeTaxes']);

        $json = json_decode($this->getFilesContents('tbai-sample-self-employed.json'), true);
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $ticketbaiArray = $ticketbai->toArray();
        $this->assertArrayHasKey('batuzIncomeTaxes', $ticketbaiArray);
        $this->assertEquals("197330", $ticketbaiArray['batuzIncomeTaxes']['incomeTaxDetails'][0]['epigraph']);
    }
}
