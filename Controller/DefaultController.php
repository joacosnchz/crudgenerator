<?php
/*
 * DefaultController.php
 * 
 * Copyright 2014 Joaquin Sanchez <jooaco.s@gmail.com>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 */
namespace DSNEmpresas\ModsGenerator\ModsGeneratorBundle\Controller;

# Kernel
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
# Entity
use DSNEmpresas\ModsGenerator\ModsGeneratorBundle\Entity\ModsGenerator;
# Objects
use DSNEmpresas\ModsGenerator\ModsGeneratorBundle\Controller\InsertGeneratorController;
use DSNEmpresas\ModsGenerator\ModsGeneratorBundle\Controller\EditGeneratorController;
use DSNEmpresas\ModsGenerator\ModsGeneratorBundle\Controller\ListGeneratorController;
use DSNEmpresas\ModsGenerator\ModsGeneratorBundle\Controller\DeleteGeneratorController;

class DefaultController extends Controller {
    protected $modsG;
    public $kernel;
    public $doctrine;
    public $translator;

    public function generate($data) {
        $this->populateModel($data['bundle']);
        $this->bundleStructure($data);
        $this->generateMods($data);
    }
    
    private function populateModel($bundle) {
        $mods = new ModsGenerator();
        
        $dir = explode('/src', __DIR__);
        $mods->setSymfDir($dir[0]);
        $mods->setBundlePath($this->kernel->locateResource('@' . $bundle));
        $this->modsG = $mods;
        
        return $mods;
    }

    private function generateMods($data) {        
        $this->createFormType($data);
        $this->createObjectManager();
        $this->createShowVacioViewFile();
        
        $modules = array(
                new InsertGeneratorController(), new EditGeneratorController(), new ListGeneratorController(), new DeleteGeneratorController());
        foreach($modules as $mod):
            $mod->modsG = $this->modsG;
            $mod->translator = $this->translator;
            $mod->doctrine = $this->doctrine;
            if($this->validModule($mod->getName())):
                $mod->generateController($data);
            endif;
            $mod->generateView($data);
            $mod->generateRouting($data);
        endforeach;
        
        $fh = fopen($this->modsG->getBundlePath() . 'Controller/ModsController.php', 'a');
        fwrite($fh, PHP_EOL . '}' . PHP_EOL); # Cerrar clase
        fclose($fh);
    }
    
    private function validModule($name) {
        $content = file($this->modsG->getBundlePath() . 'Controller/ModsController.php'); #separa en lineas
        
        for($i = 0;$i < count($content);$i++) {
            $words = explode(' ', $content[$i]); # separa en palabras
            for($j = 0;$j < count($words);$j++) {
                if(trim($words[$j]) == 'function') {
                    $func = explode('(', $words[$j+1]);
                    if(trim($func[0]) == $name) { # si la funcion insert ya existe
                        return false;
                    }
                }
            }
        }
        
        return true;
    }

