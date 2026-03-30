## ADDED Requirements

### Requirement: Explicit boundary between framework core and starter app
Quill SHALL document que responsabilidades pertenecen al framework y cuales quedan bajo el starter app u otras plantillas para evitar dependencias implicitas.

#### Scenario: Consume framework desde una aplicacion personalizada
- **WHEN** una aplicacion que no usa el starter oficial inicializa Quill mediante los contratos publicos
- **THEN** obtiene exactamente las mismas capacidades del framework sin requerir archivos ni helpers especificos del starter

### Requirement: Application-owned composition contracts
Quill MUST exponer contratos para que la aplicacion gobierne la composicion de rutas, providers, middlewares y servicios externos sin tocar el nucleo del framework.

#### Scenario: Integrate app-specific providers
- **WHEN** un equipo registra providers personalizados para rutas y servicios durante el bootstrap
- **THEN** Quill acepta dichos providers como limites de la aplicacion y mantiene el marco desacoplado del codigo especifico del starter
