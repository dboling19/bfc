<?php

namespace App\Service;

use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use App\Repository\DirectoryRepository;


class DirectoryHelper
{

  private $root_dir;
  private $dir_repo;

  public function __construct(DirectoryRepository $dir_repo, ContainerBagInterface $params)
  {
    $this->root_dir = $params->get('app.root_dir');
    $this->dir_repo = $dir_repo;
  }

  /**
   * Finds all dirs in given dir, with 0 depth
   * 
   * @author Daniel Boling
   */
  public function findAllIn($cwd)
  {
    $finder = new Finder();
    $finder->depth(0)->in($this->root_dir . $cwd);
    if ($dirs = $finder->hasResults())
    {
      $dirs = [];
      foreach ($finder as $file)
      {
        $dirs[] = str_replace($this->root_dir, '', $file->getPathname());
      }
    }

    return $dirs;
  }

  
}

?>