    private function createFormType($data) {        
        $fh = fopen($this->modsG->getBundlePath() . 'Form/' . $data['entidad'] . 'Type.php', 'w');
        
        $dataClass1 = str_replace($this->modsG->getSymfDir() . '/src/', '', $this->modsG->getBundlePath());
        $dataClass2 = str_replace('/', '\\', $dataClass1);
        $dataClass = $dataClass2 . 'Entity\\' . $data['entidad'];
        $className1 = str_replace('\\', '_', $dataClass2);
        $className = strtolower($className1) . '_' . strtolower($data['entidad']) . 'type';
        $classMeta = $this->doctrine->getManager()->getMetadataFactory()->getMetaDataFor($dataClass);
        
        $identation = "\t";
        $dobleidentation = "\t\t";
        fwrite($fh, '<?php' . PHP_EOL);
        fwrite($fh, 'namespace DSNEmpresas\\' . $data['entidad'] . '\\' . $data['entidad'] . 'Bundle\Form;' . PHP_EOL);
        fwrite($fh, PHP_EOL . 'use Symfony\Component\Form\AbstractType;' . PHP_EOL);
        fwrite($fh, 'use Symfony\Component\Form\FormBuilderInterface;' . PHP_EOL);
        fwrite($fh, 'use Symfony\Component\OptionsResolver\OptionsResolverInterface;' . PHP_EOL);
        fwrite($fh, PHP_EOL . 'class ' . $data['entidad'] . 'Type extends AbstractType {' . PHP_EOL);
        fwrite($fh, PHP_EOL . $identation . 'public function buildForm(FormBuilderInterface $builder, array $options) {' . PHP_EOL);
        fwrite($fh, $dobleidentation . '$builder' . PHP_EOL);
        foreach($classMeta->fieldNames as $field):
            if(!$classMeta->isIdentifier($field)):
                fwrite($fh, $dobleidentation . $identation . '->add(\'' . $field . '\')' . PHP_EOL);
            endif;
        endforeach;
        fwrite($fh, $dobleidentation .  ';' . PHP_EOL . $identation . '}' . PHP_EOL);
        fwrite($fh, PHP_EOL . $identation . 'public function setDefaultOptions(OptionsResolverInterface $resolver) {' . PHP_EOL);
        fwrite($fh, $dobleidentation . '$resolver->setDefaults(array(' . PHP_EOL);
        fwrite($fh, $dobleidentation . $identation . '\'data_class\' => \'' . $dataClass . '\',' . PHP_EOL . $dobleidentation . '));' . PHP_EOL . $identation . '}' . PHP_EOL);
        fwrite($fh, PHP_EOL . $identation . 'public function getName() {' . PHP_EOL);
        fwrite($fh, $dobleidentation . 'return \''  . $className . '\';' . PHP_EOL);
        fwrite($fh, $identation . '}' . PHP_EOL . '}' . PHP_EOL . '?>' . PHP_EOL);
        
        fclose($fh);
    }
    
    # Controla los archivos y carpetas existentes del bundle y crea backups si es necesario
    private function bundleStructure($data) {        
        if(file_exists($this->modsG->getBundlePath() . 'Controller/ModsController.php')):
            copy($this->modsG->getBundlePath() . 'Controller/ModsController.php', $this->modsG->getBundlePath() . 'Controller/ModsController.php.bak');
            $this->adjustController();
        else:
            $fh = fopen($this->modsG->getBundlePath() . 'Controller/ModsController.php', 'w');
            $this->controllerHeader($fh, $data);
            fclose($fh);
        endif;
        
        if(file_exists($this->modsG->getBundlePath() . 'Form/' . $data['entidad'] . 'Type.php')):
            copy($this->modsG->getBundlePath() . 'Form/' . $data['entidad'] . 'Type.php', $this->modsG->getBundlePath() . 'Form/' . $data['entidad'] . 'Type.php.bak');
        endif;
        
        if(file_exists($this->modsG->getBundlePath() . 'Resources/config/routing.yml')):
            copy($this->modsG->getBundlePath() . 'Resources/config/routing.yml', $this->modsG->getBundlePath() . 'Resources/config/routing.yml.bak');
        endif;
        
        if(file_exists($this->modsG->getBundlePath() . 'Resources/views/CRUD/insert' . $data['entidad'] . '.html.twig')):
            rename($this->modsG->getBundlePath() . 'Resources/views/CRUD/insert' . $data['entidad'] . '.html.twig', $this->modsG->getBundlePath() . 'Resources/views/CRUD/insert' . $data['entidad'] . '.html.twig.bak');
        endif;
        
        if(file_exists($this->modsG->getBundlePath() . 'Resources/views/CRUD/show' . $data['entidad'] . '.html.twig')):
            rename($this->modsG->getBundlePath() . 'Resources/views/CRUD/show' . $data['entidad'] . '.html.twig', $this->modsG->getBundlePath() . 'Resources/views/CRUD/show' . $data['entidad'] . '.html.twig.bak');
        endif;
        
        if(file_exists($this->modsG->getBundlePath() . 'Resources/views/CRUD/edit' . $data['entidad'] . '.html.twig')):
            rename($this->modsG->getBundlePath() . 'Resources/views/CRUD/edit' . $data['entidad'] . '.html.twig', $this->modsG->getBundlePath() . 'Resources/views/CRUD/edit' . $data['entidad'] . '.html.twig.bak');
        endif;
        
        if(file_exists($this->modsG->getBundlePath() . 'Resources/views/CRUD/showVacio.html.twig')):
            rename($this->modsG->getBundlePath() . 'Resources/views/CRUD/showVacio.html.twig', $this->modsG->getBundlePath() . 'Resources/views/CRUD/showVacio.html.twig.bak');
        endif;
        
        if(!file_exists($this->modsG->getBundlePath() . 'Resources/views/CRUD/')):
            mkdir($this->modsG->getBundlePath() . 'Resources/views/CRUD/');
        endif;
    }
    
