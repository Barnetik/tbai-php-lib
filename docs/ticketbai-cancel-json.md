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
            "description": "\nFaktura baliogabetuko den lurraldea - Territorio en el que se cancelar\u00e1 la factura\n  * 01: Araba\n  * 02: Bizkaia\n  * 03: Gipuzkoa\n"
        },
        "selfEmployed": {
            "type": "boolean",
            "default": false,
            "description": "Fakturaren egilea autonomoa bada - Si el emisor de la factura es aut\u00f3nomo"
        },
        "invoiceId": {
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
        "fingerprint": []
    },
    "required": [
        "territory",
        "invoiceId",
        "fingerprint"
    ]
}
```