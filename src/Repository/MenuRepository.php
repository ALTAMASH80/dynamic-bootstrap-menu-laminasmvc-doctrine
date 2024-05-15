<?php 
declare(strict_types=1);

namespace LRPHPT\MenuTree\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use LRPHPT\MenuTree\Entity\Menu;

//https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/tree.md#retrieving-the-whole-tree-as-an-array

class MenuRepository extends NestedTreeRepository{

    protected $menuPageIndex = 'pages';

    protected $bindingRootNode = null;

    public function getMenuPages(string $menuRootNodeLabel = 'node'){
        $arr = [];
        try{
            $dummyNode = $this->findOneBy(['slug' => $menuRootNodeLabel]);
            if($dummyNode !== null){
                $queryBuilder = $this->childrenQueryBuilder($dummyNode, false, ['root', 'lft']);
                $arr = $queryBuilder->getQuery()->getArrayResult();
            }
        }catch(\Exception $e){
            throw $e;
        }

        return $this->getMyTree($arr);
    }

    public function getMyTree(array $nodes = [])
    {
        $config = ['level' => 'lvl'];
        $nestedTree = [];
        $l = 0;

        if ([] !== $nodes) {
            // Node Stack. Used to help building the hierarchy
            $stack = [];
            foreach ($nodes as $child) {
                $item = $child;
                $item[$this->menuPageIndex] = [];
                // Number of stack items
                $l = count($stack);
                // Check if we're dealing with different levels
                while ($l > 0 && $stack[$l - 1][$config['level']] >= $item[$config['level']]) {
                    array_pop($stack);
                    --$l;
                }

                // Stack is empty (we are inspecting the root)
                if (0 == $l) {
                    // Assigning the root child
                    $nestedTree[$item['slug']] = $item;
                    $stack[] = &$nestedTree[$item['slug']];
                } else {
                    // Add child to parent
                    $stack[$l - 1][$this->menuPageIndex][$item['slug']] = $item;
                    $stack[] = &$stack[$l - 1][$this->menuPageIndex][$item['slug']];
                }
            }
        }

        return $nestedTree;
    }

    protected function createPickleTreeArr(array $nodes = [])
    {
        $nestedTree = [];
        $index = 0;
        if ([] !== $nodes) {
            // Node Stack. Used to help building the hierarchy
            foreach ($nodes as $child) {
                $nodeActions = [
                    [
                        'icon'=> 'fa fa-level-up',
                        'title'=> 'Delete',
                        'id'=> 'bind_' . $child['id'] . '_levelup',
                    ],
                    [
                        'icon'=> 'fa fa-level-down',
                        'title'=> 'Delete',
                        'id'=> 'bind_' . $child['id'] . '_leveldown',
                    ],
                    [
                        'icon'=> 'fa fa-edit',
                        'title'=> 'Delete',
                        'id'=> 'bind_' . $child['id'] . '_edit',
                    ],
                    [
                        'icon'=> 'fa fa-trash',
                        'title'=> 'Delete',
                        'id'=> 'bind_' . $child['id'] . '_delete',
                    ],
                ];
                $nestedTree[$index] = [
                    'n_id' => $child['id'], 
                    'n_title' => $child['label'],
                    'n_parentid' => $this->bindingRootNode->getId() === $child['parent_id'] ? 0 : $child['parent_id'],
                    'n_route' => $child['route'],
                    'n_uri' => $child['uri'],
                    'n_slug' => $child['slug'],
                    'n_elements' => $nodeActions,
                ];
                $index++;
            }
        }

        return $nestedTree;
    }

    public function getPickleTreeArray(string $menuRootNodeLabel = 'node'){
        $arr = [];
        try{
            $this->bindingRootNode = $this->findOneBy(['slug' => $menuRootNodeLabel]);
            if($this->bindingRootNode !== null){
                $queryBuilder = $this->childrenQueryBuilder($this->bindingRootNode , false, ['root', 'lft']);
                $arr = $queryBuilder->getQuery()->setHint(\Doctrine\ORM\Query::HINT_INCLUDE_META_COLUMNS, true)->getArrayResult();
            }
        }catch(\Exception $e){
            throw $e;
        }

        return $this->createPickleTreeArr($arr);
    }

    public function insertUpdateDataCollectedViaForm(array $submittedData = [], Menu $dummyParentNode = null){
        $menu = $createdNodes = [];
        $newNodes = $submittedData['newNodes'];
        $movedNodes = $submittedData['movedNodes'];

        /* Start of node creation loop. */
        if(count($newNodes) > 0){
            foreach($newNodes as $node){
                $menu[$node['id']] = new Menu();
                $menu[$node['id']]->setLabel($node['title']);
                $menu[$node['id']]->setRoute($node['route']);

                if($node['parent']['id'] === 0){/* Assign as root node. */
                    $menu[$node['id']]->setParent($dummyParentNode);
                    $createdNodes[$node['id']] = $node['parent']['id'];
                }elseif(is_long($node['parent']['value'])){/* Assign existing parent. */
                    $menu[$node['id']]->setParent($this->findOneBy(['id' => $node['parent']['value']]));
                    $createdNodes[$node['id']] = $node['parent']['id'];
                }else{
                    /* If nodes are created via parents first order.
                     * We'll not need to first create a parent and then set it. */
                    if(isset($createdNodes[$node['parent']['id']]) ){
                        $menu[$node['id']]->setParent($menu[$node['parent']['id']]);
                        $createdNodes[$node['id']] = $node['parent']['id'];
                    }
                }
                $this->_em->persist($menu[$node['id']]);
            }
            $this->_em->flush();
        }

        /* Nodes alignment change */
        if(count($movedNodes) > 0){
            foreach($movedNodes as $node){
                $nodeToMove = $this->findOneById($node['value']);
                $nodeMethod = explode('_', $node['sibling']);
                $nodeAsSibling = $this->findOneById($nodeMethod[2]);
                $nodeMethod = $nodeMethod[0];
                switch($nodeMethod){
                    case 'movedown':
                        $this->persistAsNextSiblingOf($nodeToMove, $nodeAsSibling);
                        break;
                    case 'moveup':
                        $this->persistAsPrevSiblingOf($nodeToMove, $nodeAsSibling);
                        break;
                }
            }
            $this->_em->flush();
        }
    }
}
