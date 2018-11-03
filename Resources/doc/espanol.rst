=================
Documentación español
=================
Esta es la documentación oficial para **CRUDModsGeneratorBundle**

-----------------
Indice
-----------------
#. Instalación
#. Modo de uso
#. Notas

^^^^^^^^^^^^^^^^
Instalacion
^^^^^^^^^^^^^^^^
#. Descargue el bundle, puede hacerlo de dos formas:
    #. Clonando con git:
        ``$ git clone https://github.com/joacosnchz/ModsGeneratorBundle.git``
    #. Descargando desde github:
        .. image:: download.png
#. Copie la carpeta ModsGeneratorBundle/ descargada a /src/DSNEmpresas/ModsGenerator/ en su carpeta de Symfony2.
#. Registre el bundle en app/*AppKernel.php* en Symfony2:
    .. code-block:: php

        new DSNEmpresas\ModsGenerator\ModsGeneratorBundle\ModsGeneratorBundle(),
#. Listo, bundle instalado.

^^^^^^^^^^^^^^^^
Modo de uso
^^^^^^^^^^^^^^^^
Puede usar este bundle de dos formas:

#. La mas recomendada es desde consola:
    ``php app/console generate:crudmodules [name]``

    Esto es un comando más de su consola de Symfony2, donde el parámetro **name** debe ser de la forma:
        ``[Nombre de bundle]:[Nombre de entidad]``
    De la misma forma con la cual Symfony busca repositorios, debe cumplir con las siguientes condiciones:

    * Nombre de bundle debe ser un bundle registrado en *AppKernel.php*.
    * Nombre de entidad debe ser una entidad del bundle, dentro de la carpeta *Entity/* de este bundle.

#. La segunda forma es por web, este bundle incluye su versión grafica la cual es algo más complicada.
    * Debe incluir el routing del bundle en app/config/routing.yml:
        .. code-block:: yml

            mods_generator:
                resource: "@ModsGeneratorBundle/Resources/config/routing.yml"
                prefix:   /
    * Al bundle del cual quiere generar los modulos ABM, debe dar permisos de escritura a los directorios Controller, Form, Resources, Resources/views, Resources/config.
    * Ingrese a la url */modsgenerator/new* en syfmony y completando el formulario obtendrá los modulos ABM para el bundle seleccionado.

^^^^^^^^^^^^^^^^
Notas
^^^^^^^^^^^^^^^^
* Este bundle necesita que se encuentre instalado el siguiente bundle:
    .. _PagerBundle: https://github.com/makerlabs/PagerBundle
    `PagerBundle`_
* Este bundle genera backups de los siguientes archivos si es que existen y los sobreescribe:
    #. [bundle seleccionado]/Controller/ModsController.php
    #. [bundle seleccionado]/Form/[entidad seleccionada]Type.php
    #. [bundle seleccionado]/Resources/config/routing.yml
    #. [bundle seleccionado]/Resources/views/CRUD/insert[enitdad seleccionada].html.twig
    #. [bundle seleccionado]/Resources/views/CRUD/edit[enitdad seleccionada].html.twig
    #. [bundle seleccionado]/Resources/views/CRUD/show[enitdad seleccionada].html.twig
    #. [bundle seleccionado]/Resources/views/CRUD/showVacio.html.twig
* Los backups se guardan en el mismo directorio donde están los archivos con extensión .bak
* Las url que crea el bundle son de la siguiente forma:
    #. /[entidad seleccionada]/new - para la persistencia a la base de datos.
    #. /[entidad seleccionada]/edit/{id} - para la edición de registros de la base de datos mediante su id.
    #. /[entidad seleccionada]/show/{page} - para el listado de los registros de la entidad.
    #. /[entidad seleccionada]/delete/{id} - para el borrado de registros de la base de datos mediante su id.