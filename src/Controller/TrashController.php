<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use App\Repository\DocRepository;
use App\Repository\DirectoryRepository;
use App\Entity\Directory;
use App\Entity\Doc;


class TrashController extends AbstractController
{

  private $root_dir;
  private $em;
  private $dir_repo;
  private $file_repo;
  private $request_stack;

  public function __construct(ContainerBagInterface $params, ManagerRegistry $doctrine, DocRepository $file_repo, DirectoryRepository $dir_repo, RequestStack $request_stack)
  { 
    $this->root_dir = $params->get('app.root_dir');
    $this->em = $doctrine->getManager();
    $this->dir_repo = $dir_repo;
    $this->file_repo = $file_repo;
    $this->request_stack = $request_stack;
  }

  /**
   * Controls the main file display, search, and center page
   * 
   * @author Daniel Boling
   */
  #[Route('/trash', name: 'trash_display')]
  public function trash_display(Request $request): Response
  {

    $entity = null;
    $session = $this->request_stack->getSession();
    $cwd = $this->root_dir . 'trash/';
    if ($cwd_id = $this->dir_repo->findOneBy(['path' => $cwd]))
    {
      $cwd_id = $cwd_id->getId();
    }
    // in place until route handling is implemented
    $session->set('cwd', $cwd);
    $session->set('cwd_id', $cwd_id);
    // this line will need updated during sub-directory introductions
    // and traversal configurations

    if ($params = $request->query->all())
    // the page was loaded with params, meaning a file was
    // selected.  Load file info
    {

      if ($type = $params['type'] == 'dir')
      {
        $entity = $this->dir_repo->find($params['id']);
      } elseif ($type = $params['type'] == 'file')
      {
        $entity = $this->file_repo->find($params['id']);
      }
    }

    $file_results = $this->file_repo->findAllIn($cwd_id);
    $dir_results = $this->dir_repo->findAllIn($cwd_id);
    $results = array_merge($file_results, $dir_results);

    return $this->render('displays/trash.html.twig', [
      'results' => $results,
      'entity' => $entity,
    ]);
  }


  /**
   * Moves a file to the app trash folder for deletion.
   * Another function will completely delete the file.
   * 
   * @author Daniel Boling
   */
  #[Route('/trash_entity', name:'trash_entity')]
  public function trash_entity(Request $request): Response
  {

    if ($params = $request->query->all())
    {
      $finder = new Finder();
      $cwd = $this->request_stack->getSession()->get('cwd');
      if ($params['type'] == 'dir')
      {
        $entity = $this->dir_repo->find($params['entity_id']);
        $entity->setParent($this->dir_repo->findOneBy(['path' => $this->root_dir . 'trash/']));
        $entity->setPath($this->root_dir . 'trash/' . basename($entity->getName()));
        $finder->in($cwd)->directories()->name($entity->getName());
        $result = iterator_to_array($finder);
        rename(reset($result), $this->root_dir . 'trash' . '/' . $entity->getName());

      } elseif ($params['type'] == 'file') {
        $entity = $this->file_repo->find($params['entity_id']);
        $entity->setDirectory($this->dir_repo->findOneBy(['path' => $this->root_dir . 'trash/']));
        $finder->in($cwd)->files()->name($entity->getFileName());
        $result = iterator_to_array($finder);
        rename(reset($result), $this->root_dir . 'trash/' . $entity->getFileName());
      }
      $entity->setDateTrashed(new \Datetime('now', new \DateTimeZone('America/Indiana/Indianapolis')));
      $this->em->flush();
    }

    return $this->redirectToRoute('home');
  }


    /**
   * Moves a file to the app trash folder for deletion.
   * Another function will completely delete the file.
   * 
   * @author Daniel Boling
   */
  #[Route('/delete_entity', name:'delete_entity')]
  public function delete_entity(Request $request): Response
  {
    if ($params = $request->query->all())
    {
      $finder = new Finder();
      if ($params['type'] == 'dir')
      {
        $entity = $this->dir_repo->find($params['entity_id']);
        $this->em->remove($entity);
        $finder->in($this->root_dir . '/trash')->directories()->path($entity->getName());
        $result = iterator_to_array($finder);
        rmdir(reset($result));
        $this->em->flush();
      } elseif ($params['type'] == 'file')
      {
        $entity = $this->file_repo->find($params['entity_id']);
        $this->em->remove($entity);
        $finder->in($this->root_dir . '/trash')->files()->name($entity->getFileName());
        $result = iterator_to_array($finder);
        unlink(reset($result));
        $this->em->flush();
      }
    }

    return $this->redirectToRoute('trash_display');
  }
  

}
