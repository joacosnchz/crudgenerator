=================
English Doc
=================
This is the official documentation for **CRUDModsGeneratorBundle**

-----------------
Index
-----------------
#. Instalation
#. Usage
#. Notes

^^^^^^^^^^^^^^^^
Instalation
^^^^^^^^^^^^^^^^
#. Download the bundle, you can make this by two ways:
    #. Clone with git:
        ``$ git clone https://github.com/joacosnchz/ModsGeneratorBundle.git``
    #. Downloading on github:
        .. image:: download.png
#. Copy the ModsGeneratorBundle/ downloaded folder on /src/DSNEmpresas/ModsGenerator/ under your Symfony2 root.
#. Register the bundle on app/*AppKernel.php* on Symfony2:
    .. code-block:: php

        new DSNEmpresas\ModsGenerator\ModsGeneratorBundle\ModsGeneratorBundle(),
#. You are done.

^^^^^^^^^^^^^^^^
Usage
^^^^^^^^^^^^^^^^
You can use this bundle by two ways:

#. The most recommended is on the console:
    ``php app/console generate:crudmodules [name]``

    This is just another command of Symfony2's commands, where the **name** parameter must be like:
        ``[Bundle name]:[Entity name]``

    Like the way that Symfony search repositories, it must have this conditions:

    * Bundle name must be a bundle registered in *AppKernel.php*.
    * Entity name must be a bundle's entity, inside the *Entity/* folder of that bundle.

#. The second way is by web, this bundle includes its graphic version which is a little more complicated:
    * You will need to include the bundle routing at app/config/routing.yml:
        .. code-block:: yml

            mods_generator:
                resource: "@ModsGeneratorBundle/Resources/config/routing.yml"
                prefix:   /
    * To the bundle which you want to generate CRUD modules, you will need to give write permissons to the following directories Controller, Form, Resources, Resources/views, Resources/config.
    * Go to the url */modsgenerator/new* on syfmony and filling the form you are going to get the selected bundle's CRUD modules.

^^^^^^^^^^^^^^^^
Notes
^^^^^^^^^^^^^^^^
* This bundle needs you to have installed the following bundle:
    .. _PagerBundle: https://github.com/makerlabs/PagerBundle
    `PagerBundle`_
* This bundle generates backups of the following files if exist:
    #. [selected bundle]/Controller/ModsController.php
    #. [selected bundle]/Form/[selected entity]Type.php
    #. [selected bundle]/Resources/config/routing.yml
    #. [selected bundle]/Resources/views/CRUD/insert[enitdad seleccionada].html.twig
    #. [selected bundle]/Resources/views/CRUD/edit[enitdad seleccionada].html.twig
    #. [selected bundle]/Resources/views/CRUD/show[enitdad seleccionada].html.twig
    #. [selected bundle]/Resources/views/CRUD/showVacio.html.twig
* The backups are saved on the same directory of the file with the extension .bak
* The bundle generates the following urls:
    #. /[selected entity]/new - for the database persistence.
    #. /[selected entity]/edit/{id} - for the database record edition by id.
    #. /[selected entity]/show/{page} - for the entity record listing.
    #. /[selected entity]/delete/{id} - for the database record delete by id.