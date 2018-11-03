<?php
/*
 * ModsGeneratorCommand.php
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
namespace DSNEmpresas\ModsGenerator\ModsGeneratorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use DSNEmpresas\ModsGenerator\ModsGeneratorBundle\Controller\DefaultController;

class ModsGeneratorCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
                ->setName('generate:crudmodules')
                ->setDescription('Generate CRUD modules from an entity')
                ->addArgument('name', InputArgument::REQUIRED, 'A bundle name, a namespace, or a class name');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $data = $input->getArgument('name');
        
        $translator = $this->getContainer()->get('translator');
        
        if($this->isValid($data)):
            $data2 = explode(':', $data);
            $data3 = array('bundle' => $data2[0], 'entidad' => $data2[1]);
            $modsGeneratorController = new DefaultController();
            $modsGeneratorController->kernel = $this->getContainer()->get('kernel');
            $modsGeneratorController->doctrine = $this->getContainer()->get('doctrine');
            $modsGeneratorController->translator = $translator;
            $modsGeneratorController->generate($data3);
            
            $output->writeln($translator->trans('correct notice', array('%bundle%' => $data3['bundle']), 'domain'));
        else:
            $output->writeln($translator->trans('invalid bundle', array(), 'domain'));
        endif;
    }
    
    private function isValid($data) {
        $data2 = explode(':', $data);
        if(count($data2) != 2):
            return false;
        endif;
        $bundles = $this->getContainer()->getParameter('kernel.bundles');
        if(!in_array($data2[0], array_keys($bundles))): # Check if valid bundle name
            return false;
        endif;
        
        $container = $this->getContainer();
        
        $conf = $container->get('doctrine')->getManager()->getConfiguration();
        $folder = str_replace('app', 'src/', $container->get('kernel')->getRootDir()) 
                . str_replace('\\', '/', $conf->getEntityNamespace($data2[0])) . '/';
        if(!file_exists($folder . $data2[1] . '.php')): # Check if the entity exists in the bundle
            return false;
        endif;
        
        return true;
    }
}
