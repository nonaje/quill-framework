## 1. Boundary Definition

- [x] 1.1 Inventariar que responsabilidades actuales pertenecen a `quill-framework` y cuales deben salir al futuro `quill-app`.
- [x] 1.2 Definir la API publica canonica del framework para bootstrap programatico, contratos y puntos de extension consumibles desde otro repositorio.

## 2. Framework Core Refactor

- [x] 2.1 Refactorizar `QuillFactory`, loaders y wiring relacionados para que el framework no asuma por defecto una estructura de proyecto final.
- [x] 2.2 Remover del core las dependencias HTTP concretas embebidas y dejar el runtime configurable desde la app consumidora.
- [x] 2.3 Ajustar requests, responses y emission para operar sobre contratos estables del framework sin acoplarlos a Nyholm dentro del paquete reusable.

## 3. App Boundary Readiness

- [x] 3.1 Actualizar tests para validar que `quill-framework` puede integrarse desde un composition root externo sin depender de convenciones internas de app.
- [x] 3.2 Actualizar README y documentacion tecnica para posicionar este repo como framework reusable y preparar la creacion de `quill-app` como starter oficial.
- [x] 3.3 Especificar el skeleton minimo y las responsabilidades iniciales del futuro repositorio `quill-app`.
