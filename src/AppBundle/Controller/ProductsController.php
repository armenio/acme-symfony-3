<?php
/**
 * Rafael Armenio <rafael.armenio@gmail.com>
 *
 * @link http://github.com/armenio for more information
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Products;
use AppBundle\Form\Type\ProductsType;
use Doctrine\ORM\EntityRepository;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//new RouteResource;

/**
 * Class ProductsController
 * @package AppBundle\Controller
 *
 * @RouteResource("/api/products")
 */
class ProductsController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @param $id
     * @return View
     */
    public function getAction($id)
    {
        $product = $this->getProductsRepository()->find($id);

        if ($product === null) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }

        return $product;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cgetAction(Request $request)
    {
        $query = $this->getProductsRepository()->createQueryBuilder('p');

        $q = trim($request->get('q', ''));

        if ($q) {
            $query->andWhere('p.name LIKE :q')->setParameter('q', '%' . $q . '%');
        }

        $query->addOrderBy('p.' . $request->get('order', 'stock'), $request->get('by', 'asc'));

        return $query->getQuery()->execute();
    }

    /**
     * @param Request $request
     * @return View|\Symfony\Component\Form\Form
     */
    public function postAction(Request $request)
    {
        $form = $this->createForm(ProductsType::class, null, [
            'csrf_protection' => false,
        ]);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }

        /**
         * @var $product Products
         */
        $product = $form->getData();
        $now = new \DateTime();
        $product->setCreatedAt($now);
        $product->setUpdatedAt($now);

        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->flush();

        return new View($product, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param $id
     * @return View|\Symfony\Component\Form\Form
     */
    public function putAction(Request $request, $id)
    {
        /**
         * @var $product Products
         */
        $product = $this->getProductsRepository()->find($id);

        if ($product === null) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }

        $now = new \DateTime();
        $product->setUpdatedAt($now);

        $form = $this->createForm(ProductsType::class, $product, [
            'csrf_protection' => false,
        ]);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View($product, Response::HTTP_OK);
    }


    /**
     * @param Request $request
     * @param $id
     * @return View|\Symfony\Component\Form\Form
     */
    public function patchAction(Request $request, $id)
    {
        /**
         * @var $product Products
         */
        $product = $this->getProductsRepository()->find($id);

        if ($product === null) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }

        $now = new \DateTime();
        $product->setUpdatedAt($now);

        $form = $this->createForm(ProductsType::class, $product, [
            'csrf_protection' => false,
        ]);

        $form->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $form;
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View($product, Response::HTTP_OK);
    }

    /**
     * @param $id
     * @return View
     */
    public function deleteAction($id)
    {
        /**
         * @var $product Products
         */
        $product = $this->getProductsRepository()->find($id);

        if ($product === null) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cdeleteAction(Request $request)
    {
        $entities = $this->getProductsRepository()->findBy(['id' => $request->request->filter('ids', [0])]);

        if (!count($entities)) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }
        $em = $this->getDoctrine()->getManager();

        foreach ($entities as $entity) {
            $em->remove($entity);
        }

        $em->flush();

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function reportAction(Request $request)
    {
        //busca o conteúdo no banco
        $contents = $this->getProductsRepository()->findBy([], [
            'stock' => 'asc', //mais importantes primeiro
            'updatedAt' => 'desc', //que se esgotaram recentemente
        ]);

        // renderiza o template
        $body = $this->renderView('Emails/report.html.twig', [
            'contents' => $contents,
        ]);

        // envia o email
        $message = \Swift_Message::newInstance();
        $message->setSubject('Relatório gerencial Acme')
            ->setFrom(['cron@acme.com' => 'Cron'])
            ->setTo(['gerente@acme.com' => 'Gerente'])
            ->setBody($body, 'text/html');

        $this->get('mailer')->send($message);

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @return EntityRepository
     */
    private function getProductsRepository()
    {
        return $this->get('crv.doctrine_entity_repository.products');
    }
}