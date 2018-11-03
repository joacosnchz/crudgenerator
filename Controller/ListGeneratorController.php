<?php
namespace DSNEmpresas\ModsGenerator\ModsGeneratorBundle\Controller;

use DSNEmpresas\ModsGenerator\ModsGeneratorBundle\Controller\CodeGeneratorInterface;

class ListGeneratorController implements CodeGeneratorInterface {
    public $modsG;
    public $doctrine;
    private $name = 'showAction';
    const IDENT = "\t";
    const DI = "\t\t";
    const TI = "\t\t\t";
    const CI = "\t\t\t\t";
    
    public function generateController($data) {
        $fh = fopen($this->modsG->getBundlePath() . 'Controller/ModsController.php', 'a');
        
        fwrite($fh, PHP_EOL . self::IDENT . 'public function showAction($page, Request $request) {' . PHP_EOL);
        fwrite($fh, self::DI . '$object = $this->getDoctrine()->getRepository(\'' . $data['bundle'] . ':' . $data['entidad'] . '\')->findAll();' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::DI . 'if(!$object):' . PHP_EOL);
        fwrite($fh, self::TI . 'return $this->render(\'' . $data['bundle'] . ':CRUD:showVacio.html.twig\', array(\'entity\' => \'' . $data['entidad'] . '\', \'nuevo\' => $this->generateUrl(\'insert' . $data['entidad'] . '\')));' . PHP_EOL);
        fwrite($fh, self::DI . 'endif;' . PHP_EOL . PHP_EOL . self::DI . '$session = $this->get(\'session\');' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::DI . 'if($session->get(\'' . $data['entidad'] . '\') == null):' . PHP_EOL );
        fwrite($fh, self::TI . '$session->set(\'' . $data['entidad'] . '\', 10);' . PHP_EOL);
        fwrite($fh, self::DI . 'endif;' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::DI . 'if($request->request->get(\'' . $data['entidad'] . '\') != null):' . PHP_EOL);
        fwrite($fh, self::TI . '$session->set(\'' . $data['entidad'] . '\', $request->request->get(\'' . $data['entidad'] . '\'));' . PHP_EOL);
        fwrite($fh, self::DI . 'endif;' . PHP_EOL . PHP_EOL . self::DI . '$adapter = new ArrayAdapter($object);' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::DI . '$pager = new Pager($adapter, array(\'page\' => $page, \'limit\' => $session->get(\'' . $data['entidad'] . '\')));' . PHP_EOL);
        fwrite($fh, PHP_EOL . self::DI . 'return $this->render(\'' . $data['bundle'] . ':CRUD:show' . $data['entidad'] . '.html.twig\', array(\'pager\' => $pager));' . PHP_EOL . self::IDENT . '}' . PHP_EOL);
        
        fclose($fh);
    }
    
