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
        $dummyParentNode = $repo->findBy(['lvl' => '0']);

        $request = $this->getRequest();
        if($request->isPost()){
            $lrphptRootNode2 = new \LRPHPT\MenuTree\Entity\Menu();
            $lrphptRootNode2->setLabel($request->getPost('label'));
            $lrphptRootNode2->setUri('#');
            $lrphptRootNode2->setRoute('home');
            $this->entityManager->persist($lrphptRootNode2);
            $this->entityManager->flush();

            return $this->redirect()->toRoute('lrphpt-menu');
        }
        return new ViewModel(['rootNodes' => $dummyParentNode]);
    }

    public function menutreeAction(){
        $repo = $this->entityManager->getRepository(Menu::class);
        $dummyParentNode = $repo->findOneBy([
            'slug' => $this->params()->fromRoute('slug'), 
            'id' => $this->params()->fromRoute('id'),
        ]);

        if(empty($dummyParentNode)){
            throw new \Exception('Record not found.');
        }

        $arr = $repo->getPickleTreeArray($dummyParentNode->getSlug());

        $request = $this->getRequest();
        if($request->isPost()){
            $submittedData = json_decode($request->getPost('treeUpdatedData'), true);
            $repo->insertUpdateDataCollectedViaForm($submittedData, $dummyParentNode );
            
            return $this->redirect()->toRoute('lrphpt-menu');
        }
        return new ViewModel(['treeArray' => $arr, 'rootNode' => $dummyParentNode]);
    }
}