## Why

Quill tiene una base prometedora, pero hoy su nucleo no ofrece garantias suficientes de compilacion, arranque, estabilidad contractual ni extensibilidad para ser usado con confianza como framework. Este cambio redefine el alcance a una estabilizacion operativa del core actual de Quill sobre su runtime PSR-7/Nyholm existente, priorizando un bootstrap canonico, contratos claros, comportamiento deterministico y documentacion honesta.

## What Changes

- Establecer un kernel canonico y especificado para el arranque de la aplicacion, el ciclo de request/response y la composicion de servicios.
- Formalizar el contrato del contenedor, la configuracion, el enrutamiento, la ejecucion de middlewares, el manejo de errores y la API publica minima soportada.
- Definir el comportamiento HTTP esperado de requests, responses y emision sobre el runtime actual, dejando fuera de este change la independencia total respecto de terceros.
- Separar las capacidades en especificaciones independientes para que cada una tenga sus propios escenarios, criterios de aceptacion y pruebas unitarias/integracion asociadas.
- **BREAKING** Reemplazar o retirar de la API canonica las superficies ambiguas, incompletas o inconsistentes para dejar una base coherente y documentada.

## Capabilities

### New Capabilities
- `application-bootstrap`: Define el composition root, el lifecycle de la aplicacion y el contrato de arranque de Quill.
- `container-contracts`: Define el comportamiento observable del contenedor, sus errores y sus puntos de extension.
- `configuration-and-environment`: Define carga de configuracion, `.env`, resolucion de paths y precedencia entre fuentes.
- `routing-and-dispatch`: Define registro de rutas, normalizacion de paths, matching, params y dispatch sin efectos colaterales inesperados.
- `middleware-execution`: Define resolucion, orden y extension de middlewares globales, de grupo y de ruta sobre la superficie actualmente soportada.
- `http-request-response`: Define las abstracciones HTTP del framework, el parsing de input, la construccion de responses y la emision.
- `error-handling`: Define conversion de excepciones no controladas a respuestas observables para desarrollo y produccion.
- `application-extensibility`: Define los puntos minimos de extension por aplicacion que quedan dentro del alcance operativo de este change.

### Modified Capabilities

None.

## Impact

- Afecta el nucleo completo de `src/`, especialmente `src/Quill.php`, `src/Support/GlobalFunctions.php`, `src/Container/*`, `src/Configuration/*`, `src/Router/*`, `src/Middleware/*`, `src/Handler/*`, `src/Request/*`, `src/Response/*` y `src/Loaders/*`.
- Afecta la API publica documentada en `README.md`, el bootstrap esperado por tests y la organizacion futura de pruebas por capability.
- Mantiene por ahora las dependencias runtime actuales declaradas en `composer.json` como parte explicita del alcance de este change.
- Introduce una base contractual para que futuras implementaciones mantengan principios SOLID, nombres semanticos, alta testeabilidad y extensibilidad para usuarios del framework.
