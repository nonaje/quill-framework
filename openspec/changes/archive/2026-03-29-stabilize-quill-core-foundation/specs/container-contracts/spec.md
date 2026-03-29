## ADDED Requirements

### Requirement: Deterministic service registration
El contenedor SHALL soportar registro de servicios transitorios y resolver una nueva instancia cada vez que el binding no sea singleton.

#### Scenario: Resolve transient binding twice
- **WHEN** un servicio transitorio se resuelve dos veces desde el mismo contenedor
- **THEN** cada resolucion devuelve una instancia distinta

### Requirement: Singleton lifecycle and refresh
El contenedor MUST soportar singletons cacheados por instancia de aplicacion y una operacion de refresh que reemplace su resolver y reinicie el valor previamente cacheado.

#### Scenario: Refresh singleton binding
- **WHEN** un singleton ya resuelto es refrescado con un nuevo resolver
- **THEN** la siguiente resolucion devuelve la nueva instancia y no reutiliza el valor anterior

### Requirement: Explicit resolution failures
El contenedor MUST producir errores tipados y descriptivos cuando un servicio no exista, no pueda resolverse o incurra en un ciclo de dependencias.

#### Scenario: Resolve unknown service
- **WHEN** un consumidor intenta resolver un identificador no registrado
- **THEN** el contenedor lanza una excepcion especifica de servicio no encontrado

### Requirement: Application-level overrides
El contenedor SHALL permitir overrides por aplicacion sin contaminar otras instancias de Quill ni depender de estado global compartido.

#### Scenario: Isolated bindings per application
- **WHEN** dos aplicaciones registran implementaciones distintas bajo el mismo identificador
- **THEN** cada aplicacion resuelve su propia implementacion sin afectar a la otra
