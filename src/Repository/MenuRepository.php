<?php 
declare(strict_types=1);

namespace LRPHPT\MenuTree\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

#https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/tree.md#retrieving-the-whole-tree-as-an-array

class MenuRepository extends NestedTreeRepository{

    protected $menuPageIndex = 'pages';
    
    protected $bindingRootNode = null;

    public function getMenuPages(string $menuRootNodeLabel = 'node'){
        $arr = [];
        try{
            $dummyNode = $this->findOneBy(['label' => $menuRootNodeLabel]);
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
            $this->bindingRootNode = $this->findOneBy(['label' => $menuRootNodeLabel]);
            if($this->bindingRootNode !== null){
                $queryBuilder = $this->childrenQueryBuilder($this->bindingRootNode , false, ['root', 'lft']);
                $arr = $queryBuilder->getQuery()->setHint(\Doctrine\ORM\Query::HINT_INCLUDE_META_COLUMNS, true)->getArrayResult();
            }
        }catch(\Exception $e){
            throw $e;
        }
        
        return $this->createPickleTreeArr($arr);
    }
}
