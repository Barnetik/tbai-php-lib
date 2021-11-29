<?php

include('./vendor/autoload.php');
use Barnetik\Tbai\TicketBai;

echo json_encode([
    'swagger' => '2.0',
    'info' => [
        'description' => 'TicketBai Zubia',
        'version' => '1.0',
        'title' => 'TicketBai Zubia',
        // 'termsOfService' => 'https://tbai.barnetik.com',
        'contact' => [
            'email' => 'tbai@barnetik.com'
        ],
        'license' => [
            'name' => 'AGPL 3.0',
            'url' => 'https://www.gnu.org/licenses/agpl-3.0.en.html'
        ],
    ],
    'host' => 'tbai.barnetik.com',
    'schemes' => ['http'],
    'paths' => [
        '/invoice/sign' => [
            'post' => [
                'summary' => 'Faktura bat sinatu - Firmar una factura',
                'description' => '',
                'operationId' => 'addInvoice',
                'consumes' => ['application/json'],
                'produces' => ['application/json'],
                'parameters' => [
                    [
                        'in' => 'body',
                        'name' => 'body',
                        'description' => 'Sinatu beharreko faktura (Invoice motakoa) - Factura a firmar (de tipo Invoice) ',
                        'schema' => [
                            '$ref' =>  '#/definitions/Invoice'
                        ],
                    ]
                ],
                'responses' => [
                    "200" => [
                        'description' => 'Faktura sinatu ahal izan da - La firma de la factura se ha realizado con éxito',
                        // 'schema' => [
                        //     '$ref' => '#/definitions/SignedInvoiceResult'
                        // ]
                    ],
                    "400" => [
                        'description' => 'Bidalitako fakturaren datuak ez dira zuzenak - Los datos de la factura no son correctos'
                    ],
                    "500" => [
                        'description' => 'Zerbitzarian arazo ezezagun bat egon da - Error no identificado'
                    ]
                ]
            ]
        ]
    ],
    'definitions' => [
        'Invoice' => TicketBai::docJson()
    ],

]);
