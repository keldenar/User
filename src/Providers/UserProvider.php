<?php

namespace Ephemeral\Providers;

use Silex\ServiceProviderInterface;
use Silex\Application;
use Ephemeral\UserAPI;
use Ephemeral\UserDB;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class UserServiceProvider
 * @package App\Services
 */
class UserProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Application $app)
    {
        // TODO: Implement register() method.
        $app['user'] = $app->share(function ($app) {
            if ($app['user.method'] == 'UserDB') return new UserDB($app);
            if ($app['user.method'] == 'UserAPI') return new UserAPI($app);

        });

        $app['user.validation'] = new Assert\Collection(array(
                'username' => array(new Assert\NotBlank,new Assert\Length(array('min' => 3))),
                'password' => array(new Assert\NotBlank),
                'email' => array(new Assert\NotBlank,new Assert\Length(array('min' => 3)), new Assert\Email()),
                'fullname' => array(new Assert\NotBlank,new Assert\Length(array('min' => 3))),
                'bio' => array(new Assert\NotBlank())
            )
        );

        if (array_key_exists("form.factory", $app)) {
            $app['user.form'] = $app['form.factory']->createBuilder(Type\FormType::class, [])
                ->add('username', Type\TextType::class, array(
                    'required' => true,
                    'label' => "Username",
                    'attr' => array('class' => 'form-control')))
                ->add('password', Type\RepeatedType::class, array(
                    'type' => Type\PasswordType::class,
                    'invalid_message' => 'The password fields must match.',
                    'first_options' => array('label' => 'Password'),
                    'second_options' => array('label' => 'Confirm Password'),
                    'required' => true,
                    'label' => "Password",
                    'options' => array('attr' => array('class' => 'form-control'))
                ))
                ->add('fullname', Type\TextType::class, array(
                    'required' => true,
                    'label' => "Full Name",
                    'attr' => array('class' => 'form-control')))
                ->add('email', Type\EmailType::class, array(
                    'required' => true,
                    'label' => "Email",
                    'attr' => array('class' => 'form-control')))
                ->add('bio', Type\TextareaType::class, array(
                    'label' => "Biography",
                    'attr' => array('class' => 'form-control')))
                ->add('register', Type\SubmitType::class, array('label' => "Register", 'attr' => array('type' => 'submit', 'class' => 'btn btn-success form-control')))
                ->add('reset', Type\ResetType::class, array('label' => "Reset", 'attr' => array('class' => 'btn btn-default form-control')))
                ->getForm();
        }
    }
    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }
}
