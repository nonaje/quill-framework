## MODIFIED Requirements

### Requirement: Root-based path resolution
Quill SHALL resolver archivos y recursos relativos a un `app root` explicito cuando el consumidor decida usar esa capacidad, sin convertir esa convencion de filesystem en un requisito obligatorio del framework reusable.

#### Scenario: Resolve path from external application root
- **WHEN** una aplicacion construida sobre `quill-framework` registra un `app root` y solicita rutas a archivos o recursos
- **THEN** el framework resuelve esos paths respecto a ese root explicito sin asumir una estructura opinionada por defecto

### Requirement: Deterministic configuration precedence
Quill MUST definir un orden de precedencia estable entre valores por defecto del framework, configuracion provista por la aplicacion consumidora, variables de entorno y overrides en runtime.

#### Scenario: Resolve same key from framework and app sources
- **WHEN** una misma clave esta definida en defaults del framework y en configuracion inyectada por la app consumidora
- **THEN** Quill devuelve el valor de la fuente con mayor precedencia segun la regla documentada

## ADDED Requirements

### Requirement: App-level filesystem conventions stay outside the framework
Quill MUST permitir loaders y utilidades reutilizables de configuracion o rutas sin imponer como contrato del framework la existencia de directorios opinionados como `config/`, `routes/` o un archivo `.env` en ubicaciones predeterminadas.

#### Scenario: Use framework without starter directory conventions
- **WHEN** un consumidor integra `quill-framework` desde un proyecto con estructura distinta a la del starter oficial
- **THEN** el framework sigue pudiendo bootear y cargar la configuracion provista sin exigir las convenciones de filesystem de `quill-app`
