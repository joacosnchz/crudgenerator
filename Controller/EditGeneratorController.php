<?php
namespace DSNEmpresas\ModsGenerator\ModsGeneratorBundle\Controller;

use DSNEmpresas\ModsGenerator\ModsGeneratorBundle\Controller\CodeGeneratorInterface;

class EditGeneratorController implements CodeGeneratorInterface {
    public $modsG;
    private $name = 'showOneAction';
    const IDENT = "\t";
    const DI = "\t\t";
    const TI = "\t\t\t";
    const CI = "\t\t\t\t";
    
    public function generateController($data) {
        $fh = fopen($this->modsG->getBundlePath() . 'Controller/ModsController.php', 'a');
        
        fwrite($fh, PHP_EOL . self::IDENT . 'public function showOneAction($id, Request $request) {' . PHP_EOL);
        fwrite($fh, self::DI . '$id2 = explode(\',\', $id);' . PHP_EOL);
        fwrite($fh, self::DI . 'foreach($id2 as $id2):' . PHP_EOL);
        fwrite($fh, self::TI . 'if($id2 != \'yes\'): // \'yes\' send the form when select all' . PHP_EOL);
        fwrite($fh, self::CI . '$object = $this->getDoctrine()->getRepository(\'' . $data['bundle'] . ':' . $data['entidad'] . '\')->find($id2);' . PHP_EOL);
        fwrite($fh, self::TI . 'endif;' . PHP_EOL . self::TI . '$last = $id2;' . PHP_EOL .  self::DI . 'endforeach;' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::DI . '$formType = new ' . $data['entidad'] . 'Type();' . PHP_EOL);
        fwrite($fh, self::DI . '$form = $this->createForm($formType, $object, array(\'action\' => $this->generateUrl(\'edit' . $data['entidad'] . '\', array(\'id\' => $last,\'_format\' => \'html\')), \'method\' => \'POST\'));' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::DI . 'if($request->isMethod("POST")):' . PHP_EOL);
        fwrite($fh, self::TI . '$form->bind($request);' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::TI . 'if($form->isValid()):' . PHP_EOL);
        #fwrite($fh, self::CI . '$em = $this->getDoctrine()->getManager();' . PHP_EOL);
        fwrite($fh, self::CI . '$this->update();' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::CI . '$this->get(\'session\')->getFlashBag()->add(' . PHP_EOL);
        fwrite($fh, self::CI . self::IDENT . '\'notice\', \'' . $this->translator->trans('edition', array(), 'domain') . '\'' . PHP_EOL . self::CI . ');' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::CI . 'return $this->redirect($this->generateUrl(\'show' . $data['entidad'] . '\'));' . PHP_EOL);
        fwrite($fh, self::TI . 'endif;' . PHP_EOL . self::DI . 'endif;' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::DI . 'return $this->render(\'' . $data['bundle'] . ':CRUD:edit' . $data['entidad'] .  '.html.twig\', array(\'form\' => $form->createView(), \'' . $data['entidad'] . '\' => $object));');
        fwrite($fh, PHP_EOL . self::IDENT . '}' . PHP_EOL);
        
        fclose($fh);
    }
    
    public function generateView($data) {
        $fh = fopen($this->modsG->getBundlePath() . 'Resources/views/CRUD/edit' . $data['entidad'] . '.html.twig', 'w');
        
        fwrite($fh, '{% block content %}' . PHP_EOL);
        fwrite($fh, '<style>' . PHP_EOL);
        fwrite($fh, self::IDENT . '#required { color: red; }' . PHP_EOL);
        fwrite($fh, '</style>' . PHP_EOL);
        fwrite($fh, '{{ form_start(form) }}' . PHP_EOL);
        fwrite($fh, self::IDENT . '<table class="resp">' . PHP_EOL);
        fwrite($fh, self::DI . '<tr><th colspan="2"><h3>' . $this->translator->trans('edit', array(), 'indexView') . '</h3></th></tr>' . PHP_EOL);
        fwrite($fh, self::DI . '{% for form in form %}' . PHP_EOL);
        fwrite($fh, self::TI . '<tr><td>{{ form_label(form) }}</td><td>{{ form_widget(form) }}</td></tr>' . PHP_EOL);
        fwrite($fh, self::DI . '{% endfor %}' . PHP_EOL);
        fwrite($fh, self::IDENT . '</table>' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::IDENT . '<hr>' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::IDENT . $this->translator->trans('mandatory', array('%ast%' => '<span id="required">(*)</span>'), 'indexView') . PHP_EOL);
        fwrite($fh, self::IDENT . '<input type="submit" value="' . $this->translator->trans('submit', array(), 'indexView') . '"/>' . PHP_EOL);
        fwrite($fh, self::IDENT . '<input type="button" name="back" value="' . $this->translator->trans('cancel', array(), 'indexView') . '" onclick="window.location=\'{{ path(\'show' . $data['entidad'] . '\') }}\'" />');
        fwrite($fh, PHP_EOL . '{{ form_end(form) }}' . PHP_EOL);
        fwrite($fh, '{% endblock %}');
        
        fclose($fh);
    }
    
    public function generateRouting($data) {
        $identation = '  '; // en yml no podemos identar con tabulador
        $fh = fopen($this->modsG->getBundlePath() . 'Resources/config/routing.yml', 'a');
        
        fwrite($fh, 'edit' . $data['entidad'] . ':' . PHP_EOL);
        fwrite($fh, $identation . 'pattern: /' . strtolower($data['entidad']) . '/edit/{id}.{_format}' . PHP_EOL);
        fwrite($fh, $identation . 'defaults: { _controller: ' . $data['bundle'] . ':Mods:showOne }' . PHP_EOL);
        fwrite($fh, $identation . 'requirements:' . PHP_EOL);
        fwrite($fh, $identation . $identation . '_format: html' . PHP_EOL);
        
        fclose($fh);
    }
    
    public function getName() {
        return $this->name;
    }
 }