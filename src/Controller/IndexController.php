<?php
declare(strict_types=1);

namespace LRPHPT\MenuTree\Controller;


use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use LRPHPT\MenuTree\Entity\Menu;

class IndexController extends AbstractActionController{
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    public function __construct(\Doctrine\ORM\EntityManager $entityManager){
        $this->entityManager = $entityManager;
    }

    public function indexAction(){
        $repo = $this->entityManager->getRepository(Menu::class);
//         print_r($repo->childrenHierarchy());
        $repo->childrenHierarchy($repo->findOneBy(['label' => 'lrphpt']), false, ['parent' => 'parent', 'root'], false);
        $arr = $repo->getPickleTreeArray('lrphpt');

        return new ViewModel(['treeArray' => $arr]);
    }
}