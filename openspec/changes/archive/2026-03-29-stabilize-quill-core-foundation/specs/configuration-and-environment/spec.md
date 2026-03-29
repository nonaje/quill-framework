## ADDED Requirements

### Requirement: Root-based path resolution
Quill SHALL resolver archivos de configuracion, rutas y recursos relativos a un `app root` explicito, sin depender de constantes globales implicitas.

#### Scenario: Resolve config path from application root
- **WHEN** la aplicacion solicita la ruta a un archivo de configuracion o de rutas usando el path resolver
- **THEN** la ruta resultante queda anclada al `app root` configurado para esa aplicacion

### Requirement: Deterministic configuration precedence
Quill MUST definir un orden de precedencia estable entre valores por defecto del framework, archivos de configuracion, variables de entorno y overrides en runtime.

#### Scenario: Resolve same key from multiple sources
- **WHEN** una misma clave esta definida en mas de una fuente soportada
- **THEN** Quill devuelve el valor de la fuente con mayor precedencia segun la regla documentada

### Requirement: Dot-notation configuration access
Quill SHALL ofrecer lectura consistente de configuracion mediante dot notation y devolver valores por defecto cuando una clave no exista.

#### Scenario: Read nested key with default
- **WHEN** un consumidor consulta una clave anidada inexistente con un valor por defecto
- **THEN** la configuracion devuelve exactamente el valor por defecto provisto

### Requirement: Normalized environment values
Quill MUST normalizar valores de `.env` para representar de forma consistente strings, booleanos, null y numeros soportados, y tolerar la ausencia del archivo cuando sea opcional.

#### Scenario: Load optional env file
- **WHEN** el archivo `.env` esperado no existe para una aplicacion
- **THEN** el bootstrap continua sin error y conserva los valores disponibles desde otras fuentes
