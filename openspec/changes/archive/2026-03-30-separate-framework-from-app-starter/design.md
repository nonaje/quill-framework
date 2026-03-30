## Context

Quill quiere evolucionar hacia un modelo parecido a `laravel/framework` y un starter app separado, pero hoy el repo actual sigue mezclando engine reusable, runtime HTTP concreto, convenciones de filesystem y bootstrap de una aplicacion final. Eso hace dificil exponer un `quill-framework` pequeno, estable y honesto, y deja decisiones como Nyholm, `config/`, `routes/` y el wiring web demasiado acopladas al core.

El objetivo de este cambio ya no es solamente desacoplar Nyholm, sino redefinir el paquete actual como framework reusable y trazar una frontera clara para un futuro `quill-app`. La principal restriccion es mantener un core utilizable mientras se remueven responsabilidades que pertenecen a la app starter, sin intentar crear en este mismo cambio un ecosistema de adapters o plugins mas amplio de lo necesario.

## Goals / Non-Goals

**Goals:**
- Definir una frontera clara entre `quill-framework` y `quill-app`.
- Mantener en el framework solo contratos, implementaciones base y bootstrap programatico reusable.
- Sacar del framework las dependencias y convenciones que correspondan a una aplicacion web concreta.
- Dejar al core listo para ser consumido desde otro repositorio sin tocar internals.

**Non-Goals:**
- Crear en este mismo cambio el repositorio `quill-app` completo.
- Diseñar un sistema generico de bridges o plugins multiplataforma desde el dia uno.
- Eliminar todas las dependencias externas del ecosistema PHP; el foco es separar framework de app, no reimplementar estandares.
- Mantener compatibilidad total con toda narrativa o helper previo si eso impide una frontera limpia entre framework y starter.

## Decisions

### Decision: Reframe the package as `quill-framework`
El paquete actual se tratara como el framework reusable. Su API publica debe describir capacidades base del engine y no asumir que el consumidor final vive dentro del mismo repositorio o comparte su estructura de carpetas.

Rationale:
- Alinea el producto con la meta de tener un repo `quill-app` separado.
- Reduce el riesgo de que detalles del starter se conviertan en contratos accidentales del framework.
- Hace mas facil versionar y documentar el core de forma independiente.

Alternatives considered:
- Mantener el paquete actual como framework y starter a la vez: descartado por seguir mezclando responsabilidades.
- Crear primero `quill-app` sin redefinir el core: descartado porque arrastraria los mismos acoplamientos actuales.

### Decision: Keep only programmatic bootstrap in the framework
`quill-framework` expondra un bootstrap programatico y reusable para construir aplicaciones, contenedores y servicios base. El bootstrap web concreto, el `public/index.php`, la carga opinionada de archivos y otras convenciones del proyecto final viviran en `quill-app`.

Rationale:
- Un framework reusable necesita una composicion explicita, no una estructura de proyecto fija.
- Permite que distintos consumers construyan su propia app sin copiar internals del core.
- Simplifica la frontera entre engine y starter.

Alternatives considered:
- Dejar `QuillFactory` con modo batteries-included y modo reusable a la vez: descartado por ambiguedad contractual.
- Eliminar cualquier factory del framework: descartado porque el core igual necesita un entry point programatico claro.

### Decision: Move concrete HTTP runtime choices to the starter app
El framework seguira dependiendo de contratos HTTP estables, pero la implementacion concreta por defecto del runtime, como Nyholm, pertenecera a la app starter o a wiring externo, no al corazon del framework.

Rationale:
- Nyholm deja de ser un detalle estructural del paquete reusable.
- La app starter puede optimizar DX sin contaminar el contrato del framework.
- Resuelve el desacople de runtime dentro de una narrativa mas amplia y consistente.

Alternatives considered:
- Mantener Nyholm dentro del framework pero oculto: descartado porque sigue dejando la dependencia concreta del lado equivocado de la frontera.
- Introducir ya un paquete bridge separado: diferido para mantener el alcance simple por ahora.

### Decision: Treat filesystem conventions as app concerns
Convenciones como `config/`, `routes/`, `.env`, `public/` y cualquier descubrimiento automatico opinionado se trataran como responsabilidades de `quill-app`, aunque el framework conserve loaders o utilidades base reutilizables.

Rationale:
- Estas convenciones pertenecen a la experiencia de una app final, no al contrato minimo del framework.
- Permite que otros consumers de `quill-framework` organicen su proyecto de otra manera.
- Evita que el core dependa de una unica forma de bootear o descubrir archivos.

Alternatives considered:
- Mantener convenciones por defecto en el framework y solo documentar que son opcionales: descartado porque seguirian siendo expectativas implicitas.

## Risks / Trade-offs

- [Separar framework y starter puede volver menos inmediata la DX del paquete actual] -> Mitigar con un bootstrap programatico claro y con la futura creacion de `quill-app` como consumidor oficial.
- [Mover Nyholm fuera del framework puede abrir preguntas sobre como testear y bootear HTTP en el corto plazo] -> Mitigar manteniendo contratos PSR estables y documentando un wiring minimo transitorio.
- [La frontera entre loaders base y convenciones de app puede quedar borrosa] -> Mitigar con una capability explicita de boundary y ownership claro en cada spec afectada.
- [Cambiar la narrativa publica puede romper ejemplos y expectativas actuales] -> Mitigar actualizando README, ejemplos y tests para reflejar el nuevo rol del paquete.

## Migration Plan

1. Actualizar specs y diseno para redefinir el paquete como framework reusable con un boundary explicito respecto a `quill-app`.
2. Refactorizar bootstrap, runtime HTTP y convenciones de filesystem para que el framework conserve solo lo reusable.
3. Ajustar tests y documentacion para validar el core sin asumir una app final embebida en el mismo repo.
4. Publicar o preparar despues un `quill-app` minimo que consuma el framework y provea la experiencia web opinionada.

Rollback strategy:
- Mientras el cambio no se archive, las convenciones actuales pueden mantenerse temporalmente detras de APIs internas mientras se estabiliza la nueva frontera.
- Si alguna responsabilidad resulta demasiado costosa de mover de una vez, puede quedar como detalle transitorio no canonico y diferirse a un follow-up.

## Open Questions

- Que subset exacto de `QuillFactory` debe permanecer como API publica del framework y que parte debe salir al starter.
- Si los loaders actuales de config y rutas deben permanecer en `quill-framework` como utilidades base o simplificarse aun mas.
- Cual sera la estructura minima y la estrategia de versionado del futuro repositorio `quill-app`.
