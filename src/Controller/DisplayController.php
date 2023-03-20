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
use App\Service\DirectoryHelper;


class DisplayController extends AbstractController
{

  private $root_dir;
  private $em;
  private $dir_repo;
  private $file_repo;
  private $request_stack;
  private $dir_helper;

  public function __construct(DirectoryHelper $dir_helper, ContainerBagInterface $params, ManagerRegistry $doctrine, DocRepository $file_repo, DirectoryRepository $dir_repo, RequestStack $request_stack)
  { 
    $this->root_dir = $params->get('app.root_dir');
    $this->em = $doctrine->getManager();
    $this->dir_repo = $dir_repo;
    $this->file_repo = $file_repo;
    $this->dir_helper = $dir_helper;

    $this->request_stack = $request_stack;
  }

  /**
   * Controls the main file display, search, and center page
   * 
   * @author Daniel Boling
   */
  #[Route('/', name: 'home')]
  public function home(Request $request): Response
  {

    if (!$this->dir_repo->findBy(['path' => $this->root_dir . 'home/']))
    // if directory does not exist in database create it
    {
      $dir = new Directory();
      $dir->setPath($this->root_dir . 'home/');
      $dir->setName('Home');
      $dir->setNotes('Home directory');
      $this->em->persist($dir);
      $this->em->flush();
    }

    if (!$this->dir_repo->findBy(['path' => $this->root_dir . 'trash/']))
    {
      $dir = new Directory();
      $dir->setPath($this->root_dir . 'trash/');
      $dir->setName('Trash');
      $dir->setNotes('Trash Directory');
      $this->em->persist($dir);
      $this->em->flush();
    }

    $filesystem = new Filesystem();
    if (!$filesystem->exists($this->root_dir))
    // if the system dir does not exist, create it
    {
      $filesystem->mkdir($this->root_dir);
    }
    if (!$filesystem->exists($this->root_dir . 'trash'))
    {
      $filesystem->mkdir($this->root_dir . 'trash');
    }
    // consider above startup checks to ensure
    // directories exist and the system is ready to start
    // everything past here is actual functionality


    $entity = null;
    $session = $this->request_stack->getSession();
    $cwd = $this->root_dir . 'home/';
    if ($cwd_id = $this->dir_repo->findOneBy(['path' => $cwd]))
    {
      $cwd_id = $cwd_id->getId();
    }
    // in place until route handling is implemented
    $session->set('cwd', $cwd);
    $session->set('cwd_id', $cwd_id);
    // this line will need updated during sub-directory introductions
    // and traversal configurations

    $cwd = $session->get('cwd');
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

    return $this->render('displays/home.html.twig', [
      'results' => $results,
      'entity' => $entity,
    ]);
  }
  
}
