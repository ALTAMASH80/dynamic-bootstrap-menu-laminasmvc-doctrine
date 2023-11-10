<?php 
declare(strict_types=1);

namespace LRPHPT\MenuTree\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

#https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/tree.md#retrieving-the-whole-tree-as-an-array

class MenuRepository extends NestedTreeRepository{

    protected $menuPageIndex = 'pages';

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
}
