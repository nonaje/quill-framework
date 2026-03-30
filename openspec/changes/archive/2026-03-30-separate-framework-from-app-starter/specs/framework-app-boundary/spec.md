## ADDED Requirements

### Requirement: Clear ownership between framework and starter app
Quill MUST documentar que responsabilidades pertenecen al framework reusable y cuales pertenecen al starter app oficial, incluyendo bootstrap web, runtime HTTP concreto, convenciones de proyecto, configuracion opinionada y composition root del consumidor final.

#### Scenario: Identify ownership of a framework concern
- **WHEN** un mantenedor evalua una capacidad como routing, runtime HTTP, carga de archivos o entrypoint web
- **THEN** puede determinar de forma explicita y documentada si esa responsabilidad vive en `quill-framework` o en `quill-app`

### Requirement: Framework can be consumed from a separate repository
Quill SHALL exponer una superficie publica suficiente para que otro repositorio cree y opere aplicaciones sobre el framework sin depender de archivos internos, helpers ocultos o convenciones no documentadas del paquete.

#### Scenario: Bootstrap app from external repository
- **WHEN** un repositorio `quill-app` integra `quill-framework` como dependencia y construye su propia aplicacion
- **THEN** puede registrar servicios, configurar el runtime, cargar sus recursos y procesar requests usando solo la API publica documentada del framework