    public function generateView($data) {
        $fh = fopen($this->modsG->getBundlePath() . 'Resources/views/CRUD/show' . $data['entidad'] . '.html.twig', 'w');
        
        $dataClass1 = str_replace($this->modsG->getSymfDir() . '/src/', '', $this->modsG->getBundlePath());
        $dataClass2 = str_replace('/', '\\', $dataClass1);
        $dataClass = $dataClass2 . 'Entity\\' . $data['entidad'];
        
        $classMeta = $this->doctrine->getManager()->getMetadataFactory()->getMetaDataFor($dataClass);
        
        fwrite($fh, '{% block content %}' . PHP_EOL);
        fwrite($fh, '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">' . PHP_EOL);
        fwrite($fh, '<script src="//code.jquery.com/jquery-1.10.2.js"></script>' . PHP_EOL);
        fwrite($fh, '<script src="//code.jquery.com/ui/1.11.0/jquery-ui.js"></script>' . PHP_EOL);
        fwrite($fh, '{% for flashMessage in app.session.flashbag.get(\'notice\') %}' . PHP_EOL);
        fwrite($fh, self::IDENT . '<div class="flash-notice"><div id="success">' . PHP_EOL . self::DI . '{{ flashMessage }}');
        fwrite($fh, self::IDENT . '</div></div>' . PHP_EOL . '{% endfor %}' . PHP_EOL);
        fwrite($fh, PHP_EOL . '<span id="message"></span>' . PHP_EOL);
        fwrite($fh, PHP_EOL . '<table cellpadding="5"><tr><th colspan="5"><h3>' . $this->translator->trans('list', array(), 'indexView') . '</h3></th></tr></table>' . PHP_EOL);
        fwrite($fh, '<fieldset>' . PHP_EOL . self::IDENT . '<legend>' . $this->translator->trans('tools', array(), 'indexView') . '</legend>' . PHP_EOL . self::IDENT . '<table border="0">' . PHP_EOL . self::DI . '<tr>' . PHP_EOL . self::TI . '<td><button onclick="window.location=\'{{ path(\'insert' . $data['entidad'] . '\') }}\'" />' . $this->translator->trans('new', array(), 'indexView') . '</button></td>' . PHP_EOL);
        fwrite($fh, self::TI . '<td><button id="borrar">' . $this->translator->trans('delete', array(), 'indexView') . '</button></td>' . PHP_EOL);
        fwrite($fh, self::TI . '<td><button id="editar">' . $this->translator->trans('edit', array(), 'indexView') . '</button></td>');
        fwrite($fh, self::DI . '</tr>' . PHP_EOL . self::IDENT . '</table>' . PHP_EOL . '</fieldset>');
        fwrite($fh, '<form name="list00" id="myform" action="#">' . PHP_EOL);
        fwrite($fh, self::IDENT . '<table rules="rows" border="1" class="listado" bordercolor="#808080" frame="below" cellpadding="5" name="table">' . PHP_EOL);
        fwrite($fh, self::DI . '<tr><td>#</td><td><input type="checkbox" name="Check_ctr" value="yes" onClick="checkAll(document.list00, this)"></td>');
        foreach($classMeta->fieldNames as $field):
            if(!$classMeta->isIdentifier($field)):
                fwrite($fh, '<td>' . str_replace('_', ' ', ucfirst($field)) . '</td>');
            endif;
        endforeach;
        fwrite($fh, '</tr>' . PHP_EOL . self::DI . '{% for item in pager.getResults %}' . PHP_EOL . self::TI . '<tr>' . PHP_EOL);
        fwrite($fh, self::CI . '<td>{{ loop.index }}</td>' . PHP_EOL);
        foreach($classMeta->fieldNames as $field):
            $fieldAux = $field;
            $field = $this->getFieldMethod($field);
            if($classMeta->isIdentifier($fieldAux)):
                fwrite($fh, self::CI . '<td><input type="checkbox" name="link[]" value="{{ attribute(item, \'' . $field . '\') }}" id="{{ attribute(item, \'' . $field . '\') }}" class="tf"></td>' . PHP_EOL);
            else:
                fwrite($fh, self::CI . '<td>' . '{{ attribute(item, \'' . $field . '\') }}' . '</td>' . PHP_EOL);
            endif;
        endforeach;
        fwrite($fh, self::TI . '</tr>' . PHP_EOL . self::DI . '{% endfor %}' . PHP_EOL . self::IDENT . '</table>' . PHP_EOL . '</form>');
        fwrite($fh, PHP_EOL . $this->translator->trans('show', array(), 'indexView') . ': <form name="' . $data['entidad'] . '" action="{{ path(\'show' . $data['entidad'] . '\') }}" method="POST"><input id="' . $data['entidad'] . '" name="' . $data['entidad'] . '" value="{{ app.session.get(\'' . $data['entidad'] . '\') }}" size="1" maxlength="3"></form>' . PHP_EOL);
        fwrite($fh, PHP_EOL . '<div id="dialog-confirm" title="' . $this->translator->trans('conf-title', array(), 'indexView') . '">' . PHP_EOL);
        fwrite($fh, self::IDENT . '<p>' . $this->translator->trans('confirm', array(), 'indexView') . '</p>' . PHP_EOL . '</div>' . PHP_EOL);
        fwrite($fh, PHP_EOL . '<div class="wrapper-pages">' . PHP_EOL);
        fwrite($fh, self::IDENT . '{% if pager.isPaginable %}' . PHP_EOL);
        fwrite($fh, self::DI . '{{ paginate(pager, \'show' . $data['entidad'] . '\') }}' . PHP_EOL);
        fwrite($fh, self::IDENT . '{% endif %}' . PHP_EOL . '</div>' . PHP_EOL);
        fwrite($fh, '{% endblock %}' . PHP_EOL);
        
        fwrite($fh, '{% block js %}' . PHP_EOL);
        $this->writeJs($fh, $data);
        fwrite($fh, PHP_EOL . '{% endblock %}' . PHP_EOL);
        
        fclose($fh);
    }
    
