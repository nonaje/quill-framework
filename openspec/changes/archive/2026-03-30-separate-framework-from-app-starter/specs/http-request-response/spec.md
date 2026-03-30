## MODIFIED Requirements

### Requirement: Stable request input access
Quill SHALL exponer una abstraccion de request que permita leer de forma consistente el metodo, route params, query params, body parseado soportado y el request subyacente mediante contratos estables, sin acoplar esa capacidad a una implementacion HTTP concreta embebida en el framework.

#### Scenario: Read route and query input from external app runtime
- **WHEN** una aplicacion que consume `quill-framework` inyecta un request compatible y un handler consulta parametros de ruta o query params
- **THEN** Quill devuelve los valores correctos sin mezclar fuentes ni exigir clases concretas del runtime del starter

### Requirement: Response construction helpers
Quill SHALL ofrecer una abstraccion de response inmutable con helpers semanticos para status, headers y formatos comunes como `json`, `plain` y `html`, construida sobre contratos del framework y del ecosistema PSR, sin heredar de implementaciones concretas que pertenezcan al starter app.

#### Scenario: Build JSON response from framework contract
- **WHEN** un handler genera una respuesta JSON usando el helper correspondiente
- **THEN** Quill devuelve una respuesta con status, body y `Content-Type` coherentes con el formato JSON y el resultado puede ser emitido por el runtime configurado desde la app consumidora

### Requirement: Response emission contract
Quill MUST emitir la respuesta final exactamente una vez, preservando status, headers y body producidos por el pipeline para cualquier runtime compatible compuesto por la aplicacion consumidora.

#### Scenario: Emit response through app-provided runtime
- **WHEN** el lifecycle del request produce una respuesta final valida dentro de una app construida sobre `quill-framework`
- **THEN** el emisor transmite el resultado observable del handler sin asumir una implementacion HTTP concreta perteneciente al framework
