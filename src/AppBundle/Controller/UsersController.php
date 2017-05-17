<?php
/**
 * Rafael Armenio <rafael.armenio@gmail.com>
 *
 * @link http://github.com/armenio for more information
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Users;
use AppBundle\Form\Type\UsersType;
use Doctrine\ORM\EntityRepository;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//new RouteResource;

/**
 * Class UsersController
 * @package AppBundle\Controller
 *
 * @RouteResource("/api/users")
 */
class UsersController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @param Request $request
     * @return null|object
     */
    protected function getRequestToken(Request $request)
    {
        $JWTTokenAuthenticator = $this->container->get('lexik_jwt_authentication.security.guard.jwt_token_authenticator');
        $preAuthToken = $JWTTokenAuthenticator->getCredentials($request);
        $tokenUser = $JWTTokenAuthenticator->getUser($preAuthToken, new JWTUserProvider(JWTUser::class));
        return $this->getUsersRepository()->findOneBy(['username' => $tokenUser->getUserName()]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return View
     */
    public function getAction(Request $request, $id)
    {
        $requestToken = $this->getRequestToken($request);

        if ($requestToken->getId() != $id) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }

        $user = $this->getUsersRepository()->find($id);

        if ($user === null) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }

        return $user;
    }

    /**
     * @param Request $request
     * @return View|\Symfony\Component\Form\Form
     */
    public function postAction(Request $request)
    {
        $form = $this->createForm(UsersType::class, null, [
            'csrf_protection' => false,
        ]);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }

        /**
         * @var $user Users
         */
        $user = $form->getData();
        $now = new \DateTime();
        $user->setCreatedAt($now);
        $user->setUpdatedAt($now);
        $password = $user->getPassword();
        $user->setPassword(password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]));

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $jwtManager = $this->container->get('lexik_jwt_authentication.jwt_manager');
        $token = $jwtManager->create($user);

        $data = [
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'username' => $user->getUsername(),
            ],
        ];

        return new View($data, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param $id
     * @return View|\Symfony\Component\Form\Form
     */
    public function putAction(Request $request, $id)
    {
        $requestToken = $this->getRequestToken($request);

        if ($requestToken->getId() != $id) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }

        /**
         * @var $user Users
         */
        $user = $this->getUsersRepository()->find($id);

        if ($user === null) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }

        $originalUsername = $user->getUsername();
        $originalPassword = $user->getPassword();
        $user->setPasswordConfirmation($originalPassword); // coloca a senha original no objeto para validar

        $now = new \DateTime();
        $user->setUpdatedAt($now);

        $form = $this->createForm(UsersType::class, $user, [
            'csrf_protection' => false,
            'trim' => true,
        ]);

        if (trim($request->get('password', '')) === '' && trim($request->get('passwordConfirmation', '')) === '') {
            $form->remove('password');
            $form->remove('passwordConfirmation');
        }

        $request->request->set('username', $originalUsername); //mantém o usuário atual
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }

        $password = $user->getPassword();
        if ($password != $originalPassword) {
            $user->setPassword(password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]));
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View($user, Response::HTTP_OK);
    }


    /**
     * @param Request $request
     * @param $id
     * @return View|\Symfony\Component\Form\Form
     */
    public function patchAction(Request $request, $id)
    {
        $requestToken = $this->getRequestToken($request);

        if ($requestToken->getId() != $id) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }

        /**
         * @var $user Users
         */
        $user = $this->getUsersRepository()->find($id);

        if ($user === null) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }

        $originalUsername = $user->getUsername();
        $originalPassword = $user->getPassword();
        $user->setPasswordConfirmation($originalPassword); // coloca a senha original no objeto para validar

        $now = new \DateTime();
        $user->setUpdatedAt($now);

        $form = $this->createForm(UsersType::class, $user, [
            'csrf_protection' => false,
        ]);

        if (trim($request->get('password', '')) === '' && trim($request->get('passwordConfirmation', '')) === '') {
            $form->remove('password');
            $form->remove('passwordConfirmation');
        }

        $request->request->set('username', $originalUsername); //mantém o usuário atual
        $form->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $form;
        }

        $password = $user->getPassword();
        if ($password != $originalPassword) {
            $user->setPassword(password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]));
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View($user, Response::HTTP_OK);
    }

    /**
     * @return EntityRepository
     */
    private function getUsersRepository()
    {
        return $this->get('crv.doctrine_entity_repository.users');
    }
}