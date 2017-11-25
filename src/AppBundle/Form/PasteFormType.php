<?php

namespace AppBundle\Form;

use AppBundle\Service\MessageLogTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [])
            ->add('message', TextareaType::class, [])
            ->add('chat_filter', ChoiceType::class, [
                'choices' => [
                    'Hide Server Messages' => MessageLogTransformer::HIDE_SERVER_MSG,
                    'Hide Private Messages' => MessageLogTransformer::HIDE_PRIVATE_MSG,
                    'Hide Team Chat' => MessageLogTransformer::HIDE_TEAM_CHAT,
                    'Hide Admin Chat' => MessageLogTransformer::HIDE_ALL_ADMIN,
                    'Hide Join & Part Messages' => MessageLogTransformer::HIDE_JOIN_PART,
                    'Hide IP Addresses' => MessageLogTransformer::HIDE_IP_ADDRESS,
                    'Hide Kill Messages' => MessageLogTransformer::HIDE_KILL_MSG,
                    'Hide Flag Messages' => MessageLogTransformer::HIDE_FLAG_ACTION,
                    'Hide Public Chat' => MessageLogTransformer::HIDE_PUBLIC_MSG,
                ],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('no_save', CheckboxType::class, [
                'required' => false,
                'label' => "Don't save to account"
            ])
            ->add('submit', SubmitType::class, [])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getBlockPrefix()
    {
        return 'app_bundle_paste';
    }
}
