## ADDED Requirements

### Requirement: Public capabilities without mandatory globals
Quill MUST exponer sus capacidades principales mediante objetos y contratos explicitos, de modo que los helpers globales sean opcionales y no la unica via de uso.

#### Scenario: Use Quill without global helpers
- **WHEN** una aplicacion inicializa y usa Quill solo mediante su bootstrap canonico y sus objetos resultantes
- **THEN** puede registrar rutas, configurar servicios y procesar requests sin invocar helpers globales

### Requirement: Stable service overrides per application
Quill SHALL ofrecer overrides reemplazables por aplicacion para bindings documentados del contenedor, error handling y response emission, sin contaminar otras instancias.

#### Scenario: Customize one application instance only
- **WHEN** una instancia de Quill reemplaza un binding soportado durante el bootstrap
- **THEN** el cambio afecta solo a esa aplicacion y no contamina otras instancias ni el estado global compartido
