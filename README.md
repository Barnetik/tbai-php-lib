# Tbai PHP lib

TicketBai sistema erabiltzeko PHP liburutegia (Lanean oraindik)


Gaur egun egiteko gai dena:

 * Fakturen, fakturen zuzenketen eta hauen baliogabetzeen TicketBai formatudun XML-a sortu
 * Fakturak, fakturen zuzenketak eta hauen baliogabetzeak XaDES motako sinadurarekin sinatu
 * Fakturak, fakturen zuzenketak eta hauen baliogabetzeak EAEko hiru diputazioetako zerbitzuetara igorri. 
 * Bizkaiko kasuan (Batuz), pertsona fisikoentzako 140 modeloa badabil fakturak igortzean eta fakturak baliogabetzean.
   * Oraindik ez dabil zuzenketen kasuetan

Oraingoz ez da Zuzendu zerbitzuekin komunikatzeko gai

----

Librería para integrarse con el sistema TicketBai (En desarrollo)

Funcionalidades actuales:

 * Generar XML en formato TicketBai de facturas, facturas rectificativas y cancelaciones de factura
 * Firmar estos documentos con firma XaDES
 * Enviar estos documentos a los servicios de las tres haciendas forales de la CAV. (En el caso de Bizkaia aún no es posible emitir los modelos 140)
 * En el caso de Bizkaia (Batuz), es posible también emitir y cancelar facturas para personas físicas utilizando el modelo 140
    * Aún no es posible emitir correcciones

Por ahora no se comunica con los servicios Zuzendu


