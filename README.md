El grupo  que desarolla esta aplicacion esta formado por ; 

Aitor Lopez Guerrero 

Andres Gomez Ocaña

Daniel Sánchez Ortega

David Zurkiewicz Redondo

Mario Álvarez Alba


La aplicaion que se va desarollar es ....


Los directorios que vamos a crear y para que se usan ;

.gitignore
le dice a Git qué archivos no debe subir nunca: secretos (.env, claves)
dependencias descargadas (node_modules/), archivos del sistema, logs y bases de datos locales.
Es lo mas importante para no  filtrar contraseñas

.env.example
plantilla de configuracion.Lista las variables que necesita la app sin valores reales. 
Se sube para que entre nosotoros sepamos qué rellenar; 
cada uno lo copia como .env con sus valores,
y ese .env no se sube.



CONTRIBUTING.md
las normas de trabajo del equipo: cómo se llaman las ramas, 
cómo se escriben los commits y cómo se revisa el código

SECURITY.md

documentamos las medidas de seguridad que aplicais y como reportar un fallo.
Es donde justificaremos nuestra securización


 ## Instalación
1. Clonar el repositorio
2. Copiar `.env.example` como `.env` y rellenar los valores
3. (instalación de dependencias — pendiente)

## Ejecución
(pendiente)

## Estructura del proyecto
- `src/` → código de la aplicación
- `tests/` → pruebas
- `docs/` → documentación, diagramas e informes

## Forma de trabajo
Ver [CONTRIBUTING.md](CONTRIBUTING.md).

## Seguridad
Ver [SECURITY.md](SECURITY.md).
