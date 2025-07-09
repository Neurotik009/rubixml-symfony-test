<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Role;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRolesType extends AbstractType
{

    public function __construct() {

    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('roles', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Rollen',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('r')
                        ->orderBy('r.name', 'ASC');
                },
                'choice_value' => 'name',
                'by_reference' => false,
                'attr' => [
                    'class' => 'role-choices',
                ],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
