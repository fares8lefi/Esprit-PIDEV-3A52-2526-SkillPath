<?php

namespace App\Form;

use App\Entity\Module;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du module',
                'attr' => ['placeholder' => 'ex: Intelligence Artificielle']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['rows' => 4, 'placeholder' => 'Décrivez le contenu du module...']
            ])
            ->add('dateCreation', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de création',
            ])
            ->add('level', TextType::class, [
                'label' => 'Niveau',
                'attr' => ['placeholder' => 'ex: Débutant, Intermédiaire, Expert']
            ])
            ->add('image', TextType::class, [
                'label' => 'URL de l\'image (Optionnel)',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Module::class,
        ]);
    }
}
