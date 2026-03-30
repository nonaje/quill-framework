## MODIFIED Requirements

### Requirement: Canonical application bootstrap
Quill SHALL exponer un punto de arranque canonico y programatico que permita construir una aplicacion reusable desde codigo, sin requerir que el framework conozca por defecto la estructura de carpetas o el entrypoint web de una aplicacion final.

#### Scenario: Build application programmatically
- **WHEN** un consumidor integra `quill-framework` desde su propio bootstrap y crea la aplicacion indicando sus dependencias necesarias
- **THEN** Quill devuelve una instancia operable con contenedor, routing y servicios base preparados sin asumir `public/index.php`, `config/` o `routes/` como parte obligatoria del framework

### Requirement: Extensible composition root
Quill MUST permitir que el consumidor reemplace servicios por defecto durante el bootstrap usando hooks o bindings documentados, de modo que una app externa pueda componer runtime HTTP, configuracion y otros servicios sin modificar el codigo interno del framework.

#### Scenario: Override default service during bootstrap
- **WHEN** una aplicacion registra una implementacion personalizada para un servicio soportado durante el arranque
- **THEN** la aplicacion resuelve la implementacion personalizada en lugar de la predeterminada y mantiene ese wiring dentro de su propio composition root
