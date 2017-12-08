<?php

namespace AppBundle\Form;

use AppBundle\Entity\Paste;
use AppBundle\Form\DataTransformer\MessageFilterBitwiseTransformer;
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
    private $bitwiseTransformer;

    public function __construct(MessageFilterBitwiseTransformer $bitwiseTransformer)
    {
        $this->bitwiseTransformer = $bitwiseTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [])
            ->add('message', TextareaType::class, [])
            ->add('filter', ChoiceType::class, [
                'choices' => [
                    'Hide Server Messages' => MessageLogTransformer::HIDE_SERVER_MSG,
                    'Hide Private Messages' => MessageLogTransformer::HIDE_PRIVATE_MSG,
                    'Hide Team Chat' => MessageLogTransformer::HIDE_TEAM_CHAT,
                    'Hide Admin Chat' => MessageLogTransformer::HIDE_ADMIN_CHAT,
                    'Hide Join & Part Messages' => MessageLogTransformer::HIDE_JOIN_PART,
                    'Hide IP Addresses' => MessageLogTransformer::HIDE_IP_ADDRESS,
                    'Hide Kill Messages' => MessageLogTransformer::HIDE_KILL_MSG,
                    'Hide Flag Messages' => MessageLogTransformer::HIDE_FLAG_ACTION,
                    'Hide Public Chat' => MessageLogTransformer::HIDE_PUBLIC_MSG,
                    'Hide Pausing' => MessageLogTransformer::HIDE_PAUSING,
                    'Hide Client Messages' => MessageLogTransformer::HIDE_CLIENT_MSG,
                ],
                'multiple' => true,
                'expanded' => true,
                'label' => 'Chat Filter(s)',
            ])
            ->add('encrypted', CheckboxType::class, [
                'required' => false,
                'label' => "Don't save to account"
            ])
            ->add('submit', SubmitType::class, [])
        ;

        $builder
            ->get('filter')
            ->addModelTransformer($this->bitwiseTransformer)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Paste::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_paste';
    }
}
