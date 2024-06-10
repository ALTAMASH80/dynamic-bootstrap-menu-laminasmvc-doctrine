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

    public function updateAction(){
        $request = $this->getRequest();
        $data = ['status' => '404', 'message' => 'Failure'];
        $viewModel = new \Laminas\View\Model\JsonModel();
        if($request->isPost() && $request->isXmlHttpRequest()){
            $repo = $this->entityManager->getRepository(Menu::class);
            $postDataArr = $request->getPost()->toArray();
            $return = null;

            $return = $repo->editPostedNode($postDataArr);
            if($return){
                $uow = $this->entityManager->getUnitOfWork();
                $return = $uow->getOriginalEntityData($return);

                $arrReturn = [
                    'id' => $postDataArr['id'],
                    'title' => $return['label'],
                    'route_name' => $return['route'],
                    'uri' => $return['uri'],
                    'resource' => $return['resource'],
                    'slug' => $return['slug'],
                ];
                unset($return);
                $data['status'] = 200; 
                $data['message'] = 'Success';
                $data['data'] = $arrReturn;
                $data['postedData']  = $postDataArr;
            }
        }

        return $viewModel->setVariables($data);
    }
}