    private function adjustController() {
        $filename = $this->modsG->getBundlePath() . 'Controller/ModsController.php';
        $content = file($filename); # separa en lineas
        
        for($i = 0;$i < count($content);$i++) {
            $words = explode(' ', $content[$i]); # separa en palabras
            for($j = 0;$j < count($words);$j++) {
                if(trim($words[$j]) == 'MakerLabs\PagerBundle\Pager;') {
                    return;
                }
                if(trim($words[$j]) == 'class') { # cuando encuentra la palabra class (declaracion de la calse)
                    $content[$i] = $this->pagerHeader();
                    file_put_contents($filename, $content);
                }
            }
        }
    }
    
    # incluir header necesario
    private function pagerHeader() {
        $string = '# Pager - Paginador' . PHP_EOL;
        $string .= 'use MakerLabs\PagerBundle\Pager;' . PHP_EOL;
        $string .= 'use MakerLabs\PagerBundle\Adapter\ArrayAdapter;' . PHP_EOL;
        $string .= 'class ModsController extends ObjectManager {' . PHP_EOL;
        
        return $string;
    }
    
    private function controllerHeader($fh, $data) {
        $namespaceAux = str_replace($this->modsG->getSymfDir() . '/src/', '', $this->modsG->getBundlePath());
        // Es necesario poner doble barra invertida(\) para que se escriba una
        $namespace = str_replace('/', '\\', $namespaceAux);
        
        fwrite($fh, '<?php' . PHP_EOL);
        fwrite($fh,'namespace ' . $namespace . 'Controller;' . PHP_EOL);
        fwrite($fh,'use Symfony\Component\HttpFoundation\Request;' . PHP_EOL);
        fwrite($fh,'use ' . $namespace . 'Form\\' . $data['entidad'] . 'Type;' . PHP_EOL); // Es necesario poner doble barra invertida(\) para que se escriba una
        fwrite($fh,'use ' . $namespace . 'Entity\\' . $data['entidad'] . ';' . PHP_EOL); // Es necesario poner doble barra invertida(\) para que se escriba una
        fwrite($fh,'use ' . $namespace . 'Controller\\ObjectManager;' . PHP_EOL); // Es necesario poner doble barra invertida(\) para que se escriba una
        $pager = $this->pagerHeader();
        fwrite($fh, $pager);
    }
    
