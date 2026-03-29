## ADDED Requirements

### Requirement: Middleware scope ordering
Quill SHALL ejecutar middlewares globales, de grupo y de ruta en un orden deterministico y documentado, preservando la herencia esperada de grupos anidados.

#### Scenario: Execute nested middleware chain
- **WHEN** un request coincide con una ruta dentro de grupos anidados que tambien tienen middlewares propios
- **THEN** Quill ejecuta la cadena completa en el orden documentado antes de invocar el handler final

### Requirement: Supported middleware surface
Quill MUST aceptar middlewares declarados como instancias `MiddlewareInterface` o callables documentados dentro de la API canonica de este change.

#### Scenario: Resolve callable middleware
- **WHEN** una ruta o configuracion referencia un middleware callable valido
- **THEN** Quill resuelve el middleware asociado y lo incorpora al pipeline del request

### Requirement: Short-circuit semantics
Quill SHALL permitir que un middleware termine el pipeline devolviendo una respuesta sin invocar al siguiente middleware o al handler.

#### Scenario: Middleware returns response early
- **WHEN** un middleware decide devolver una respuesta antes de delegar
- **THEN** Quill finaliza el pipeline con esa respuesta y no ejecuta los componentes restantes

### Requirement: Explicit middleware failures
Quill MUST fallar de forma descriptiva cuando un middleware declarado dentro de la superficie soportada no cumpla el contrato requerido.

#### Scenario: Invalid middleware callable
- **WHEN** una aplicacion registra un middleware que no puede resolverse a un `MiddlewareInterface` valido
- **THEN** Quill produce un error explicito que identifica el middleware invalido
