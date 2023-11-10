# Dynamic-Bootstrap-Menu-LaminasMVC-Doctrine
Create dynamic menus via doctrine in Laminas MVC.

## Installation
```
composer require altamash80/dynamic-bootstrap-menu-laminasmvc-doctrine
```
## Dependency
1. Doctrine
2. Lrphpt Menu
3. Laminas MVC(minimum)

## Add Module in module config file
Add the module name in module.config.php.
```
return[
    'Lrphpt',
    'LRPHPT\MenuTree',
    'Application',
];
```

Run the command line below to create and execute migration.
```
./vendor/bin/doctrine-module migrations:diff
./vendor/bin/doctrine-module migrations:execute
```
### Insert Data Manually
Dynamic creation of data is not implemented.
```

public function indexAction(){
    $lrphptRootNode = new \LRPHPT\MenuTree\Entity\Menu();
    $lrphptRootNode->setLabel('lrphpt');
    $lrphptRootNode->setUri('#');
    $homeUrl = \LRPHPT\MenuTree\Entity\Menu();
    $homeUrl->setLabel('Home');
    $homeUrl->setRoute('home');
    $homeUrl->setParent($lrphptRootNode);

    $food = new \LRPHPT\MenuTree\Entity\Menu();
    $food->setLabel('Food');
    $food->setUri('https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/tree.md#retrieving-the-whole-tree-as-an-array');
    $food->setParent($lrphptRootNode);

    $fruits = new \LRPHPT\MenuTree\Entity\Menu();
    $fruits->setLabel('Fruits');
    $fruits->->setUri('https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/tree.md#retrieving-the-whole-tree-as-an-array');
    $fruits->setParent($food);

    $vegetables = new \LRPHPT\MenuTree\Entity\Menu();
    $vegetables->setTitle('Vegetables');
    $vegetables->setUri('https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/tree.md#retrieving-the-whole-tree-as-an-array');
    $vegetables->setParent($food);

    $carrots = new \LRPHPT\MenuTree\Entity\Menu();
    $carrots->setLabel('Carrots');
    $carrots->setUri('https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/tree.md#retrieving-the-whole-tree-as-an-array');
    $carrots->setParent($vegetables);

    $lrphptRootNode2 = new \LRPHPT\MenuTree\Entity\Menu();
    $lrphptRootNode2->setLabel('lrphpt another');
    $lrphptRootNode2->setUri('#');

    $this->em->persist($lrphptRootNode);
    $this->em->persist($lrphptRootNode2);
    $this->em->persist($home);
    $this->em->persist($food);
    $this->em->persist($fruits);
    $this->em->persist($vegetables);
    $this->em->persist($carrots);
    $this->em->flush();
}
```

## Usage
Add the below line in any layout.phtml file.
```
<?=$this->navigation('lrphpt_navigation')
                    ->bootstrapMenu()
                    ->setUlClass('navbar-nav')
                    // Optional setting to use with LmcRbac route guard.
                    //->setAuthorizationService($this->LmcRbacAuthorizationServiceHelper())
                    ; ?>
/*
Use the below configuration first by creating a navigation factory with someother_navigation. See this [link](https://github.com/ALTAMASH80/Dynamic-Bootstrap-Menu-LaminasMVC-Doctrine/blob/master/config/module.config.php#L23). Then write the below lines in the class file.
public function getName(){
   return 'lrphpt another';
}

<?=$this->navigation('someother_navigation')
                    ->bootstrapMenu()
                    ->setUlClass('navbar-nav')
                    // Optional setting to use with LmcRbac route guard.
                    //->setAuthorizationService($this->LmcRbacAuthorizationServiceHelper())
                    ; ?>
*/
```
