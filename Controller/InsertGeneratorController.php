<?php
namespace DSNEmpresas\ModsGenerator\ModsGeneratorBundle\Controller;

use DSNEmpresas\ModsGenerator\ModsGeneratorBundle\Controller\CodeGeneratorInterface;

class InsertGeneratorController implements CodeGeneratorInterface {
    public $modsG;
    private $name = 'insertAction';
    const IDENT = "\t";
    const DI = "\t\t";
    const TI = "\t\t\t";
    const CI = "\t\t\t\t";
    
    public function generateController($data) {        
        $fh = fopen($this->modsG->getBundlePath() . 'Controller/ModsController.php', 'a');
        
        fwrite($fh, PHP_EOL . self::IDENT . 'public function insertAction(Request $request) {' . PHP_EOL);
        fwrite($fh, self::DI . '$object = new ' . $data['entidad'] . '();' . PHP_EOL);
        fwrite($fh, self::DI . '$formType = new ' . $data['entidad'] . 'Type();' . PHP_EOL);
        fwrite($fh, self::DI . '$form = $this->createForm($formType, $object, array(\'action\' => $this->generateUrl(\'insert' . $data['entidad'] . '\'), \'method\' => \'POST\'));' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::DI . 'if($request->isMethod("POST")):' . PHP_EOL);
        fwrite($fh, self::TI . '$form->bind($request);');
        fwrite($fh, PHP_EOL . self::TI . 'if($form->isValid()):' . PHP_EOL);
        #fwrite($fh, self::CI . '$em = $this->getDoctrine()->getManager();' . PHP_EOL);
        fwrite($fh, self::CI . '$this->insert($object);' . PHP_EOL);
        #fwrite($fh, self::CI . '$em->flush();' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::CI . '$this->get(\'session\')->getFlashBag()->add(' . PHP_EOL);
        fwrite($fh, self::CI . self::IDENT . '\'notice\', \'' . $this->translator->trans('persistence', array(), 'domain') . '\'' . PHP_EOL);
        fwrite($fh, self::CI . ');' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::CI . 'return $this->redirect($this->generateUrl(\'show' . $data['entidad'] . '\'));' . PHP_EOL);
        fwrite($fh, self::TI . 'endif;' . PHP_EOL);
        fwrite($fh, self::DI . 'endif;' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::DI . 'return $this->render(\'' . $data['bundle'] . ':CRUD:insert' . $data['entidad'] . '.html.twig\', array(\'form\' => $form->createView()));' . PHP_EOL);
        fwrite($fh, self::IDENT . '}' . PHP_EOL);
        
        fclose($fh);
    }
    
    public function generateView($data) {        
        $fh = fopen($this->modsG->getBundlePath() . 'Resources/views/CRUD/insert' . $data['entidad'] . '.html.twig', 'w');
        
        fwrite($fh, '{% block content %}' . PHP_EOL);
        fwrite($fh, '<style>' . PHP_EOL);
        fwrite($fh, self::IDENT . '#required { color: red; }' . PHP_EOL);
        fwrite($fh, '</style>' . PHP_EOL);
        fwrite($fh, PHP_EOL . '{{ form_start(form) }}' . PHP_EOL);
        fwrite($fh, '<table>' . PHP_EOL);
        fwrite($fh, self::IDENT . '{% for form in form %}' . PHP_EOL);
        fwrite($fh, self::DI . '<tr><td>{{ form_label(form) }}</td><td>{{ form_widget(form) }}</td></tr>' . PHP_EOL);
        fwrite($fh, self::IDENT . '{% endfor %}' . PHP_EOL);
        fwrite($fh, '</table>' . PHP_EOL);
        fwrite($fh, PHP_EOL . '<hr>' . PHP_EOL);
        fwrite($fh, self::IDENT . $this->translator->trans('mandatory', array('%ast%' => '<span id="required">(*)</span>'), 'indexView') . PHP_EOL);
        fwrite($fh, '<input type="submit" value="' . $this->translator->trans('submit', array(), 'indexView') . '"/>' . PHP_EOL);
        fwrite($fh, '<input type="button" name="back" value="' . $this->translator->trans('cancel', array(), 'indexView') . '" onclick="window.location=\'{{ path(\'show' . $data['entidad'] . '\') }}\'" />');
        fwrite($fh, PHP_EOL . '{{ form_end(form) }}' . PHP_EOL);
        fwrite($fh, '{% endblock %}' . PHP_EOL);
        
        fclose($fh);
    }
    
    public function generateRouting($data) {
        $identation = '  '; // en yml no podemos identar con tabulador
        
        $fh = fopen($this->modsG->getBundlePath() . 'Resources/config/routing.yml', 'a');
        
        fwrite($fh, 'insert' . $data['entidad'] . ':' . PHP_EOL);
        fwrite($fh, $identation . 'pattern: /' . strtolower($data['entidad']) . '/new' . PHP_EOL);
        fwrite($fh, $identation . 'defaults: { _controller: ' . $data['bundle'] . ':Mods:insert }' . PHP_EOL);
        
        fclose($fh);
    }
    
    public function getName() {
        return $this->name;
    }
 }