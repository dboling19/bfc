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

    if (!$this->dir_repo->findBy(['name' => 'bfc']))
    // if directory does not exist in database create it
    {
      $dir = new Directory();
      $dir->setName('bfc');
      $dir->setParent('');
      $dir->setNotes('Home directory');
      $this->em->persist($dir);
      $this->em->flush();
    }

    {
      $dir = new Directory();
      $dir->setName('trash');
      $dir->setParent('');
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
   if (!$filesystem->exists($this->root_dir . '/trash'))
    {
      $filesystem->mkdir($this->root_dir . '/trash');
    }
    // consider above startup checks to ensure
    // directories exist and the system is ready to start
    // everything past here is actual functionality


    $file = null;
    $session = $this->request_stack->getSession();
    $session->set('dir', '');
    // this line will need updated during sub-directory introductions
    // and traversal configurations

    $cwd = $session->get('dir');
    if ($params = $request->query->all())
    // the page was loaded with params, meaning a file was
    // selected.  Load file info
    {
      $file = $this->file_repo->find($params['id']);
      $cwd = $params['dir'];
    }

    $db_dirs = $this->dir_repo->findAllDirsIn($cwd);
    $fs_dirs = $this->dir_helper->findAllIn($cwd);
    foreach ($db_dirs as $db_dir)
    {
      foreach ($fs_dirs as $fs_dir)
      {
        if ($db_dir != $fs_dir)
        {
          $filesystem->mkdir($this->root_dir . $db_dir);
        }
      }
    }


    $files = $this->file_repo->findAllIn($cwd);

    return $this->render('displays/home.html.twig', [
      'files' => $files,
      'file' => $file,
    ]);
  }
  
}