    private function createObjectManager() {
        $namespace = str_replace($this->modsG->getSymfDir() . '/src/', '', $this->modsG->getBundlePath());
        $namespace = str_replace('/', '\\', $namespace); // Es necesario poner doble barra invertida(\) para que se escriba una
        
        $this->createObjectManagerInterface($namespace);
        
        $identation = "\t";
        $dobidentation = "\t\t";
        
        $filename = $this->modsG->getBundlePath() . 'Controller/ObjectManager.php';
        $fh = fopen($filename, 'w');
        
        fwrite($fh, '<?php' . PHP_EOL . 'namespace ' . $namespace . 'Controller;' . PHP_EOL . PHP_EOL);
        fwrite($fh, 'use Symfony\Bundle\FrameworkBundle\Controller\Controller;' . PHP_EOL);
        fwrite($fh, 'use ' . $namespace . 'Controller\ObjectManagerInterface;' . PHP_EOL);
        fwrite($fh, 'use Doctrine\ORM\EntityManager;' . PHP_EOL . PHP_EOL);
        fwrite($fh, 'class ObjectManager extends Controller implements ObjectManagerInterface {' . PHP_EOL . PHP_EOL);
        fwrite($fh, $identation . 'public function insert($obj, EntityManager $em = null) {' . PHP_EOL);
        fwrite($fh, $dobidentation . '$em = $this->validateEm($em);' . PHP_EOL);
        fwrite($fh, $dobidentation . '$em->persist($obj);' . PHP_EOL);
        fwrite($fh, $dobidentation . '$em->flush();' . PHP_EOL . $identation . '}' . PHP_EOL . PHP_EOL);
        fwrite($fh, $identation . 'public function update(EntityManager $em = null) {' . PHP_EOL);
        fwrite($fh, $dobidentation . '$em = $this->validateEm($em);' . PHP_EOL);
        fwrite($fh, $dobidentation . '$em->flush();' . PHP_EOL . $identation . '}' . PHP_EOL . PHP_EOL);
        fwrite($fh, $identation . 'public function delete($obj, EntityManager $em = null) {' . PHP_EOL);
        fwrite($fh, $dobidentation . '$em = $this->validateEm($em);' . PHP_EOL . $dobidentation . '$em->remove($obj);' . PHP_EOL);
        fwrite($fh, $dobidentation . '$em->flush();' . PHP_EOL . $identation . '}' . PHP_EOL . PHP_EOL);
        fwrite($fh, $identation . 'public function validateEm(EntityManager $em = null) {' . PHP_EOL);
        fwrite($fh, $dobidentation . 'if(!$em):' . PHP_EOL . $dobidentation . $identation . '$em = $this->getDoctrine()->getManager();' . PHP_EOL);
        fwrite($fh, $dobidentation . 'endif;' . PHP_EOL . PHP_EOL . $dobidentation . 'return $em;' . $identation . PHP_EOL . $identation . '}' . PHP_EOL . '}' . PHP_EOL . '?>');
    }
    
    private function createObjectManagerInterface($namespace) {
        $identation = "\t";
        
        $filename = $this->modsG->getBundlePath() . 'Controller/ObjectManagerInterface.php';
        $fh = fopen($filename, 'w');
        
        fwrite($fh, '<?php' . PHP_EOL . 'namespace ' . $namespace . 'Controller;' . PHP_EOL . PHP_EOL);
        fwrite($fh, 'use Doctrine\ORM\EntityManager;' . PHP_EOL . PHP_EOL);
        fwrite($fh, 'interface ObjectManagerInterface {' . PHP_EOL);
        fwrite($fh, $identation . 'public function insert($obj, EntityManager $em);' . PHP_EOL . PHP_EOL);
        fwrite($fh, $identation . 'public function update(EntityManager $em);' . PHP_EOL . PHP_EOL);
        fwrite($fh, $identation . 'public function delete($obj, EntityManager $em);' . PHP_EOL . PHP_EOL);
        fwrite($fh, $identation . 'public function validateEm(EntityManager $em);' . PHP_EOL . '}' . PHP_EOL . '?>');
    }
    
    private function createShowVacioViewFile() {        
        $identation = "\t";
        
        $fh = fopen($this->modsG->getBundlePath() . 'Resources/views/CRUD/showVacio.html.twig', 'w');
        
        fwrite($fh, '{% block content %}' . PHP_EOL);
        fwrite($fh, '<table border="1" class="show" cellpadding=\'5px\'>' . PHP_EOL);
        fwrite($fh, $identation . '<tr><th colspan="5"><h3>Lista de {{ entity }}</h3></th></tr>' . PHP_EOL);
        fwrite($fh, '</table>' . PHP_EOL);
        fwrite($fh, PHP_EOL . '<p>En este momento no se han encontrado {{ entity }}</p>' . PHP_EOL);
        fwrite($fh, PHP_EOL . '<button onclick="window.location=\'{% if nuevo is defined %}{{ nuevo }}{% endif %}\'">' . $this->translator->trans('new', array(), 'indexView') . '</button>' . PHP_EOL);
        fwrite($fh, '<input type="button" value="Volver" onclick="javascript:window.history.go(-1);" id="volver">' . PHP_EOL);
        fwrite($fh, '{% endblock %}');
        
        fclose($fh);
    }
}