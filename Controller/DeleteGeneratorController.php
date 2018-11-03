<?php
namespace DSNEmpresas\ModsGenerator\ModsGeneratorBundle\Controller;

use DSNEmpresas\ModsGenerator\ModsGeneratorBundle\Controller\CodeGeneratorInterface;

class DeleteGeneratorController implements CodeGeneratorInterface {
    public $modsG;
    private $name = 'deleteAction';
    const IDENT = "\t";
    const DI = "\t\t";
    const TI = "\t\t\t";
    const CI = "\t\t\t\t";
    
    public function generateController($data) {        
        $fh = fopen($this->modsG->getBundlePath() . 'Controller/ModsController.php', 'a');
        
        fwrite($fh, PHP_EOL . self::IDENT . 'public function deleteAction($id) {' . PHP_EOL);
        fwrite($fh, self::DI . '$id2 = explode(\',\', $id);' . PHP_EOL);
        fwrite($fh, self::DI . 'foreach($id2 as $id2):' . PHP_EOL);
        fwrite($fh, self::TI . 'if($id2 != \'yes\'): // \'yes\' send the form when select all' . PHP_EOL);
        fwrite($fh, self::CI . '$em = $this->getDoctrine()->getManager();' . PHP_EOL);
        fwrite($fh, self::CI . '$object = $em->getRepository(\'' . $data['bundle'] . ':' . $data['entidad'] . '\')->find($id2);' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::CI . '$this->delete($object, $em);' . PHP_EOL);
        fwrite($fh, self::CI . '$this->get(\'session\')->getFlashBag()->add(' . PHP_EOL);
        fwrite($fh, self::CI . self::IDENT . '\'notice\', \'' . $this->translator->trans('delete', array(), 'domain') . '\'' . PHP_EOL . self::CI . ');' . PHP_EOL);
        fwrite($fh, self::TI . 'endif;' . PHP_EOL .  self::DI . 'endforeach;' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::DI . 'return $this->redirect($this->generateUrl(\'show' . $data['entidad'] . '\'));' . PHP_EOL);
        fwrite($fh, self::IDENT . '}' . PHP_EOL);
        
        fclose($fh);
    }
    
    public function generateView($data) {        
        
    }
    
    public function generateRouting($data) {
        $identation = '  '; // en yml no podemos identar con tabulador
        $fh = fopen($this->modsG->getBundlePath() . 'Resources/config/routing.yml', 'a');
        
        fwrite($fh, 'delete' . $data['entidad'] . ':' . PHP_EOL);
        fwrite($fh, $identation . 'pattern: /' . strtolower($data['entidad']) . '/delete/{id}' . PHP_EOL);
        fwrite($fh, $identation . 'defaults: { _controller: ' . $data['bundle'] . ':Mods:delete }' . PHP_EOL);
        
        fclose($fh);
    }
    
    public function getName() {
        return $this->name;
    }
 }