```json
{
    "type": "object",
    "properties": {
        "territory": {
            "type": "string",
            "enum": [
                "01",
                "02",
                "03"
            ],
            "description": "\nFaktura aurkeztuko den lurraldea - Territorio en el que se presentar\u00e1 la factura\n  * 01: Araba\n  * 02: Bizkaia\n  * 03: Gipuzkoa\n"
        },
        "selfEmployed": {
            "type": "boolean",
            "default": false,
            "description": "Fakturaren egilea autonomoa bada - Si el emisor de la factura es aut\u00f3nomo"
        },
        "subject": {
            "type": "object",
            "properties": {
                "issuer": {
                    "type": "object",
                    "properties": {
                        "vatId": {
                            "type": "string",
                            "pattern": "^(([a-z|A-Z]{1}\\d{7}[a-z|A-Z]{1})|(\\d{8}[a-z|A-Z]{1})|([a-z|A-Z]{1}\\d{8}))$",
                            "description": "IFZ - NIF"
                        },
                        "name": {
                            "type": "string",
                            "maxLength": 120,
                            "description": "Abizenak eta izena edo Sozietatearen izena - Apellidos y nombre o Raz\u00f3n social"
                        }
                    },
                    "required": [
                        "vatId",
                        "name"
                    ]
                },
                "recipients": {
                    "type": "array",
                    "items": {
                        "type": "object",
                        "properties": {
                            "vatId": {
                                "type": "string",
                                "description": "IFZ edo Identifikatzailea - NIF o Identificador"
                            },
                            "vatIdType": {
                                "type": "string",
                                "enum": [
                                    "02",
                                    "03",
                                    "04",
                                    "05",
                                    "06"
                                ],
                                "default": "02",
                                "description": "\nDokumentu mota - Tipo de documento:\n * 02: IFZ - NIF\n * 03: Pasaportea - Pasaporte\n * 04: Egoitza dagoen herrialdeak edo lurraldeak emandako nortasun agiri ofiziala - Documento oficial de identificaci\u00f3n expedido por el pa\u00eds o territorio de residencia\n * 05: Egoitza ziurtagiria - Certificado de residencia\n * 06: Beste frogagiri bat - Otro documento probatorio\n                    "
                            },
                            "name": {
                                "type": "string",
                                "maxLength": 120,
                                "description": "Abizenak eta izena edo Sozietatearen izena - Apellidos y nombre o Raz\u00f3n social"
                            },
                            "postalCode": {
                                "type": "string",
                                "maxLength": 20
                            },
                            "address": {
                                "type": "string",
                                "maxLength": 250
                            },
                            "countryCode": {
                                "type": "string",
                                "description": "Herrialdearen kodea (ISO3166 alpha2) - C\u00f3digo de pa\u00eds (ISO3166 alpha2)",
                                "default": "ES"
                            }
                        },
                        "required": [
                            "vatId",
                            "name"
                        ]
                    },
                    "minItems": 0,
                    "maxItems": 100
                },
                "issuedBy": {
                    "type": "string",
                    "enum": [
                        "N",
                        "T",
                        "D"
                    ],
                    "default": "N",
                    "description": "\nHirugarren batek edo hartzaileak egindako faktura - Factura emitida por tercera entidad o por entidad destinataria\n * N: Ez. Faktura egileak berak egin du - No. Factura emitida por la propia entidad emisora\n * T: Faktura hirugarren batek egin du - Factura emitida por tercera entidad\n * D: Faktura eragiketaren hartzaileak egin du - Factura emitida por la entidad destinataria de la operaci\u00f3n\n                    "
                }
            },
            "required": [
                "issuer",
                "recipients"
            ]
        },
        "invoice": {
            "type": "object",
            "properties": {
                "header": {
                    "type": "object",
                    "properties": {
                        "series": {
                            "type": "string",
                            "maxLength": 20,
                            "description": "Fakturaren seriea - Serie factura"
                        },
                        "invoiceNumber": {
                            "type": "string",
                            "maxLength": 20,
                            "description": "Fakturaren zenbakia - N\u00famero factura"
                        },
                        "expeditionDate": {
                            "type": "string",
                            "minLength": 10,
                            "maxLength": 10,
                            "pattern": "^\\d{2,2}-\\d{2,2}-\\d{4,4}$",
                            "description": "Faktura bidali den data (adib: 21-12-2020) - Fecha de expedici\u00f3n de factura (ej: 21-12-2020)"
                        },
                        "expeditionTime": {
                            "type": "string",
                            "minLength": 10,
                            "maxLength": 10,
                            "pattern": "^\\d{2,2}:\\d{2,2}:\\d{2,2}$",
                            "description": "Faktura bidali den ordua (adib: 21:00:00) - Hora de expedici\u00f3n de factura (ej: 21:00:00)"
                        },
                        "simplifiedInvoice": {
                            "type": "boolean",
                            "default": false,
                            "description": "Faktura erraztua - Factura simplificada"
                        },
                        "rectifyingInvoice": {
                            "type": "object",
                            "properties": {
                                "code": {
                                    "type": "string",
                                    "enum": [
                                        "R1",
                                        "R2",
                                        "R3",
                                        "R4",
                                        "R5"
                                    ],
                                    "description": "\n  Faktura zuzentzailearen mota identifikatzen duen kodea - C\u00f3digo que identifica el tipo de factura rectificativa\n   * R1: Faktura zuzentzailea: zuzenbidean eta BEZaren Legearen 80.Bat, Bi eta Sei artikuluan oinarritutako akatsa - Factura rectificativa: error fundado en derecho y Art. 80 Uno, Dos y Seis de la Ley del IVA\n   * R2: Faktura zuzentzailea: BEZaren Legearen 80.Hiru artikulua - Factura rectificativa: art\u00edculo 80 Tres de la Ley del IVA\n   * R3: Faktura zuzentzailea: BEZaren Legearen 80.Lau artikulua - Factura rectificativa: art\u00edculo 80 Cuatro de la Ley del IVA\n   * R4: Faktura zuzentzailea: gainerakoak - Factura rectificativa: Resto\n   * R5: Faktura sinplifikatuak zuzentzeko faktura - Factura rectificativa en facturas simplificadas\n          "
                                },
                                "type": {
                                    "type": "string",
                                    "enum": [
                                        "S",
                                        "I"
                                    ],
                                    "description": "\nFaktura zuzentzaile mota - Tipo de factura rectificativa\n * S: Ordezkapenagatiko faktura zuzentzailea - Factura rectificativa por sustituci\u00f3n\n * I: Diferentziengatiko faktura zuzentzailea - Factura rectificativa por diferencias\n          "
                                },
                                "rectifyingAmount": {
                                    "type": "object",
                                    "properties": {
                                        "base": {
                                            "type": "string",
                                            "pattern": "^(\\+|-)?\\d{1,12}(\\.\\d{0,2})?$",
                                            "description": "Ordezkatutako fakturaren zerga oinarria (2 dezimalekin) - Base imponible de la factura sustituida (2 decimales)"
                                        },
                                        "quota": {
                                            "type": "string",
                                            "pattern": "^(\\+|-)?\\d{1,12}(\\.\\d{0,2})?$",
                                            "description": "Ordezkatutako fakturaren jasanarazitako kuota (2 dezimalekin) - Cuota repercutida de la factura sustituida (2 decimales)"
                                        },
                                        "surcharge": {
                                            "type": "string",
                                            "pattern": "^(\\+|-)?\\d{1,12}(\\.\\d{0,2})?$",
                                            "description": "Ordezkatutako fakturaren baliokidetasun errekarguaren kuota (2 dezimalekin) - Cuota del recargo de equivalencia de la factura sustituida. (2 decimales)"
                                        }
                                    },
                                    "required": [
                                        "base",
                                        "quota"
                                    ]
                                }
                            },
                            "required": [
                                "code",
                                "type"
                            ]
                        },
                        "rectifiedInvoices": {
                            "type": "array",
                            "items": {
                                "type": "object",
                                "properties": {
                                    "invoiceNumber": {
                                        "type": "string",
                                        "maxLength": 20,
                                        "description": "Zuzendutako edo ordezkatutako faktura identifikatzen duen zenbakia - N\u00famero de la factura rectificada o sustituida"
                                    },
                                    "sentDate": {
                                        "type": "string",
                                        "pattern": "^\\d{2,2}-\\d{2,2}-\\d{4,4}$",
                                        "description": "Zuzendutako edo ordezkatutako faktura egin den data (adib: 21-12-2020) - Fecha de expedici\u00f3n de la factura rectificada o sustituida (ej: 21-12-2020)"
                                    },
                                    "series": {
                                        "type": "string",
                                        "maxLength": 20,
                                        "description": "Zuzendutako edo ordezkatutako faktura identifikatzen duen serie zenbakia - N\u00famero de serie que identifica a la factura rectificada o sustituida"
                                    }
                                },
                                "required": [
                                    "invoiceNumber",
                                    "sentDate"
                                ]
                            },
                            "minItems": 0,
                            "maxItems": 100
                        }
                    }
                },
                "data": {
                    "type": "object",
                    "properties": {
                        "operationDate": {
                            "type": "string",
                            "minLength": 10,
                            "maxLength": 10,
                            "pattern": "^\\d{2,2}-\\d{2,2}-\\d{4,4}$",
                            "description": "Faktura bidali den data (adib: 21-12-2020) - Fecha de operaci\u00f3n de factura (ej: 21-12-2020)"
                        },
                        "description": {
                            "type": "string",
                            "maxLength": 250
                        },
                        "details": {
                            "type": "array",
                            "items": {
                                "type": "object",
                                "properties": {
                                    "description": {
                                        "type": "string",
                                        "maxLength": 250
                                    },
                                    "unitPrice": {
                                        "type": "string",
                                        "pattern": "^(\\+|-)?\\d{1,12}(\\.\\d{0,2})?$",
                                        "description": "Zenbatekoa, aleko (BEZ gabe, 2 dezimalekin) - Importe unitario (sin IVA, 2 decimales)"
                                    },
                                    "quantity": {
                                        "type": "string",
                                        "pattern": "^(\\+|-)?\\d{1,12}(\\.\\d{0,2})?$",
                                        "description": "Kopurua (2 dezimalekin) - Cantidad (2 decimales)"
                                    },
                                    "discount": {
                                        "type": "string",
                                        "pattern": "^(\\+|-)?\\d{1,12}(\\.\\d{0,2})?$",
                                        "description": "Deskontua (2 dezimalekin) - Descuento (Sin IVA, 2 decimales)"
                                    },
                                    "totalAmount": {
                                        "type": "string",
                                        "pattern": "^(\\+|-)?\\d{1,12}(\\.\\d{0,2})?$",
                                        "description": "Zenbatekoa, guztira (BEZ barne, 2 dezimalekin) - Importe total (con IVA, 2 decimales)"
                                    }
                                },
                                "required": [
                                    "description",
                                    "unitPrice",
                                    "quantity",
                                    "totalAmount"
                                ]
                            },
                            "minItems": 0,
                            "maxItems": 1000
                        },
                        "total": {
                            "type": "string",
                            "pattern": "^(\\+|-)?\\d{1,12}(\\.\\d{0,2})?$",
                            "description": "Zenbatekoa guztira (2 dezimalekin) - Importe total (2 decimales)"
                        },
                        "vatRegimes": {
                            "type": "array",
                            "items": {
                                "type": "string",
                                "enum": [
                                    "01",
                                    "02",
                                    "03",
                                    "04",
                                    "05",
                                    "06",
                                    "07",
                                    "08",
                                    "09",
                                    "10",
                                    "11",
                                    "12",
                                    "13",
                                    "14",
                                    "15",
                                    "51",
                                    "52",
                                    "53"
                                ],
                                "description": "\n * 01: Erregimen orokorreko eragiketa eta hurrengo balioetan jaso gabe dagoen beste edozein kasu - Operaci\u00f3n de r\u00e9gimen general y cualquier otro supuesto que no est\u00e9 recogido en los siguientes valores\n * 02: Esportazioa - Exportaci\u00f3n\n * 03: Erabilitako ondasunen, arte objektuen, zaharkinen eta bilduma objektuen araudi berezia aplikatzen zaien eragiketak - Operaciones a las que se aplique el r\u00e9gimen especial de bienes usados, objetos de arte, antig\u00fcedades y objetos de colecci\u00f3n\n * 04: Inbertsio urrearen araubide berezia - R\u00e9gimen especial del oro de inversi\u00f3n\n * 05: Bidaia-agentzien araubide berezia - R\u00e9gimen especial de las agencias de viajes\n * 06: BEZeko erakundeen multzoaren araudi berezia (maila aurreratua) - R\u00e9gimen especial grupo de entidades en IVA (Nivel Avanzado)\n * 07: Kutxa-irizpidearen araubide berezia - R\u00e9gimen especial del criterio de caja\n * 08: Ekoizpen, Zerbitzu eta Inportazioaren gaineko Zergari \/ Kanarietako Zeharkako Zerga Orokorrari lotutako eragiketak - Operaciones sujetas al IPSI\/IGIC (Impuesto sobre la Producci\u00f3n, los Servicios y la Importaci\u00f3n \/ Impuesto General Indirecto Canario)\n * 09: Besteren izenean eta kontura ari diren bidai agentziek egindako zerbitzuen fakturazioa(Fakturazio Araudiko 3. xedapen gehigarria) - Facturaci\u00f3n de las prestaciones de servicios de agencias de viaje que act\u00faan como mediadoras en nombre y por cuenta ajena (disposici\u00f3n adicional 3\u00aa del Reglamento de Facturaci\u00f3n)\n * 10: Hirugarrenen kontura kobratzea ordainsari profesionalak edo jabetza industrialetik eratorritako eskubideak, egilearenak edo bazkideen, bazkideen edo elkargokideen kontura kobratzeko eginkizun horiek betetzen dituzten sozietate, elkarte, elkargo profesional edo bestelako erakundeek egindakoak - Cobros por cuenta de terceros o terceras de honorarios profesionales o de derechos derivados de la propiedad industrial, de autor u otros por cuenta de sus socios, socias, asociados, asociadas, colegiados o colegiadas efectuados por sociedades, asociaciones, colegios profesionales u otras entidades que realicen estas funciones de cobro\n * 11: Negozio lokala errentatzeko eragiketak, atxikipenari lotuak - Operaciones de arrendamiento de local de negocio sujetos a retenci\u00f3n\n * 12: Negozio lokala errentatzeko eragiketak, atxikipenari lotu gabeak - Operaciones de arrendamiento de local de negocio no sujetos a retenci\u00f3n\n * 13: Negozio lokala errentatzeko eragiketak, atxikipenari lotuak eta lotu gabeak - Operaciones de arrendamiento de local de negocio sujetas y no sujetas a retenci\u00f3n\n * 14: Hartzailea administrazio publiko bat denean ordaintzeke dauden BEZdun fakturak, obra ziurtagirietakoak - Factura con IVA pendiente de devengo en certificaciones de obra cuyo destinatario sea una Administraci\u00f3n P\u00fablica\n * 15: Segidako traktuko eragiketetan ordaintzeke dagoen BEZdun faktura - Factura con IVA pendiente de devengo en operaciones de tracto sucesivo\n * 51: Baliokidetasun errekarguko eragiketak - Operaciones en recargo de equivalencia\n * 52: Erregimen erraztuko eragiketak - Operaciones en r\u00e9gimen simplificado\n * 53: BEZaren ondorioetarako enpresari edo profesionaltzat jotzen ez diren pertsona edo erakundeek egindako eragiketak - Operaciones realizadas por personas o entidades que no tengan la consideraci\u00f3n de empresarios, empresarias o profesionales a efectos del IVA\n                        "
                            },
                            "minItems": 1,
                            "maxItems": 3,
                            "description": "Gakoak: BEZaren araubideen eta zerga-ondorioak dauzkaten eragiketak - Claves de reg\u00edmenes de IVA y operaciones con trascendencia tributaria"
                        },
                        "supportedRetention": {
                            "type": "string",
                            "pattern": "^(\\+|-)?\\d{1,12}(\\.\\d{0,2})?$",
                            "description": "Jasandako atxikipena (2 dezimalekin) - Retenci\u00f3n soportada (2 decimales)"
                        },
                        "taxBaseCost": {
                            "type": "string",
                            "pattern": "^(\\+|-)?\\d{1,12}(\\.\\d{0,2})?$",
                            "description": "Kosturako zerga-oinarria (2 dezimalekin) - Base imponible a coste (2 decimales)"
                        }
                    },
                    "required": [
                        "description",
                        "total",
                        "vatRegimes"
                    ]
                },
                "breakdown": {
                    "type": "object",
                    "properties": {
                        "nationalSubjectExemptBreakdownItems": {
                            "type": "array",
                            "maxItems": 2,
                            "items": {
                                "type": "object",
                                "properties": {
                                    "taxBase": {
                                        "type": "string",
                                        "description": "Salbuetsitako zerga-oinarria (2 dezimalekin) - Base imponible exenta (2 decimales)"
                                    },
                                    "reason": {
                                        "type": "string",
                                        "enum": [
                                            "E1",
                                            "E2",
                                            "E3",
                                            "E4",
                                            "E5",
                                            "E6"
                                        ],
                                        "description": "\nArrazoia - Raz\u00f3n:\n  * E1: Salbuetsita 20. art. - Exenta Art.20\n  * E2: Salbuetsita 21. art. - Exenta Art.21\n  * E3: Salbuetsita 22. art. - Exenta Art.22\n  * E4: Salbuetsita 23. art. eta 24. art. - Exenta Art.23 y 24\n  * E5: Salbuetsita 25. art. - Exenta Art.25\n  * E6: Salbuetsita Beste batzuk - Exenta Otros\n\n"
                                    }
                                },
                                "required": [
                                    "taxBase",
                                    "reason"
                                ]
                            },
                            "description": "Kargapean eta salbuetsitakoak - Sujetas a carga y exentas"
                        },
                        "nationalSubjectNotExemptBreakdownItems": {
                            "type": "array",
                            "maxItems": 7,
                            "items": {
                                "type": "object",
                                "properties": {
                                    "vatDetails": {
                                        "type": "array",
                                        "description": "Zenbatekoak - Importes",
                                        "minItems": 1,
                                        "maxItems": 6,
                                        "items": {
                                            "type": "object",
                                            "properties": {
                                                "taxBase": {
                                                    "type": "string",
                                                    "description": "Zerga oinarria (2 dezimalekin) - Base imponible (2 decimales)"
                                                },
                                                "taxRate": {
                                                    "type": "string",
                                                    "description": "Zerga tasa (2 dezimalekin) - Tipo impositivo (2 decimales)"
                                                },
                                                "taxQuota": {
                                                    "type": "string",
                                                    "description": "Zergaren kuota (2 dezimalekin) - Cuota del impuesto (2 decimales)"
                                                },
                                                "equivalenceRate": {
                                                    "type": "string",
                                                    "description": "Baliokidetasun errekarguaren tasa (2 dezimalekin) - Tipo del recargo de equivalencia (2 decimales)"
                                                },
                                                "equivalenceQuota": {
                                                    "type": "string",
                                                    "description": "Baliokidetasun errekarguaren kuota (2 dezimalekin) - Cuota del recargo de equivalencia (2 decimales)"
                                                },
                                                "isEquivalenceOperation": {
                                                    "type": "boolean",
                                                    "default": false,
                                                    "description": "Baliokidetasun errekargudun eragiketa edo araubide erraztuko eragiketa bat da - Es una operaci\u00f3n en recargo de equivalencia o R\u00e9gimen simplificado"
                                                }
                                            },
                                            "required": [
                                                "taxBase"
                                            ]
                                        }
                                    },
                                    "type": {
                                        "type": "string",
                                        "enum": [
                                            "S1",
                                            "S2"
                                        ],
                                        "description": "\nSalbuetsi gabeko mota - Tipo de no exenta\n  * S1: sub. pas. inbertsiorik ez - sin Inversi\u00f3n de Sujeto Pasivo (ISP)\n  * S2: sub. pas. inbertsioa - con Inversi\u00f3n de Sujeto Pasivo (ISP)\n"
                                    }
                                },
                                "required": [
                                    "type",
                                    "vatDetails"
                                ]
                            },
                            "description": "Kargapean eta salbuetsi gabe - Sujetas a carga y no exentas"
                        },
                        "nationalNotSubjectBreakdownItems": {
                            "type": "array",
                            "maxItems": 7,
                            "items": {
                                "type": "object",
                                "properties": {
                                    "amount": {
                                        "type": "string",
                                        "description": "Zenbatekoa (2 dezimalekin) - Importe (2 decimales)"
                                    },
                                    "reason": {
                                        "type": "string",
                                        "enum": [
                                            "RL",
                                            "OT"
                                        ],
                                        "description": "\nKargapean ez egoteko arrazoia - Causa no sujeci\u00f3n:\n  * RL: Kargapean ez kokapen arauak direla eta - No sujeto por reglas de localizaci\u00f3n\n  * OT: Kargapean ez 7., 14. art, Beste batzuk - No sujeto art. 7, 14, Otros\n"
                                    }
                                },
                                "required": [
                                    "amount",
                                    "reason"
                                ]
                            },
                            "description": "Kargapean ez daudenak - No sujetas a carga"
                        },
                        "foreignServiceSubjectExemptBreakdownItems": {
                            "type": "array",
                            "maxItems": 2,
                            "items": {
                                "type": "object",
                                "properties": {
                                    "taxBase": {
                                        "type": "string",
                                        "description": "Salbuetsitako zerga-oinarria (2 dezimalekin) - Base imponible exenta (2 decimales)"
                                    },
                                    "reason": {
                                        "type": "string",
                                        "enum": [
                                            "E1",
                                            "E2",
                                            "E3",
                                            "E4",
                                            "E5",
                                            "E6"
                                        ],
                                        "description": "\nArrazoia - Raz\u00f3n:\n  * E1: Salbuetsita 20. art. - Exenta Art.20\n  * E2: Salbuetsita 21. art. - Exenta Art.21\n  * E3: Salbuetsita 22. art. - Exenta Art.22\n  * E4: Salbuetsita 23. art. eta 24. art. - Exenta Art.23 y 24\n  * E5: Salbuetsita 25. art. - Exenta Art.25\n  * E6: Salbuetsita Beste batzuk - Exenta Otros\n\n"
                                    }
                                },
                                "required": [
                                    "taxBase",
                                    "reason"
                                ]
                            },
                            "description": "Kargapean eta salbuetsitakoak - Sujetas a carga y exentas"
                        },
                        "foreignServiceSubjectNotExemptBreakdownItems": {
                            "type": "array",
                            "maxItems": 7,
                            "items": {
                                "type": "object",
                                "properties": {
                                    "vatDetails": {
                                        "type": "array",
                                        "description": "Zenbatekoak - Importes",
                                        "minItems": 1,
                                        "maxItems": 6,
                                        "items": {
                                            "type": "object",
                                            "properties": {
                                                "taxBase": {
                                                    "type": "string",
                                                    "description": "Zerga oinarria (2 dezimalekin) - Base imponible (2 decimales)"
                                                },
                                                "taxRate": {
                                                    "type": "string",
                                                    "description": "Zerga tasa (2 dezimalekin) - Tipo impositivo (2 decimales)"
                                                },
                                                "taxQuota": {
                                                    "type": "string",
                                                    "description": "Zergaren kuota (2 dezimalekin) - Cuota del impuesto (2 decimales)"
                                                },
                                                "equivalenceRate": {
                                                    "type": "string",
                                                    "description": "Baliokidetasun errekarguaren tasa (2 dezimalekin) - Tipo del recargo de equivalencia (2 decimales)"
                                                },
                                                "equivalenceQuota": {
                                                    "type": "string",
                                                    "description": "Baliokidetasun errekarguaren kuota (2 dezimalekin) - Cuota del recargo de equivalencia (2 decimales)"
                                                },
                                                "isEquivalenceOperation": {
                                                    "type": "boolean",
                                                    "default": false,
                                                    "description": "Baliokidetasun errekargudun eragiketa edo araubide erraztuko eragiketa bat da - Es una operaci\u00f3n en recargo de equivalencia o R\u00e9gimen simplificado"
                                                }
                                            },
                                            "required": [
                                                "taxBase"
                                            ]
                                        }
                                    },
                                    "type": {
                                        "type": "string",
                                        "enum": [
                                            "S1",
                                            "S2"
                                        ],
                                        "description": "\nSalbuetsi gabeko mota - Tipo de no exenta\n  * S1: sub. pas. inbertsiorik ez - sin Inversi\u00f3n de Sujeto Pasivo (ISP)\n  * S2: sub. pas. inbertsioa - con Inversi\u00f3n de Sujeto Pasivo (ISP)\n"
                                    }
                                },
                                "required": [
                                    "type",
                                    "vatDetails"
                                ]
                            },
                            "description": "Kargapean eta salbuetsi gabe - Sujetas a carga y no exentas"
                        },
                        "foreignServiceNotSubjectBreakdownItems": {
                            "type": "array",
                            "maxItems": 7,
                            "items": {
                                "type": "object",
                                "properties": {
                                    "amount": {
                                        "type": "string",
                                        "description": "Zenbatekoa (2 dezimalekin) - Importe (2 decimales)"
                                    },
                                    "reason": {
                                        "type": "string",
                                        "enum": [
                                            "RL",
                                            "OT"
                                        ],
                                        "description": "\nKargapean ez egoteko arrazoia - Causa no sujeci\u00f3n:\n  * RL: Kargapean ez kokapen arauak direla eta - No sujeto por reglas de localizaci\u00f3n\n  * OT: Kargapean ez 7., 14. art, Beste batzuk - No sujeto art. 7, 14, Otros\n"
                                    }
                                },
                                "required": [
                                    "amount",
                                    "reason"
                                ]
                            },
                            "description": "Kargapean ez daudenak - No sujetas a carga"
                        },
                        "foreignDeliverySubjectExemptBreakdownItems": {
                            "type": "array",
                            "maxItems": 2,
                            "items": {
                                "type": "object",
                                "properties": {
                                    "taxBase": {
                                        "type": "string",
                                        "description": "Salbuetsitako zerga-oinarria (2 dezimalekin) - Base imponible exenta (2 decimales)"
                                    },
                                    "reason": {
                                        "type": "string",
                                        "enum": [
                                            "E1",
                                            "E2",
                                            "E3",
                                            "E4",
                                            "E5",
                                            "E6"
                                        ],
                                        "description": "\nArrazoia - Raz\u00f3n:\n  * E1: Salbuetsita 20. art. - Exenta Art.20\n  * E2: Salbuetsita 21. art. - Exenta Art.21\n  * E3: Salbuetsita 22. art. - Exenta Art.22\n  * E4: Salbuetsita 23. art. eta 24. art. - Exenta Art.23 y 24\n  * E5: Salbuetsita 25. art. - Exenta Art.25\n  * E6: Salbuetsita Beste batzuk - Exenta Otros\n\n"
                                    }
                                },
                                "required": [
                                    "taxBase",
                                    "reason"
                                ]
                            },
                            "description": "Kargapean eta salbuetsitakoak - Sujetas a carga y exentas"
                        },
                        "foreignDeliverySubjectNotExemptBreakdownItems": {
                            "type": "array",
                            "maxItems": 7,
                            "items": {
                                "type": "object",
                                "properties": {
                                    "vatDetails": {
                                        "type": "array",
                                        "description": "Zenbatekoak - Importes",
                                        "minItems": 1,
                                        "maxItems": 6,
                                        "items": {
                                            "type": "object",
                                            "properties": {
                                                "taxBase": {
                                                    "type": "string",
                                                    "description": "Zerga oinarria (2 dezimalekin) - Base imponible (2 decimales)"
                                                },
                                                "taxRate": {
                                                    "type": "string",
                                                    "description": "Zerga tasa (2 dezimalekin) - Tipo impositivo (2 decimales)"
                                                },
                                                "taxQuota": {
                                                    "type": "string",
                                                    "description": "Zergaren kuota (2 dezimalekin) - Cuota del impuesto (2 decimales)"
                                                },
                                                "equivalenceRate": {
                                                    "type": "string",
                                                    "description": "Baliokidetasun errekarguaren tasa (2 dezimalekin) - Tipo del recargo de equivalencia (2 decimales)"
                                                },
                                                "equivalenceQuota": {
                                                    "type": "string",
                                                    "description": "Baliokidetasun errekarguaren kuota (2 dezimalekin) - Cuota del recargo de equivalencia (2 decimales)"
                                                },
                                                "isEquivalenceOperation": {
                                                    "type": "boolean",
                                                    "default": false,
                                                    "description": "Baliokidetasun errekargudun eragiketa edo araubide erraztuko eragiketa bat da - Es una operaci\u00f3n en recargo de equivalencia o R\u00e9gimen simplificado"
                                                }
                                            },
                                            "required": [
                                                "taxBase"
                                            ]
                                        }
                                    },
                                    "type": {
                                        "type": "string",
                                        "enum": [
                                            "S1",
                                            "S2"
                                        ],
                                        "description": "\nSalbuetsi gabeko mota - Tipo de no exenta\n  * S1: sub. pas. inbertsiorik ez - sin Inversi\u00f3n de Sujeto Pasivo (ISP)\n  * S2: sub. pas. inbertsioa - con Inversi\u00f3n de Sujeto Pasivo (ISP)\n"
                                    }
                                },
                                "required": [
                                    "type",
                                    "vatDetails"
                                ]
                            },
                            "description": "Kargapean eta salbuetsi gabe - Sujetas a carga y no exentas"
                        },
                        "foreignDeliveryNotSubjectBreakdownItems": {
                            "type": "array",
                            "maxItems": 7,
                            "items": {
                                "type": "object",
                                "properties": {
                                    "amount": {
                                        "type": "string",
                                        "description": "Zenbatekoa (2 dezimalekin) - Importe (2 decimales)"
                                    },
                                    "reason": {
                                        "type": "string",
                                        "enum": [
                                            "RL",
                                            "OT"
                                        ],
                                        "description": "\nKargapean ez egoteko arrazoia - Causa no sujeci\u00f3n:\n  * RL: Kargapean ez kokapen arauak direla eta - No sujeto por reglas de localizaci\u00f3n\n  * OT: Kargapean ez 7., 14. art, Beste batzuk - No sujeto art. 7, 14, Otros\n"
                                    }
                                },
                                "required": [
                                    "amount",
                                    "reason"
                                ]
                            },
                            "description": "Kargapean ez daudenak - No sujetas a carga"
                        }
                    }
                }
            },
            "required": [
                "header",
                "data",
                "breakdown"
            ]
        },
        "fingerprint": {
            "type": "object",
            "properties": {
                "previousInvoice": {
                    "type": "object",
                    "properties": {
                        "invoiceNumber": {
                            "type": "string",
                            "maxLength": 20,
                            "description": "Aurreko fakturaren zenbakia - N\u00famero factura factura anterior"
                        },
                        "sentDate": {
                            "type": "string",
                            "pattern": "^\\d{2,2}-\\d{2,2}-\\d{4,4}$",
                            "description": "Aurreko faktura bidali zen data (adib: 21-12-2020) - Fecha de expedici\u00f3n de factura anterior (ej: 21-12-2020)"
                        },
                        "signature": {
                            "type": "string",
                            "maxLength": 100,
                            "description": "Aurreko fakturaren TBAI fitxategiko SignatureValue eremuko lehen ehun karaktereak - Primeros cien caracteres del campo SignatureValue del fichero TBAI de la factura anterior"
                        },
                        "series": {
                            "type": "string",
                            "maxLength": 20,
                            "description": "Aurreko fakturaren seriea - Serie factura anterior"
                        }
                    },
                    "required": [
                        "invoiceNumber",
                        "sentDate",
                        "signature"
                    ]
                }
            }
        },
        "batuzIncomeTaxes": {
            "type": "object",
            "properties": {
                "incomeTaxDetails": {
                    "items": {
                        "type": "object",
                        "properties": {
                            "epigraph": {
                                "type": "string",
                                "description": "PFEZ jardueraren epigrafe zenbakia - Ep\u00edgrafe de la actividad a la que est\u00e1 asociado el IRPF"
                            },
                            "incomeTaxAmount": {
                                "type": "string",
                                "description": "PFEZ sarrera zenbatekoa (2 dezimalekin). Epigrafe bat baino gehiago zehazten bada, derrigorezkoa - Importe del ingreso IRPF (con 2 decimales). Obligatorio si la factura lleva asociado m\u00e1s de un ep\u00edgrafe"
                            },
                            "collectionAndPaymentCriteria": {
                                "type": "boolean",
                                "default": false,
                                "description": "Faktura Kobrantzen eta ordainketen irizpidera atxikita badago - Si la factura est\u00e1 acogida al criterio de Cobros y Pagos"
                            }
                        },
                        "required": [
                            "epigraph"
                        ]
                    },
                    "minItems": 1,
                    "maxItems": 10
                }
            },
            "required": [
                "epigraph"
            ]
        }
    },
    "required": [
        "territory",
        "subject",
        "invoice",
        "fingerprint"
    ]
}
```