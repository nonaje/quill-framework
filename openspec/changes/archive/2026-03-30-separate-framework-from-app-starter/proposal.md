## Why

Quill hoy mezcla en un mismo paquete el framework reusable, el runtime HTTP concreto y las convenciones de una aplicacion final, lo que dificulta exponer una base limpia para otros repositorios y hace que dependencias como Nyholm se filtren al core. Este cambio redefine el alcance para separar `quill-framework` de un futuro `quill-app`, dejando al framework como nucleo reusable orientado a contratos y a la app como starter oficial para proyectos web.

## What Changes

- Redefinir el core para que `quill-framework` exponga contratos, bootstrap programatico e implementaciones base sin asumir la estructura de una aplicacion final.
- Mover fuera del core las decisiones de runtime HTTP concreto, filesystem bootstrap y convenciones de proyecto que pertenezcan al futuro `quill-app`.
- Desacoplar el framework de clases concretas de Nyholm para que cualquier runtime PSR compatible quede del lado de la app starter.
- Formalizar el boundary entre framework reusable y starter app, incluyendo que responsabilidades, configuraciones y puntos de extension viven en cada lado.
- **BREAKING** Cambiar la API canonica y la narrativa publica de Quill para que el paquete actual represente el framework reusable y no un starter web batteries-included.

## Capabilities

### New Capabilities

- `framework-app-boundary`: define la frontera contractual entre `quill-framework` y `quill-app`, incluyendo ownership de bootstrap, runtime, convenciones de proyecto y puntos de extension.

### Modified Capabilities

- `application-bootstrap`: el framework debe ofrecer un bootstrap programatico reusable, mientras que el arranque web/filesystem de una app concreta queda fuera del core.
- `configuration-and-environment`: el framework mantiene capacidades base de configuracion, pero las convenciones de `config/`, `.env` y descubrimiento de archivos por defecto pasan a ser responsabilidad de la app starter.
- `http-request-response`: request, response, streams y emission deben operar sobre contratos y no sobre implementaciones Nyholm embebidas en el framework.
- `extensibility-and-runtime-independence`: una app debe poder componer el runtime HTTP y otros servicios del framework desde afuera sin tocar internals.

## Impact

- Afecta `src/Factory/QuillFactory.php`, `src/Quill.php`, `src/Response/*`, `src/Router/*`, loaders/configuracion y la documentacion publica en `README.md`.
- Cambia la narrativa del paquete actual para posicionarlo como framework reusable y prepara la aparicion de un repositorio `quill-app` como consumidor oficial.
- Puede requerir reducir o mover dependencias runtime concretas del `composer.json` del framework hacia la app starter.
- Impacta ejemplos, tests y decisiones de bootstrap que hoy asumen que framework y app viven en el mismo paquete.
