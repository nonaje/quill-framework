## 1. Foundation and bootstrap blockers

- [x] 1.1 Reemplazar la sintaxis PHP no soportada del core y fijar la version minima oficial de PHP en `composer.json`
- [x] 1.2 Introducir un bootstrap canonico con `QuillFactory` y un contexto de aplicacion explicito
- [x] 1.3 Reorganizar `Quill` para que el lifecycle del request quede definido por composicion y no por estado implicito
- [x] 1.4 Corregir el harness inicial de pruebas y agregar un smoke test de arranque contra la nueva API canonica
- [x] 1.5 Rebaselinar proposal, design, specs y README para reflejar el alcance viable de estabilizacion del core actual

## 2. Container and configuration core

- [x] 2.1 Redefinir el contenedor para soportar transients, singletons, refresh y errores tipados por instancia de aplicacion
- [x] 2.2 Implementar deteccion o manejo explicito de fallas de resolucion y ciclos de dependencias en el contenedor
- [x] 2.3 Rehacer el path resolver para que todo archivo dependa de un `app root` explicito y no de estado global
- [x] 2.4 Rehacer el repositorio de configuracion con dot notation y reglas claras de precedencia entre defaults, archivos, `.env` y overrides
- [x] 2.5 Agregar pruebas unitarias e integracion para contenedor, configuracion, `.env` y resolucion de paths

## 3. Parallel Track A - HTTP request and response core

- [x] 3.1 Introducir las abstracciones runtime propias de Quill para request, response y response emission
- [x] 3.2 Implementar acceso consistente a method, route params, query params, body parseado y request PSR subyacente
- [x] 3.3 Garantizar parsing no destructivo del body, incluyendo lectura JSON repetible dentro del mismo request
- [x] 3.4 Implementar helpers semanticos de response como `json`, `plain`, `html`, `status` y `headers`
- [x] 3.5 Agregar pruebas unitarias e integracion para request parsing, responses y emision final

## 4. Parallel Track B - Routing and dispatch

- [x] 4.1 Rehacer el registro de rutas para soportar verbos HTTP, grupos anidados y carga desde archivos con paths normalizados
- [x] 4.2 Implementar matching deterministico por metodo y path, con extraccion de parametros dinamicos por nombre
- [x] 4.3 Definir e implementar la politica de conflicto para rutas duplicadas
- [x] 4.4 Rehacer el dispatch para preservar el registro de rutas entre requests y ejecutar de forma confiable la superficie canonica basada en closures
- [x] 4.5 Agregar pruebas unitarias e integracion para verbos, grupos, route files, params y requests repetidos

## 5. Parallel Track C - Middleware execution and error handling

- [x] 5.1 Rehacer la resolucion de middlewares para aceptar la superficie canonica soportada del change
- [x] 5.2 Implementar el orden deterministico de middlewares globales, de grupo y de ruta, incluyendo short-circuiting
- [x] 5.3 Rehacer el error handler para mapear excepciones no controladas a respuestas controladas segun el entorno
- [x] 5.4 Permitir reemplazo por aplicacion de error handlers, middleware resolvers y componentes relacionados del pipeline
- [x] 5.5 Agregar pruebas unitarias e integracion para orden de middlewares, short-circuiting y respuestas de error

## 6. Application extensibility

- [x] 6.1 Garantizar que las capacidades publicas puedan usarse sin helpers globales obligatorios
- [x] 6.2 Hacer que los helpers globales, si se mantienen, deleguen a un contexto de aplicacion explicito y aislado por instancia
- [x] 6.3 Agregar pruebas para overrides por aplicacion y puntos de extension soportados dentro del alcance actual

## 7. Public API alignment and documentation

- [x] 7.1 Alinear `README.md` y ejemplos con el bootstrap canonico, las APIs reales y la estrategia de extensibilidad
- [x] 7.2 Eliminar o reemplazar APIs muertas, inconsistentes o ambiguas, documentando claramente los cambios breaking
- [x] 7.3 Revisar que cada capability implementada tenga sus pruebas asociadas y trazabilidad directa a sus escenarios OpenSpec
