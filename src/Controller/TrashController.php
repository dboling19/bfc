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

    $file = null;
    $session = $this->request_stack->getSession();
    $session->set('dir', $this->root_dir . 'trash');
    $dir = $session->get('dir');
    if ($params = $request->query->all())
    {
      $file = $this->file_repo->find($params['id']);

    }


    $files = $this->file_repo->findTrash();


    $file = null;
    if ($params = $request->query->all())
    {
      $file = $this->file_repo->find($params['id']);

    }

    return $this->render('displays/trash.html.twig', [
      'files' => $files,
      'file' => $file,
    ]);
  }


  /**
   * Moves a file to the app trash folder for deletion.
   * Another function will completely delete the file.
   * 
   * @author Daniel Boling
   */
  #[Route('/trash_file', name:'trash_file')]
  public function trash_file(Request $request): Response
  {

    if ($params = $request->query->all())
    {
      $file = $this->file_repo->find($params['file_id']);
      $file->setDateTrashed(new \Datetime('now'));
      $file->setDirectory($this->dir_repo->findOneBy(['name' => 'trash']));
      $finder = new Finder();
      $finder->in($this->request_stack->getSession()->get('dir'))->files()->name($file->getFileName());
      foreach ($finder as $result)
      {
        rename($result, $this->root_dir . '/trash' . '/' .$file->getFileName());
      }
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
  #[Route('/delete_file', name:'delete_file')]
  public function delete_file(Request $request): Response
  {

    if ($params = $request->query->all())
    {
      $file = $this->file_repo->find($params['file_id']);
      $this->em->remove($file);
      $finder = new Finder();
      $finder->in($this->root_dir . '/trash')->files()->name($file->getFileName());
      foreach ($finder as $result)
      {
        unlink($result);
      }
      $this->em->flush();
    }

    return $this->redirectToRoute('trash_display');
  }
  

}
