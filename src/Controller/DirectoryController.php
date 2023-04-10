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
use App\Repository\TagRepository;
use App\Entity\Directory;
use App\Entity\Doc;
use App\Entity\Tag;
use App\Service\DirectoryHelper;


class DirectoryController extends AbstractController
{

  private $root_dir;
  private $em;
  private $dir_repo;
  private $file_repo;
  private $tag_repo;
  private $request_stack;
  private $dir_helper;

  public function __construct(DirectoryHelper $dir_helper, ContainerBagInterface $params, ManagerRegistry $doctrine, TagRepository $tag_repo, DocRepository $file_repo, DirectoryRepository $dir_repo, RequestStack $request_stack)
  { 
    $this->root_dir = $params->get('app.root_dir');
    $this->em = $doctrine->getManager();
    $this->dir_repo = $dir_repo;
    $this->file_repo = $file_repo;
    $this->tag_repo = $tag_repo;
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

    $dir = new Directory();
    $dup_cwd_dirs = $this->dir_repo->findAllIn($cwd, $cwd . $name);
    $db_cwd = $this->dir_repo->find($cwd_id);
    if (count($dup_cwd_dirs) > 0)
    // sets the directory name to the count + 1 of found directories
    {
      $name .= '(' . count($dup_cwd_dirs) . ')';
      $dir->setPath($cwd . $name);
      $dir->setName($name);
      $dir->setParent($db_cwd);

    } else {
      $dir->setPath($cwd . $name . '/');
      $dir->setName($name);
      $dir->setParent($db_cwd);
    }
    foreach (explode(',', $params['folder_selected_tags']) as $tag)
    {
      $dir->addTag($this->tag_repo->findOneBy(['name' => $tag]));
    }
    $this->em->persist($dir);
    $this->em->flush();

    $filesystem = new Filesystem();
    $filesystem->mkdir($cwd . $name);
    // will use the given or modified name from above

    return $this->redirectToRoute('folder_display', ['id' => $cwd_id]);
  }


  /**
   * Change dir and redirect to folder_display
   * 
   * @author Daniel Boling
   */
  #[Route('/chdir', name: 'chdir')]
  public function chdir(Request $request): Response
  {
    $session = $this->request_stack->getSession();
    $session->set('cwd_id', $request->query->get('id'));
    $session->set('cwd', $this->dir_repo->find($request->query->get('id'))->getPath());
    $cwd_id = $session->get('cwd_id');

    return $this->redirectToRoute('folder_display', ['id' => $cwd_id]);
  }

}