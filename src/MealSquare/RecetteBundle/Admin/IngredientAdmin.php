<?php

namespace MealSquare\RecetteBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class IngredientAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('libelle')
            ->add('description')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('custom', 'string', array('template' => 'MealSquareCommonBundle:Admin:list_imagefield_custom.html.twig', 'label' => 'Image'))
            ->add('id')
            ->add('libelle')
            //->add('description')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('id')
            ->add('libelle')
            ->add('description')
            ->add('image', 'sonata_type_model_list', array('required' => false), array(
                    'link_parameters' => array(
                        'context' => 'ingredient',
                        'hide_context' => true
                    )
                ))
            ->add('galerie', 'sonata_type_model_list', array('required' => false), array(
                    'link_parameters'   => array(
                        'context' => 'ingredient',
                        'hide_context' => true
                    )
                ))
            ->add('categorie', 'sonata_type_model_list', array('required' => false), array(
                    'link_parameters' => array(
                        'context' => 'ingredient',
                        'hide_context' => true
                    )
                ))
            
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('libelle')
            ->add('description')
        ;
    }
}