    private function getFieldMethod($field) {
        if(strpos($field, '_')):
            $long = strlen($field);
            $splittedString = str_split($field);
            for($i = 0;$i < $long;$i++) {
                if($splittedString[$i] == '_'):
                    unset($splittedString[$i]);
                    $splittedString[$i+1] = ucfirst($splittedString[$i+1]);
                endif;
            }
            $field = implode($splittedString);
        endif;

        return $field;
    }
    
    private function writeJs($fh, $data) {
        $identation = "\t";
        $dobleidentation = "\t\t";
        $tripleidentation = "\t\t\t";
        $cuadrupleidentation = "\t\t\t\t";
        $quinidentation = "\t\t\t\t\t";
        
        fwrite($fh, '<script type="text/javascript">' . PHP_EOL . 'function checkAll(checkname, bx) {' . PHP_EOL . $identation .
                        'for (i = 0; i < checkname.length; i++){' . PHP_EOL . $dobleidentation .
                            'checkname[i].checked = bx.checked? true:false;' . PHP_EOL . $identation . 
                        '}' . PHP_EOL .
                    '}' . PHP_EOL .
                    'function checkPage(bx){' . PHP_EOL . $identation .
                        'var bxs = document.getElementByTagName ( "table" ).getElementsByTagName ( "link[]" );' . PHP_EOL . 
                        PHP_EOL . $identation .
                        'for(i = 0; i < bxs.length; i++){' . PHP_EOL . $dobleidentation .
                            'bxs[i].checked = bx.checked? true:false;' . PHP_EOL . $identation .
                        '}' . PHP_EOL .
                    '}' . PHP_EOL);
        fwrite($fh, '$(function() {' . PHP_EOL);
        fwrite($fh, $identation . '$( "#borrar" ).click(function() {' . PHP_EOL);
        fwrite($fh, $dobleidentation . '$( "#dialog-confirm" ).dialog( "open" );' . PHP_EOL);
        fwrite($fh, $identation . '});' . PHP_EOL . PHP_EOL . $identation .  '$( "#dialog-confirm" ).dialog({' . PHP_EOL);
        fwrite($fh, $dobleidentation . 'autoOpen: false,' . PHP_EOL . $dobleidentation . 'resizable: false,' . PHP_EOL . $dobleidentation . 'height:350,' . PHP_EOL . $dobleidentation . 'modal: true,' . PHP_EOL);
        fwrite($fh, $dobleidentation . 'buttons: {' . PHP_EOL . $tripleidentation . $this->translator->trans('cancel', array(), 'indexView') . ': function() {');
        fwrite($fh, PHP_EOL . $cuadrupleidentation . '$( this ).dialog( "close" );' . PHP_EOL . $tripleidentation . '},' . PHP_EOL);
        fwrite($fh, PHP_EOL . $tripleidentation . '"' . $this->translator->trans('submit', array(), 'indexView') . '": function() {' . PHP_EOL);
        fwrite($fh, $cuadrupleidentation . 'var ids;' . PHP_EOL);
        fwrite($fh, PHP_EOL . $cuadrupleidentation . 'ids = $(\'input[type=checkbox]:checked\').map(function() {' . PHP_EOL);
        fwrite($fh, $quinidentation . 'return $(this).attr(\'value\');' . PHP_EOL . $cuadrupleidentation . '}).get();' . PHP_EOL);
        fwrite($fh, PHP_EOL . $cuadrupleidentation . 'if(ids.length > 0) {' . PHP_EOL . $quinidentation);
        fwrite($fh, 'var route = "{{ path(\'delete' . $data['entidad'] . '\', { \'id\': \'PLACEHOLDER\' }) }}";' . PHP_EOL);
        fwrite($fh, $quinidentation . 'window.location = route.replace("PLACEHOLDER", ids);' . PHP_EOL . $cuadrupleidentation . '}' . PHP_EOL);
        fwrite($fh, $cuadrupleidentation . 'else {' . PHP_EOL . $quinidentation . '$( this ).dialog( "close" );' . PHP_EOL);
        fwrite($fh, $quinidentation . '$( \'#message\' ).html(\'<div id="error">Debe seleccionar un elemento de la lista.</div>\').fadeIn( 100 ).delay( 1000 ).slideUp( 800 );');
        fwrite($fh, PHP_EOL . $cuadrupleidentation . '}' . PHP_EOL . $tripleidentation . '}' . PHP_EOL . $dobleidentation . '}' . PHP_EOL . $identation . '});' . PHP_EOL);
        fwrite($fh, PHP_EOL . $identation . '$(\'#editar\').click(function() {' . PHP_EOL);
        fwrite($fh, $dobleidentation . 'var ids;' . PHP_EOL . PHP_EOL . $dobleidentation . 'ids = $(\'input[type=checkbox]:checked\').map(function() {' . PHP_EOL);
        fwrite($fh, $tripleidentation . 'return $(this).attr(\'value\');' . PHP_EOL . $dobleidentation . '}).get();' . PHP_EOL);
        fwrite($fh, PHP_EOL . $dobleidentation . 'if(ids.length > 0) {' . PHP_EOL);
        fwrite($fh, $tripleidentation . 'var route = "{{ path(\'edit' . $data['entidad'] . '\', { \'id\': \'PLACEHOLDER\', \'_format\': \'html\' }) }}";');
        fwrite($fh, PHP_EOL . $tripleidentation . 'window.location = route.replace("PLACEHOLDER", ids);' . PHP_EOL);
        fwrite($fh, $dobleidentation . '}' . PHP_EOL . $dobleidentation . 'else {' . PHP_EOL);
        fwrite($fh, $tripleidentation . '$( \'#message\' ).html(\'<div id="error">Debe seleccionar un elemento de la lista.</div>\').fadeIn( 100 ).delay( 1000 ).slideUp( 800 );');
        fwrite($fh, PHP_EOL . $dobleidentation . '}' . PHP_EOL . $identation . '});' . PHP_EOL . '});' . PHP_EOL . '</script>');
    }
    
    public function generateRouting($data) {
        $identation = '  '; // en yml no podemos identar con tabulador
        $fh = fopen($this->modsG->getBundlePath() . 'Resources/config/routing.yml', 'a');
        
        fwrite($fh, 'show' . $data['entidad'] . ':' . PHP_EOL);
        fwrite($fh, $identation . 'pattern: /' . strtolower($data['entidad']) . '/show/{page}' . PHP_EOL);
        fwrite($fh, $identation . 'defaults: { _controller: ' . $data['bundle'] . ':Mods:show, page: 1 }' . PHP_EOL);
        
        fclose($fh);
    }
    
    public function getName() {
        return $this->name;
    }
 }