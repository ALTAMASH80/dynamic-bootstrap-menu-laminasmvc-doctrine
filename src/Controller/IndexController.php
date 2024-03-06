<?php
declare(strict_types=1);

namespace LRPHPT\MenuTree\Controller;


use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use LRPHPT\MenuTree\Entity\Menu;
use Doctrine\ORM\EntityManager;

class IndexController extends AbstractActionController{
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    public function __construct(EntityManager $entityManager){
        $this->entityManager = $entityManager;
    }

    public function indexAction(){
        $repo = $this->entityManager->getRepository(Menu::class);
//         print_r($repo->childrenHierarchy());
        $dummyParentNode = $repo->findOneBy(['label' => 'lrphpt']);
        $repo->childrenHierarchy($dummyParentNode, false, ['parent' => 'parent', 'root'], false);
        $arr = $repo->getPickleTreeArray('lrphpt');

        $request = $this->getRequest();
        if($request->isPost()){
            $submittedData = json_decode($request->getPost('treeUpdatedData'), true);
            /* echo '<pre>';
            print_r($submittedData);
            echo '</pre>'; */
            $menu = $createdNodes = $leftOverNodes = [];
            $newNodes = $submittedData['newNodes'];
            $parentNodes = $submittedData['parentNodes'];
            $movedNodes = $submittedData['movedNodes'];
            /* Start of node creation loop. */
            $index = 0;
            foreach($newNodes as $node){
                $menu[$index] = new Menu();
                $menu[$index]->setLabel($node['title']);
                var_dump(
                    $node['parent']['id'], 
                    ($node['parent']['id'] === 0), 
                    str_starts_with((string)$node['parent']['id'], 'node')
                );
                if($node['parent']['id'] === 0){/* Assign as root node. */
                    $menu[$index]->setParent($dummyParentNode);
                    $createdNodes[$node['id']] = $node['parent']['id'];
                }elseif(str_starts_with((string)$node['parent']['id'], 'node')){/* Assign existing parent. */
                    $menu[$index]->setParent($repo->findOneBy(['id' => $node['parent']['value']]));
                    $createdNodes[$node['id']] = $node['parent']['id'];
                }else{/* Need to first create a parent and then set it. */
                    $leftOverNodes[$node['parent']['id']] = $menu[$index];
                }
                var_dump(
                    in_array($node['parent']['id'], $createdNodes)
                );
                if(in_array($node['parent']['id'], $createdNodes)){
                    var_dump('persist on ' . $menu[$index]->getLabel());
                    $this->entityManager->persist($menu[$index]);
                }
                $index++;
            }
            $this->entityManager->flush();
            var_dump('executed Flush',$createdNodes, $leftOverNodes);die;

        }
        return new ViewModel(['treeArray' => $arr]);
    }

    public function tabulatorAction(){
        $repo = $this->entityManager->getRepository(Menu::class);
        //         print_r($repo->childrenHierarchy());
        $repo->childrenHierarchy($repo->findOneBy(['label' => 'lrphpt']), false, ['parent' => 'parent', 'root'], false);
        $arr = $repo->getPickleTreeArray('lrphpt');

        return new ViewModel(['treeArray' => $arr]);
    }
}