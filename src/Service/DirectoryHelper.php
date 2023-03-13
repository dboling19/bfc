<?php

namespace App\Service;

use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;


class DirectoryHelper
{

  private $root_dir;

  public function __construct(ContainerBagInterface $params)
  {
    $this->root_dir = $params->get('app.root_dir');

  }

  /**
   * Finds all dires in given dir, with 0 depth
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