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


class DirectoryController extends AbstractController
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
   * Create a directory in cwd
   * 
   * @author Daniel Boling
   */
  #[Route('/create/dir', name: 'dir_create')]
  public function create_directory(Request $request): Response
  {
    $params = $request->request->all();
    $name = $params['name'];
    $cwd = $this->request_stack->getSession()->get('cwd');
    $cwd_id = $this->request_stack->getSession()->get('cwd_id');
    // dd(basename($cwd) . '/' . $name);

    $dir = new Directory();
    $dup_cwd_dirs = $this->dir_repo->findAllIn($cwd, $cwd . $name);
    $db_cwd = $this->dir_repo->find($cwd_id);
    if (count($dup_cwd_dirs) > 0)
    // sets the directory name to the count + 1 of found directories
    {
      $name .= '(' . count($dup_cwd_dirs) . ')';
      $dir->setPath($cwd . strtolower($name));
      $dir->setName($name);
      $dir->setParent($db_cwd);

    } else {
      $dir->setPath($cwd . strtolower($name));
      $dir->setName($name);
      $dir->setParent($db_cwd);
    }
    $this->em->persist($dir);
    $this->em->flush();

    $filesystem = new Filesystem();
    $filesystem->mkdir($cwd . $name);
    // will use the given or modified name from above

    return $this->redirectToRoute('home');


  }

}