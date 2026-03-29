## Context

Quill necesita pasar de prototipo prometedor a framework pequeno y confiable. Hoy el arranque, el runtime HTTP, el enrutamiento, el pipeline de middlewares y varios helpers publicos no tienen contratos suficientemente claros, y el codigo actual mezcla estado global, wiring implicito y dependencias externas concretas en el camino critico.

Este cambio define la base tecnica para reorganizar Quill alrededor de capacidades pequenas y explicitamente especificadas. Los principales interesados son el mantenedor del framework y los usuarios que necesitan una API publica predecible, extensible y testeable.

Las restricciones principales son:
- Este change debe dejar estable y usable el core actual basado en PSR-7/Nyholm, sin prometer aun independencia total de runtime.
- Cada capability debe incluir escenarios que deriven en pruebas unitarias y de integracion, sin crear una spec de testing separada.
- La API publica debe favorecer nombres semanticos, extensibilidad por composicion y aislamiento por instancia de aplicacion.

## Goals / Non-Goals

**Goals:**
- Definir un composition root unico y estable para Quill.
- Separar contratos de bootstrap, contenedor, configuracion, routing, middleware, HTTP, errores y extensibilidad.
- Garantizar que cada capability tenga escenarios verificables y trazables a pruebas.
- Hacer explicitos los puntos de extension minimos y soportados para usuarios del framework.

**Non-Goals:**
- Reimplementar de una vez todos los estandares PSR o RFC fuera del camino critico del MVP.
- Remover en este branch las dependencias runtime obligatorias de terceros del core.
- Mantener compatibilidad total con APIs ambiguas o inconsistentes si eso impide estabilizar el nucleo.
- Definir features de alto nivel ajenas al core, como ORM, colas, templates avanzados o autenticacion.

## Decisions

### Decision: Organize the change by capabilities
La propuesta se divide en capacidades pequenas y separadas para que cada spec describa un contrato observable, sus escenarios y sus pruebas asociadas.

Rationale:
- Reduce el riesgo de mezclar decisiones de bootstrap con detalles de HTTP o routing.
- Permite implementar por fases o por sub-agentes especializados.
- Facilita archivar, revisar y evolucionar capacidades sin perder trazabilidad.

Alternatives considered:
- Unica spec monolitica para todo el framework: descartada por baja mantenibilidad.
- Dividir por carpetas actuales de `src/`: descartada porque replica una estructura actual que todavia no representa limites claros del dominio.

### Decision: Stabilize the current PSR-backed runtime first
Este change estabiliza el runtime actual basado en PSR-7/Nyholm en lugar de reemplazarlo. La independencia total respecto de terceros y los adapters opcionales quedaran para un cambio posterior.

Rationale:
- Reduce el alcance de este change a una base operativa, verificable y documentada.
- Evita mezclar estabilizacion del core con una reimplementacion profunda del runtime HTTP.
- Permite preparar follow-ups mas pequenos y honestos para independencia de runtime y adapters.

Alternatives considered:
- Intentar remover Nyholm/PSR en este mismo change: descartado por costo, riesgo y falta de foco para dejar el core operativo.
- Mantener la narrativa original de independencia total aunque no se implemente: descartado por desalinear spec y codigo.

### Decision: Establish a single composition root and explicit application context
Quill tendra un punto de arranque canonico, documentado y extensible. Los helpers globales, si se mantienen, seran una capa opcional sobre un contexto de aplicacion explicito y ya inicializado.

Rationale:
- Evita estado global implicito como mecanismo principal de coordinacion.
- Facilita pruebas, aplicaciones multiples y overrides por instancia.
- Hace mas claro donde se registran servicios, lifecycle y adapters.

Alternatives considered:
- Seguir usando un singleton global como unico punto de verdad: descartado por acoplamiento y baja aislacion.
- Eliminar por completo todo helper global: diferido; puede hacerse luego si la DX no se resiente.

### Decision: Make dispatch deterministic and side-effect free
El pipeline del request no debe mutar el registro de rutas ni depender de ordenes implicitos no documentados. Las rutas, middlewares y errores deben seguir reglas deterministicas y observables.

Rationale:
- Simplifica debugging y testing.
- Evita errores de reentrancia o comportamiento diferente entre requests consecutivos.
- Permite validar el orden de ejecucion como parte de la especificacion.

Alternatives considered:
- Mantener optimizaciones por mutacion del estado interno durante el dispatch: descartado por fragilidad.

### Decision: Keep tests attached to each capability
Cada capability definira escenarios que se traduciran en pruebas unitarias y/o de integracion dentro de su misma implementacion. No se creara una capability de testing separada.

Rationale:
- Mantiene juntas la intencion funcional y su verificacion.
- Evita una fase de tests desconectada del comportamiento que se pretende garantizar.
- Permite paralelizar implementacion y pruebas por area.

Alternatives considered:
- Una spec exclusiva para tests: descartada por duplicar el trabajo y perder contexto.

## Risks / Trade-offs

- [Reescribir el core runtime aumenta el alcance] -> Mitigar con fases pequenas, adapters opcionales y criterios de aceptacion por capability.
- [Cambiar APIs publicas puede romper ejemplos y apps existentes] -> Mitigar con una API canonica clara, documentacion actualizada y aliases solo cuando sean baratos y valiosos.
- [Separar demasiadas capacidades puede generar solapamientos] -> Mitigar con ownership claro de cada capability y decisiones de frontera explicitas en esta design.
- [Mantener superficie legacy fuera de la API canonica puede generar confusion] -> Mitigar documentando claramente que helpers/globales o resolutores legacy quedan fuera del contrato publico estable.

## Migration Plan

1. Definir y aprobar las capacidades y decisiones base.
2. Implementar primero compilacion estable, bootstrap y composition root.
3. Implementar despues request/response, routing, middleware y errores sobre contratos ya fijados.
4. Actualizar tests por capability a medida que cada area quede estable.
5. Revisar y simplificar la API canonica, los helpers opcionales y la documentacion publica al final.

Rollback strategy:
- Mientras el cambio no se archive, cualquier implementacion puede mantenerse encapsulada en la rama de cambio.
- Si algun punto de extension o helper no queda suficientemente estable, se retira del contrato canonico y se difiere a un cambio posterior.

## Open Questions

- Cual sera la version minima oficial de PHP para el nuevo core estable.
- Como se separara en cambios posteriores la independencia de runtime y la compatibilidad opcional.
- Si los helpers globales seguiran distribuyendose por defecto o quedaran como azucar sintactica opt-in.
