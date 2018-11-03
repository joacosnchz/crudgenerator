<?php
namespace DSNEmpresas\ModsGenerator\ModsGeneratorBundle\Controller;

interface CodeGeneratorInterface {    
    public function generateController($data);
    
    public function generateView($data);
    
    public function generateRouting($data);
    
    public function getName();
}

