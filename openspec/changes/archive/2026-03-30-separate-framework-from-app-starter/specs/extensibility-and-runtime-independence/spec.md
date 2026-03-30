## MODIFIED Requirements

### Requirement: Stable service overrides per application
Quill SHALL ofrecer overrides reemplazables por aplicacion para bindings documentados del contenedor, runtime HTTP, configuracion, manejo de errores y response emission, sin contaminar otras instancias ni obligar a modificar internals del framework.

#### Scenario: Customize one application instance only
- **WHEN** una aplicacion que consume `quill-framework` reemplaza un binding soportado durante el bootstrap
- **THEN** el cambio afecta solo a esa aplicacion y no contamina otras instancias ni el estado global compartido

## ADDED Requirements

### Requirement: Framework services are composable from outside the core
Quill MUST exponer los puntos de extension necesarios para que una app externa componga el framework con su propio runtime, convenciones y wiring sin copiar o parchear codigo interno del paquete.

#### Scenario: Compose framework from a separate starter repository
- **WHEN** un repositorio `quill-app` registra los servicios y convenciones que necesita encima de `quill-framework`
- **THEN** la aplicacion puede bootear, procesar requests y extender el core usando unicamente contratos y hooks documentados
