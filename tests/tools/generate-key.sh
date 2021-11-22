#! /bin/bash
keytool -genkeypair -keystore tbaiTestStore.p12 -storetype PKCS12 -storepass tbai-test -alias TBAI -keyalg RSA -keysize 2048 -validity 99999 -dname "CN=TBAI Test Certificate, O=ACME Koop., L=Markina-Xemein, ST=Bizkaia, C=ES" -ext san=dns:tbai-test.example.com,dns:localhost,ip:127.0.0.1

