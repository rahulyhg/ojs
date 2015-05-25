<?php

namespace Ojs\JournalBundle\Controller;

use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Source\Entity;
use Doctrine\ORM\QueryBuilder;
use Ojs\Common\Helper\ActionHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Ojs\Common\Controller\OjsController as Controller;
use Ojs\JournalBundle\Entity\JournalsIndex;
use Ojs\JournalBundle\Form\JournalsIndexType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;

/**
 * JournalsIndex controller.
 *
 */
class JournalsIndexController extends Controller
{

    /**
     * Lists all JournalsIndex entities.
     *
     */
    public function indexAction()
    {
        $journal = $this->get("ojs.journal_service")->getSelectedJournal()->getId();
        $source = new Entity('OjsJournalBundle:JournalsIndex');
        if ($journal) {
            $ta = $source->getTableAlias();
            $source->manipulateQuery(function (QueryBuilder $qb) use ($journal, $ta) {
                $qb->andWhere(
                                $qb->expr()->eq("$ta.journal_id", ':journal')
                        )
                        ->setParameter('journal', $journal);
            });
        }
        if (!$journal) {
            throw new NotFoundHttpException("Journal not found!");
        }
        $grid = $this->get('grid')->setSource($source);

        $actionColumn = new ActionsColumn("actions", 'actions');
        ActionHelper::setup($this->get('security.csrf.token_manager'));

        $rowAction[] = ActionHelper::showAction('manager_journals_indexes_show', 'id');
        $rowAction[] = ActionHelper::editAction('manager_journals_indexes_edit', 'id');
        $rowAction[] = ActionHelper::deleteAction('manager_journals_indexes_delete', 'id');

        $actionColumn->setRowActions($rowAction);
        $grid->addColumn($actionColumn);
        $data = [];
        $data['grid'] = $grid;
        $data['journal_id'] = $journal;

        return $grid->getGridResponse('OjsJournalBundle:JournalsIndex:index.html.twig', $data);
    }

    /**
     * Creates a new JournalsIndex entity.
     *
     * @param  Request                   $request
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request)
    {
        $journal = $this->get("ojs.journal_service")->getSelectedJournal()->getId();
        $em = $this->getDoctrine()->getManager();
        $entity = new JournalsIndex();
        if ($journal) {
            $journalObj = $em->find('OjsJournalBundle:Journal', $journal);
            $entity->setJournalId($journal);
            $entity->setJournal($journalObj);
        }
        if (!$journal) {
            throw new NotFoundHttpException("Journal not found!");
        }
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setJournalIndexId($entity->getJournalIndex()->getId());
            $em->persist($entity);
            $em->flush();
            $this->successFlashBag('successful.create');

            return $this->redirect($this->generateUrl('manager_journals_indexes_show', array('id' => $entity->getId())));
        }
        $this->successFlashBag('successful.create');

        return $this->render('OjsJournalBundle:JournalsIndex:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a JournalsIndex entity.
     *
     * @param JournalsIndex $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(JournalsIndex $entity)
    {
        $form = $this->createForm(new JournalsIndexType(), $entity, array(
            'action' => $this->generateUrl('manager_journals_indexes_create', ['journal' => $entity->getJournalId()]),
            'method' => 'POST',
            'user' => $this->getUser(),
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new JournalsIndex entity.
     * @return Response
     */
    public function newAction()
    {
        $entity = new JournalsIndex();
        $journal = $this->get("ojs.journal_service")->getSelectedJournal()->getId();
        if ($journal) {
            $entity->setJournalId($journal);
        }
        if (!$journal) {
            throw new NotFoundHttpException('Journal not found!');
        }
        $form = $this->createCreateForm($entity);

        return $this->render('OjsJournalBundle:JournalsIndex:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a JournalsIndex entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OjsJournalBundle:JournalsIndex')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('notFound');
        }

        return $this->render('OjsJournalBundle:JournalsIndex:show.html.twig', array(
                    'entity' => $entity,
        ));
    }

    /**
     * Displays a form to edit an existing JournalsIndex entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OjsJournalBundle:JournalsIndex')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('notFound');
        }

        $editForm = $this->createEditForm($entity);

        return $this->render('OjsJournalBundle:JournalsIndex:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a JournalsIndex entity.
     *
     * @param JournalsIndex $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(JournalsIndex $entity)
    {
        $form = $this->createForm(new JournalsIndexType(), $entity, array(
            'action' => $this->generateUrl('manager_journals_indexes_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'user' => $this->getUser(),
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing JournalsIndex entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OjsJournalBundle:JournalsIndex')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('notFound');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            $this->successFlashBag('successful.update');

            return $this->redirect($this->generateUrl('manager_journals_indexes_edit', array('id' => $id)));
        }

        return $this->render('OjsJournalBundle:JournalsIndex:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     * @throws TokenNotFoundException
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('OjsJournalBundle:JournalsIndex')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('notFound');
        }

        $csrf = $this->get('security.csrf.token_manager');
        $token = $csrf->getToken('manager_journals_indexes'.$id);
        if($token!=$request->get('_token'))
            throw new TokenNotFoundException("Token Not Found!");
        $em->remove($entity);
        $em->flush();
        $this->successFlashBag('successful.remove');

        return $this->redirectToRoute('manager_journals_indexes');
    }
}
