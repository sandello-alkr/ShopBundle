<?php

namespace alkr\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use alkr\ShopBundle\Entity\Good;
use alkr\ShopBundle\Form\GoodType;

/**
 * Good controller.
 *
 * @Route("/manager/good")
 */
class GoodController extends Controller
{

    /**
     * Lists all Good entities.
     *
     * @Route("/", name="good")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ShopBundle:Good')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Good entity.
     *
     * @Route("/", name="good_create")
     * @Method("POST")
     * @Template("ShopBundle:Good:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Good();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if(is_object($entity->getPreview()->file))
                $entity->getPreview()->setPagePreview($entity)->upload();
            else
                $entity->setPreview(null);
            $addPhoto = $entity->getPhotos();
            if($addPhoto)
            foreach ($addPhoto as $photo) {
                $photo->setPage($entity);
                $photo->upload();
                $em->persist($photo);
            }
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('manager_index'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Good entity.
    *
    * @param Good $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Good $entity)
    {
        $form = $this->createForm(new GoodType($this->hierarchyGoods()), $entity, array(
            'action' => $this->generateUrl('good_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Создать'));

        return $form;
    }

    /**
     * Displays a form to create a new Good entity.
     *
     * @Route("/new", name="good_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Good();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Good entity.
     *
     * @Route("/{id}/edit", name="good_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ShopBundle:Good')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Good entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Good entity.
    *
    * @param Good $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Good $entity)
    {
        $form = $this->createForm(new GoodType($this->hierarchyGoods()), $entity, array(
            'action' => $this->generateUrl('good_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Сохранить'));

        return $form;
    }
    /**
     * Edits an existing Good entity.
     *
     * @Route("/{id}", name="good_update")
     * @Method("PUT")
     * @Template("ShopBundle:Good:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ShopBundle:Good')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Good entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        // $request = $request->get('alkr_cmsbundle_page');

        // $entity->setContent($request['content']);
        if ($editForm->isValid()) {
            $entity->setLastmod(new \DateTime('now'));
            if(is_object($entity->getPreview()->file))
                $entity->getPreview()->setPagePreview($entity)->upload();
            elseif($entity->getPreview()->getId() == null)
                $entity->setPreview(null);
            $addPhoto = $entity->getPhotos()->getInsertDiff();
            foreach ($addPhoto as $photo) {
                $photo->setPage($entity);
                $photo->upload();
                $em->persist($photo);
            }
            $delPhoto = $entity->getPhotos()->getDeleteDiff();
            foreach ($delPhoto as $photo) {
                $entity->removePhoto($photo);
                $photo->remove();
                $em->remove($photo);
            }
            $addMapItem = $entity->getMapItems()->getInsertDiff();
            foreach ($addMapItem as $mapItem) {
                $mapItem->setPage($entity);
                $em->persist($mapItem);
            }
            $delMapItem = $entity->getMapItems()->getDeleteDiff();
            foreach ($delMapItem as $mapItem) {
                $entity->removeMapItem($mapItem);
                $em->remove($mapItem);
            }
            $em->flush();

            return $this->redirect($this->generateUrl('manager_index'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Good entity.
     *
     * @Route("/{id}", name="good_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ShopBundle:Good')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Good entity.');
            }

            $preview = $entity->getPreview()->setPagePreview(null)->remove();
            $entity->setPreview(null);

            $em->remove($entity);
            $em->flush();
            $em->remove($preview);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('manager_index'));
    }

    /**
     * Creates a form to delete a Good entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('good_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Удалить','attr'=>array('class'=>'delete_button', 'type'=>'danger')))
            ->getForm()
        ;
    }

    public function hierarchyGoods()
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('ShopBundle:Good');
        return $repo->getChildren();
    }
}
